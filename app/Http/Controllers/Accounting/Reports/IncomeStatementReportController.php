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

class IncomeStatementReportController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->can('view income statement report')) {
            abort(403, 'Unauthorized access to this report.');
        }
        
        $user = Auth::user();
        $company = $user->company;

        // Get filter parameters
        $startDate = $request->get('start_date', now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $reportingType = $request->get('reporting_type', 'accrual');
        $branchId = $request->get('branch_id', 'all');
        $layout = $request->get('layout', 'standard');

        // Comparative columns
        $comparativeColumns = $request->get('comparative_columns', []);

        // Get branches for filter: only user's assigned branches
        $branches = $user->branches()->where('branches.company_id', $company->id)->get();

        // Get income statement data
        // Normalize branchId: allow 'all' only if user has more than one assigned branch
        if ($branchId === 'all' && $branches->count() <= 1) {
            $branchId = optional($branches->first())->id;
        }
        $incomeStatementData = $this->getIncomeStatementData($startDate, $endDate, $reportingType, $branchId, $layout, $comparativeColumns);

        return view('accounting.reports.income-statement.index', compact(
            'incomeStatementData',
            'startDate',
            'endDate',
            'reportingType',
            'branchId',
            'layout',
            'branches',
            'user',
            'comparativeColumns'
        ));
    }

    private function getIncomeStatementData($startDate, $endDate, $reportingType, $branchId, $layout, $comparativeColumns = [])
    {
        $user = Auth::user();
        $company = $user->company;

        // Get current period data
        $currentYearData = $this->getYearData($startDate, $endDate, $reportingType, $branchId);
        
        // Build comparative datasets keyed by provided names
        $comparativeData = [];
        foreach ($comparativeColumns as $index => $column) {
            if (!empty($column['start_date']) && !empty($column['end_date'])) {
                $label = isset($column['name']) && trim($column['name']) !== '' ? trim($column['name']) : ('Comparative ' . ($index + 1));
                $comp = $this->getYearData($column['start_date'], $column['end_date'], $reportingType, $branchId);
                $comparativeData[$label] = $comp;
            }
        }

        return [
            'data' => [
                'revenues' => $currentYearData['revenues'],
                'expenses' => $currentYearData['expenses'],
                'total_revenue' => $currentYearData['total_revenue'],
                'total_expenses' => $currentYearData['total_expenses'],
                'profit_loss' => $currentYearData['total_revenue'] - $currentYearData['total_expenses'],
            ],
            'comparative' => $comparativeData,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'reporting_type' => $reportingType,
            'branch_id' => $branchId,
            'layout' => $layout
        ];
    }

    private function getYearData($startDate, $endDate, $reportingType, $branchId)
    {
        $user = Auth::user();
        $company = $user->company;

        // Build the base query for income accounts
        $incomeQuery = DB::table('gl_transactions')
            ->join('chart_accounts', 'gl_transactions.chart_account_id', '=', 'chart_accounts.id')
            ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
            ->join('account_class', 'account_class_groups.class_id', '=', 'account_class.id')
            ->where('account_class_groups.company_id', $company->id)
            ->whereBetween('gl_transactions.date', [$startDate, $endDate])
            ->whereIn('account_class.name', ['income', 'revenue']);

        // Build the base query for expense accounts
        $expenseQuery = DB::table('gl_transactions')
            ->join('chart_accounts', 'gl_transactions.chart_account_id', '=', 'chart_accounts.id')
            ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
            ->join('account_class', 'account_class_groups.class_id', '=', 'account_class.id')
            ->where('account_class_groups.company_id', $company->id)
            ->whereBetween('gl_transactions.date', [$startDate, $endDate])
            ->whereIn('account_class.name', ['expenses', 'expense']);

        // Add branch filter: 'all' means all assigned branches
        $assignedBranchIds = Auth::user()->branches()->pluck('branches.id')->toArray();
        if ($branchId === 'all') {
            if (!empty($assignedBranchIds)) {
                $incomeQuery->whereIn('gl_transactions.branch_id', $assignedBranchIds);
                $expenseQuery->whereIn('gl_transactions.branch_id', $assignedBranchIds);
            }
        } elseif ($branchId) {
            $incomeQuery->where('gl_transactions.branch_id', $branchId);
            $expenseQuery->where('gl_transactions.branch_id', $branchId);
        } else {
            if (!empty($assignedBranchIds)) {
                $incomeQuery->whereIn('gl_transactions.branch_id', $assignedBranchIds);
                $expenseQuery->whereIn('gl_transactions.branch_id', $assignedBranchIds);
            }
        }

        // Add reporting type filter (cash vs accrual)
        if ($reportingType === 'cash') {
            // For cash basis, select all GL transactions that are part of the same transaction when any bank account is involved
            $incomeQuery->whereExists(function ($subquery) {
                $subquery->select(DB::raw(1))
                    ->from('gl_transactions as gl2')
                    ->whereColumn('gl2.transaction_id', 'gl_transactions.transaction_id')
                    ->whereColumn('gl2.transaction_type', 'gl_transactions.transaction_type')
                    ->whereIn('gl2.chart_account_id', function($bankSubquery) {
                        $bankSubquery->select('chart_account_id')
                            ->from('bank_accounts');
                    });
            });

            $expenseQuery->whereExists(function ($subquery) {
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

        // Select fields for income
        $incomeQuery->select(
            'chart_accounts.id as account_id',
            'chart_accounts.account_name',
            'chart_accounts.account_code',
            'account_class.name as class_name',
            DB::raw("SUM(CASE WHEN gl_transactions.nature = 'credit' THEN gl_transactions.amount ELSE -gl_transactions.amount END) as sum")
        )
        ->groupBy('chart_accounts.id', 'chart_accounts.account_name', 'chart_accounts.account_code', 'account_class.name');

        // Select fields for expenses
        $expenseQuery->select(
            'chart_accounts.id as account_id',
            'chart_accounts.account_name',
            'chart_accounts.account_code',
            'account_class.name as class_name',
            DB::raw("SUM(CASE WHEN gl_transactions.nature = 'debit' THEN gl_transactions.amount ELSE -gl_transactions.amount END) as sum")
        )
        ->groupBy('chart_accounts.id', 'chart_accounts.account_name', 'chart_accounts.account_code', 'account_class.name');

        $incomeAccounts = $incomeQuery->get();
        $expenseAccounts = $expenseQuery->get();

        // Group by account class groups
        $chartAccountsRevenues = $incomeAccounts->groupBy(function ($item) {
            return $item->class_name;
        })->map(function ($group) {
            return $group->map(function ($item) {
                return [
                    'account_id' => $item->account_id,
                    'account' => $item->account_name,
                    'account_code' => $item->account_code,
                    'sum' => (float) $item->sum
                ];
            })->values()->toArray();
        })->toArray();

        $chartAccountsExpenses = $expenseAccounts->groupBy(function ($item) {
            return $item->class_name;
        })->map(function ($group) {
            return $group->map(function ($item) {
                return [
                    'account_id' => $item->account_id,
                    'account' => $item->account_name,
                    'account_code' => $item->account_code,
                    'sum' => (float) $item->sum
                ];
            })->values()->toArray();
        })->toArray();

        // Calculate totals
        $totalRevenue = collect($chartAccountsRevenues)->flatten(1)->sum('sum');
        $totalExpenses = collect($chartAccountsExpenses)->flatten(1)->sum('sum');

        return [
            'revenues' => $chartAccountsRevenues,
            'expenses' => $chartAccountsExpenses,
            'total_revenue' => $totalRevenue,
            'total_expenses' => $totalExpenses
        ];
    }

    public function export(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get filter parameters
        $startDate = $request->get('start_date', now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $reportingType = $request->get('reporting_type', 'accrual');
        $branchId = $request->get('branch_id', 'all');
        $layout = $request->get('layout', 'standard');
        $exportType = $request->get('export_type', 'pdf');

        // Comparative columns
        $comparativeColumns = $request->get('comparative_columns', []);

        // Get income statement data
        $incomeStatementData = $this->getIncomeStatementData($startDate, $endDate, $reportingType, $branchId, $layout, $comparativeColumns);

        if ($exportType === 'pdf') {
            return $this->exportPdf($incomeStatementData, $company, $startDate, $endDate, $reportingType);
        } else {
            return $this->exportExcel($incomeStatementData, $company, $startDate, $endDate, $reportingType);
        }
    }

    private function exportPdf($incomeStatementData, $company, $startDate, $endDate, $reportingType)
    {
        $pdf = Pdf::loadView('accounting.reports.income-statement.pdf', [
            'incomeStatementData' => $incomeStatementData,
            'company' => $company,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'reportingType' => $reportingType
        ]);

        $filename = 'income_statement_' . $startDate . '_to_' . $endDate . '_' . $reportingType . '.pdf';
        return $pdf->download($filename);
    }

    private function exportExcel($incomeStatementData, $company, $startDate, $endDate, $reportingType)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', $company->name ?? 'SmartFinance');
        $sheet->setCellValue('A2', 'INCOME STATEMENT');
        $sheet->setCellValue('A3', 'Period: ' . Carbon::parse($startDate)->format('M d, Y') . ' to ' . Carbon::parse($endDate)->format('M d, Y'));
        $sheet->setCellValue('A4', 'Basis: ' . ucfirst($reportingType));

        $row = 6;

        // Revenue Section
        $sheet->setCellValue('A' . $row, 'INCOME');
        $row++;

        foreach ($incomeStatementData['data']['revenues'] as $groupName => $accounts) {
            $sheet->setCellValue('A' . $row, $groupName);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $row++;

            foreach ($accounts as $account) {
                $sheet->setCellValue('A' . $row, $account['account']);
                $sheet->setCellValue('B' . $row, number_format($account['sum'], 2));
                $row++;
            }
        }

        $sheet->setCellValue('A' . $row, 'TOTAL INCOME');
        $sheet->setCellValue('B' . $row, number_format($incomeStatementData['data']['total_revenue'], 2));
        $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
        $row += 2;

        // Expense Section
        $sheet->setCellValue('A' . $row, 'LESS EXPENSES');
        $row++;

        foreach ($incomeStatementData['data']['expenses'] as $groupName => $accounts) {
            $sheet->setCellValue('A' . $row, $groupName);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $row++;

            foreach ($accounts as $account) {
                $sheet->setCellValue('A' . $row, $account['account']);
                $sheet->setCellValue('B' . $row, number_format(abs($account['sum']), 2));
                $row++;
            }
        }

        $sheet->setCellValue('A' . $row, 'TOTAL EXPENSES');
        $sheet->setCellValue('B' . $row, number_format(abs($incomeStatementData['data']['total_expenses']), 2));
        $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
        $row++;

        $sheet->setCellValue('A' . $row, 'PROFIT / LOSS');
        $sheet->setCellValue('B' . $row, number_format($incomeStatementData['data']['profit_loss'], 2));
        $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);

        // Auto-size columns
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);

        $filename = 'income_statement_' . $startDate . '_to_' . $endDate . '_' . $reportingType . '.xlsx';
        
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'income_statement');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend();
    }
}
