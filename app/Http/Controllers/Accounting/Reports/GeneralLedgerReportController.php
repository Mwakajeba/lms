<?php

namespace App\Http\Controllers\Accounting\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Barryvdh\DomPDF\Facade\Pdf;

class GeneralLedgerReportController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->can('view general ledger report')) {
            abort(403, 'Unauthorized access to this report.');
        }
        
        $user = Auth::user();
        $company = $user->company;

        // Get filter parameters
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $reportType = $request->get('report_type', 'accrual');
        $accountId = $request->get('account_id', null);
        $branchId = $request->get('branch_id', null);
        $showOpeningBalance = $request->get('show_opening_balance', true);
        $groupBy = $request->get('group_by', 'account');

        // Get accounts for filter
        $accounts = DB::table('chart_accounts')
            ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
            ->where('account_class_groups.company_id', $company->id)
            ->select('chart_accounts.id', 'chart_accounts.account_name', 'chart_accounts.account_code')
            ->orderBy('chart_accounts.account_code')
            ->get();

        // Get branches for filter: only user's assigned branches
        $branches = $user->branches()->where('branches.company_id', $company->id)->get();

        // Get general ledger data
        $generalLedgerData = $this->getGeneralLedgerData(
            $startDate, 
            $endDate, 
            $reportType, 
            $accountId, 
            $branchId, 
            $showOpeningBalance, 
            $groupBy
        );

        return view('accounting.reports.general-ledger.index', compact(
            'generalLedgerData',
            'startDate',
            'endDate',
            'reportType',
            'accountId',
            'branchId',
            'showOpeningBalance',
            'groupBy',
            'accounts',
            'branches',
            'user'
        ));
    }

    private function getGeneralLedgerData($startDate, $endDate, $reportType, $accountId, $branchId, $showOpeningBalance, $groupBy)
    {
        $user = Auth::user();
        $company = $user->company;

        // Build base query
        $query = DB::table('gl_transactions')
            ->join('chart_accounts', 'gl_transactions.chart_account_id', '=', 'chart_accounts.id')
            ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
            ->leftJoin('customers as c', 'gl_transactions.customer_id', '=', 'c.id')
            ->where('account_class_groups.company_id', $company->id)
            ->whereBetween('gl_transactions.date', [$startDate, $endDate]);

        // Add account filter
        if ($accountId) {
            $query->where('gl_transactions.chart_account_id', $accountId);
        }

        // Add branch filter
        // Branch filter: 'all' means all assigned branches
        $assignedBranchIds = Auth::user()->branches()->pluck('branches.id')->toArray();
        if ($branchId === 'all') {
            if (!empty($assignedBranchIds)) {
                $query->whereIn('gl_transactions.branch_id', $assignedBranchIds);
            }
        } elseif ($branchId) {
            $query->where('gl_transactions.branch_id', $branchId);
        } else {
            if (!empty($assignedBranchIds)) {
                $query->whereIn('gl_transactions.branch_id', $assignedBranchIds);
            }
        }

        // Add reporting type filter (cash vs accrual)
        if ($reportType === 'cash') {
            $query->whereExists(function ($subquery) {
                $subquery->select(DB::raw(1))
                    ->from('gl_transactions as gl2')
                    ->whereColumn('gl2.transaction_id', 'gl_transactions.transaction_id')
                    ->whereColumn('gl2.transaction_type', 'gl_transactions.transaction_type')
                    ->whereIn('gl2.chart_account_id', function($bankSubquery) {
                        $bankSubquery->select('chart_account_id')
                            ->from('bank_accounts');
                    });
            });
        }

        // Select fields based on group by
        if ($groupBy === 'account') {
            $query->select(
                'gl_transactions.*',
                'chart_accounts.account_name',
                'chart_accounts.account_code',
                'account_class_groups.name as group_name',
                'c.name as customer_name'
            )
            ->orderBy('chart_accounts.account_code')
            ->orderBy('gl_transactions.date')
            ->orderBy('gl_transactions.id');
        } elseif ($groupBy === 'date') {
            $query->select(
                'gl_transactions.*',
                'chart_accounts.account_name',
                'chart_accounts.account_code',
                'account_class_groups.name as group_name',
                'c.name as customer_name'
            )
            ->orderBy('gl_transactions.date')
            ->orderBy('chart_accounts.account_code')
            ->orderBy('gl_transactions.id');
        } else { // voucher
            $query->select(
                'gl_transactions.*',
                'chart_accounts.account_name',
                'chart_accounts.account_code',
                'account_class_groups.name as group_name',
                'c.name as customer_name'
            )
            ->orderBy('gl_transactions.transaction_id')
            ->orderBy('gl_transactions.transaction_type')
            ->orderBy('gl_transactions.id');
        }

        $transactions = $query->get();

        // Calculate opening balances if requested
        $openingBalances = collect();
        if ($showOpeningBalance) {
            $openingBalances = $this->getOpeningBalances($startDate, $accountId, $branchId, $reportType);
        }

        // Process transactions and calculate running balances
        $processedData = $this->processTransactions($transactions, $openingBalances, $groupBy);

        return [
            'transactions' => $processedData,
            'opening_balances' => $openingBalances,
            'summary' => $this->getSummary($processedData),
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'report_type' => $reportType,
                'account_id' => $accountId,
                'branch_id' => $branchId,
                'show_opening_balance' => $showOpeningBalance,
                'group_by' => $groupBy
            ]
        ];
    }

    private function getOpeningBalances($startDate, $accountId, $branchId, $reportType)
    {
        $user = Auth::user();
        $company = $user->company;

        $query = DB::table('gl_transactions')
            ->join('chart_accounts', 'gl_transactions.chart_account_id', '=', 'chart_accounts.id')
            ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
            ->where('account_class_groups.company_id', $company->id)
            ->where('gl_transactions.date', '<', $startDate);

        if ($accountId) {
            $query->where('gl_transactions.chart_account_id', $accountId);
        }

        $assignedBranchIds = Auth::user()->branches()->pluck('branches.id')->toArray();
        if ($branchId === 'all') {
            if (!empty($assignedBranchIds)) {
                $query->whereIn('gl_transactions.branch_id', $assignedBranchIds);
            }
        } elseif ($branchId) {
            $query->where('gl_transactions.branch_id', $branchId);
        } else {
            if (!empty($assignedBranchIds)) {
                $query->whereIn('gl_transactions.branch_id', $assignedBranchIds);
            }
        }

        if ($reportType === 'cash') {
            $query->whereExists(function ($subquery) {
                $subquery->select(DB::raw(1))
                    ->from('gl_transactions as gl2')
                    ->whereColumn('gl2.transaction_id', 'gl_transactions.transaction_id')
                    ->whereColumn('gl2.transaction_type', 'gl_transactions.transaction_type')
                    ->whereIn('gl2.chart_account_id', function($bankSubquery) {
                        $bankSubquery->select('chart_account_id')
                            ->from('bank_accounts');
                    });
            });
        }

        $openingBalances = $query->select(
            'gl_transactions.chart_account_id',
            'chart_accounts.account_name',
            'chart_accounts.account_code',
            DB::raw('SUM(CASE WHEN gl_transactions.nature = "debit" THEN gl_transactions.amount ELSE 0 END) as total_debit'),
            DB::raw('SUM(CASE WHEN gl_transactions.nature = "credit" THEN gl_transactions.amount ELSE 0 END) as total_credit')
        )
        ->groupBy('gl_transactions.chart_account_id', 'chart_accounts.account_name', 'chart_accounts.account_code')
        ->get()
        ->keyBy('chart_account_id');

        return $openingBalances;
    }

    private function processTransactions($transactions, $openingBalances, $groupBy)
    {
        // Ensure opening balances is a collection for safe ->get() access
        if (!($openingBalances instanceof \Illuminate\Support\Collection)) {
            $openingBalances = collect($openingBalances ?: []);
        }
        $processedData = [];
        $runningBalances = [];

        foreach ($transactions as $transaction) {
            $accountId = $transaction->chart_account_id;
            
            // Initialize running balance
            if (!isset($runningBalances[$accountId])) {
                $openingBalance = $openingBalances->get($accountId);
                $runningBalances[$accountId] = $openingBalance ? 
                    ($openingBalance->total_debit - $openingBalance->total_credit) : 0;
            }

            // Calculate running balance
            if ($transaction->nature === 'debit') {
                $runningBalances[$accountId] += $transaction->amount;
            } else {
                $runningBalances[$accountId] -= $transaction->amount;
            }

            $transaction->running_balance = $runningBalances[$accountId];
            $transaction->opening_balance = $openingBalances->get($accountId);

            $processedData[] = $transaction;
        }

        return $processedData;
    }

    private function getSummary($transactions)
    {
        $summary = [
            'total_debit' => 0,
            'total_credit' => 0,
            'net_movement' => 0,
            'transaction_count' => count($transactions),
            'account_count' => collect($transactions)->unique('chart_account_id')->count()
        ];

        foreach ($transactions as $transaction) {
            if ($transaction->nature === 'debit') {
                $summary['total_debit'] += $transaction->amount;
            } else {
                $summary['total_credit'] += $transaction->amount;
            }
        }

        $summary['net_movement'] = $summary['total_debit'] - $summary['total_credit'];

        return $summary;
    }

    public function export(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get filter parameters
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $reportType = $request->get('report_type', 'accrual');
        $accountId = $request->get('account_id', null);
        $branchId = $request->get('branch_id', null);
        $showOpeningBalance = $request->get('show_opening_balance', true);
        $groupBy = $request->get('group_by', 'account');
        $exportType = $request->get('export_type', 'pdf');

        // Get general ledger data
        $generalLedgerData = $this->getGeneralLedgerData(
            $startDate, 
            $endDate, 
            $reportType, 
            $accountId, 
            $branchId, 
            $showOpeningBalance, 
            $groupBy
        );

        if ($exportType === 'pdf') {
            return $this->exportPdf($generalLedgerData, $company, $startDate, $endDate, $reportType);
        } else {
            return $this->exportExcel($generalLedgerData, $company, $startDate, $endDate, $reportType);
        }
    }

    private function exportPdf($generalLedgerData, $company, $startDate, $endDate, $reportType)
    {
        // Determine branch name for header
        $user = Auth::user();
        $branches = $user->branches()
            ->where('branches.company_id', $company->id)
            ->select('branches.id', 'branches.name')
            ->get();

        $branchId = $generalLedgerData['filters']['branch_id'] ?? null;
        $branchName = 'All Branches';
        if ($branchId === 'all' && ($branches->count() ?? 0) <= 1) {
            $branchName = optional($branches->first())->name ?? 'All Branches';
        } elseif ($branchId && $branchId !== 'all') {
            $branch = $branches->firstWhere('id', $branchId);
            $branchName = $branch->name ?? 'Unknown Branch';
        }

        $pdf = Pdf::loadView('accounting.reports.general-ledger.pdf', [
            'generalLedgerData' => $generalLedgerData,
            'company' => $company,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'reportType' => $reportType,
            'groupBy' => $generalLedgerData['filters']['group_by'] ?? 'account',
            'branchName' => $branchName
        ]);

        $filename = 'general_ledger_' . $startDate . '_to_' . $endDate . '_' . $reportType . '.pdf';
        return $pdf->download($filename);
    }

    private function exportExcel($generalLedgerData, $company, $startDate, $endDate, $reportType)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', $company->name ?? 'SmartFinance');
        $sheet->setCellValue('A2', 'GENERAL LEDGER REPORT');
        $sheet->setCellValue('A3', 'Period: ' . \Carbon\Carbon::parse($startDate)->format('M d, Y') . ' to ' . \Carbon\Carbon::parse($endDate)->format('M d, Y'));
        $sheet->setCellValue('A4', 'Basis: ' . ucfirst($reportType));

        // Set column headers
        $sheet->setCellValue('A6', 'Date');
        $sheet->setCellValue('B6', 'Account Code');
        $sheet->setCellValue('C6', 'Account Name');
        $sheet->setCellValue('D6', 'Transaction ID');
        $sheet->setCellValue('E6', 'Description');
        $sheet->setCellValue('F6', 'Debit');
        $sheet->setCellValue('G6', 'Credit');
        $sheet->setCellValue('H6', 'Balance');

        $row = 7;

        $openingBalances = $generalLedgerData['opening_balances'] ?? [];
        $transactions = $generalLedgerData['transactions'] ?? [];

        $lastAccountId = null;

        foreach ($transactions as $transaction) {
            // Insert opening balance row if this is the first transaction for this account
            if ($transaction->chart_account_id !== $lastAccountId) {
                $ob = $openingBalances->get($transaction->chart_account_id);
                $opening = $ob ? (($ob->total_debit ?? 0) - ($ob->total_credit ?? 0)) : 0;

                $sheet->setCellValue('A' . $row, 'Opening Balance');
                $sheet->setCellValue('B' . $row, $transaction->account_code);
                $sheet->setCellValue('C' . $row, $transaction->account_name);
                $sheet->setCellValue('D' . $row, '');
                $sheet->setCellValue('E' . $row, '');
                $sheet->setCellValue('F' . $row, '');
                $sheet->setCellValue('G' . $row, '');
                $sheet->setCellValue('H' . $row, number_format($opening, 2));
                $row++;

                $lastAccountId = $transaction->chart_account_id;
            }

            // Transaction row
            $sheet->setCellValue('A' . $row, \Carbon\Carbon::parse($transaction->date)->format('M d, Y'));
            $sheet->setCellValue('B' . $row, $transaction->account_code);
            $sheet->setCellValue('C' . $row, $transaction->account_name);
            $sheet->setCellValue('D' . $row, $transaction->transaction_id);
            $sheet->setCellValue('E' . $row, $transaction->description);
            $sheet->setCellValue('F' . $row, $transaction->nature === 'debit' ? number_format($transaction->amount, 2) : '');
            $sheet->setCellValue('G' . $row, $transaction->nature === 'credit' ? number_format($transaction->amount, 2) : '');
            $sheet->setCellValue('H' . $row, number_format($transaction->running_balance, 2));
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $filename = 'general_ledger_' . $startDate . '_to_' . $endDate . '_' . $reportType . '.xlsx';
        
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'general_ledger');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend();
    }
}
