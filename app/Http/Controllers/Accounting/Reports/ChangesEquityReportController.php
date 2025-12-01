<?php

namespace App\Http\Controllers\Accounting\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ChangesEquityReportController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->can('view changes in equity report')) {
            abort(403, 'Unauthorized access to this report.');
        }
        
        $user = Auth::user();
        $company = $user->company;
        
        // Get branches for admin users
        $branches = [];
        if ($user->hasRole('admin')) {
            $branches = DB::table('branches')
                ->where('company_id', $company->id)
                ->select('id', 'name')
                ->get();
        }

        // Set default values
        $fromDate = $request->get('from_date', now()->startOfYear()->format('Y-m-d'));
        $toDate = $request->get('to_date', now()->format('Y-m-d'));
        $branchId = $request->get('branch_id', $user->branch_id);
        $equityCategoryId = $request->get('equity_category_id', '');

        // Get equity categories
        $equityCategories = DB::table('equity_categories')->get();

        // Get changes in equity data
        $changesEquityData = $this->getChangesEquityData($fromDate, $toDate, $branchId, $equityCategoryId);

        return view('accounting.reports.changes-equity.index', compact(
            'changesEquityData',
            'branches',
            'fromDate',
            'toDate',
            'branchId',
            'equityCategoryId',
            'equityCategories',
            'user'
        ));
    }

    private function getChangesEquityData($fromDate, $toDate, $branchId, $equityCategoryId)
    {
        $user = Auth::user();
        $company = $user->company;

        // Build the base query for equity accounts
        $query = DB::table('gl_transactions')
            ->join('chart_accounts', 'gl_transactions.chart_account_id', '=', 'chart_accounts.id')
            ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
            ->join('account_class', 'account_class_groups.class_id', '=', 'account_class.id')
            ->leftJoin('equity_categories', 'chart_accounts.equity_category_id', '=', 'equity_categories.id')
            ->where('account_class_groups.company_id', $company->id)
            ->where('chart_accounts.has_equity', true)
            ->whereBetween('gl_transactions.date', [$fromDate, $toDate]);

        // Add branch filter if specified
        if ($branchId && $branchId != 'all') {
            $query->where('gl_transactions.branch_id', $branchId);
        }

        // Add equity category filter if specified
        if ($equityCategoryId) {
            $query->where('chart_accounts.equity_category_id', $equityCategoryId);
        }

        // Get the data
        $equityTransactions = $query->select(
            'chart_accounts.id as account_id',
            'chart_accounts.account_name',
            'chart_accounts.account_code',
            'equity_categories.name as equity_category_name',
            'equity_categories.description as equity_category_description',
            'gl_transactions.date',
            'gl_transactions.nature',
            'gl_transactions.amount',
            'gl_transactions.description as transaction_description',
            'gl_transactions.transaction_type',
            'gl_transactions.transaction_id'
        )
        ->orderBy('gl_transactions.date')
        ->orderBy('equity_categories.name')
        ->orderBy('chart_accounts.account_name')
        ->get();

        // Group data by equity category
        $groupedData = [];
        $categoryTotals = [];
        $overallTotal = 0;

        foreach ($equityTransactions as $transaction) {
            $categoryName = $transaction->equity_category_name ?? 'Uncategorized';
            
            if (!isset($groupedData[$categoryName])) {
                $groupedData[$categoryName] = [];
                $categoryTotals[$categoryName] = [
                    'debit_total' => 0,
                    'credit_total' => 0,
                    'net_change' => 0
                ];
            }

            // Calculate the impact (credit increases equity, debit decreases equity)
            $impact = $transaction->nature === 'credit' ? $transaction->amount : -$transaction->amount;
            
            $groupedData[$categoryName][] = [
                'date' => $transaction->date,
                'account_name' => $transaction->account_name,
                'account_code' => $transaction->account_code,
                'description' => $transaction->transaction_description,
                'nature' => $transaction->nature,
                'amount' => $transaction->amount,
                'impact' => $impact,
                'transaction_type' => $transaction->transaction_type
            ];

            // Update category totals
            if ($transaction->nature === 'debit') {
                $categoryTotals[$categoryName]['debit_total'] += $transaction->amount;
            } else {
                $categoryTotals[$categoryName]['credit_total'] += $transaction->amount;
            }
            $categoryTotals[$categoryName]['net_change'] += $impact;
            $overallTotal += $impact;
        }

        // Get opening balance (equity balance before the report period)
        $openingBalance = $this->getOpeningEquityBalance($fromDate, $branchId, $equityCategoryId);

        return [
            'grouped_data' => $groupedData,
            'category_totals' => $categoryTotals,
            'overall_total' => $overallTotal,
            'opening_balance' => $openingBalance,
            'closing_balance' => $openingBalance + $overallTotal,
            'filters' => [
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'branch_id' => $branchId,
                'equity_category_id' => $equityCategoryId
            ]
        ];
    }

    private function getOpeningEquityBalance($fromDate, $branchId, $equityCategoryId)
    {
        $user = Auth::user();
        $company = $user->company;

        $query = DB::table('gl_transactions')
            ->join('chart_accounts', 'gl_transactions.chart_account_id', '=', 'chart_accounts.id')
            ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
            ->where('account_class_groups.company_id', $company->id)
            ->where('chart_accounts.has_equity', true)
            ->where('gl_transactions.date', '<', $fromDate);

        // Add branch filter if specified
        if ($branchId && $branchId != 'all') {
            $query->where('gl_transactions.branch_id', $branchId);
        }

        // Add equity category filter if specified
        if ($equityCategoryId) {
            $query->where('chart_accounts.equity_category_id', $equityCategoryId);
        }

        $result = $query->select(
            DB::raw('SUM(CASE WHEN gl_transactions.nature = "credit" THEN gl_transactions.amount ELSE 0 END) as credit_total'),
            DB::raw('SUM(CASE WHEN gl_transactions.nature = "debit" THEN gl_transactions.amount ELSE 0 END) as debit_total')
        )->first();

        return ($result->credit_total ?? 0) - ($result->debit_total ?? 0);
    }

    public function export(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get filter parameters
        $fromDate = $request->get('from_date', now()->startOfYear()->format('Y-m-d'));
        $toDate = $request->get('to_date', now()->format('Y-m-d'));
        $branchId = $request->get('branch_id', $user->branch_id);
        $equityCategoryId = $request->get('equity_category_id', '');
        $exportType = $request->get('export_type', 'pdf');

        // Get changes in equity data
        $changesEquityData = $this->getChangesEquityData($fromDate, $toDate, $branchId, $equityCategoryId);

        if ($exportType === 'excel') {
            return $this->exportExcel($changesEquityData, $company, $fromDate, $toDate);
        } else {
            return $this->exportPdf($changesEquityData, $company, $fromDate, $toDate);
        }
    }

    private function exportPdf($changesEquityData, $company, $fromDate, $toDate)
    {
        $user = Auth::user();
        
        // Get branches for header
        $branches = [];
        if ($user->hasRole('admin')) {
            $branches = DB::table('branches')
                ->where('company_id', $company->id)
                ->select('id', 'name')
                ->get();
        }
        
        // Generate PDF
        $pdf = \PDF::loadView('accounting.reports.changes-equity.pdf', compact(
            'changesEquityData', 
            'company', 
            'branches',
            'fromDate',
            'toDate'
        ));
        $pdf->setPaper('A4', 'portrait');
        
        $filename = 'changes_in_equity_' . $fromDate . '_to_' . $toDate . '.pdf';
        return $pdf->download($filename);
    }

    private function exportExcel($changesEquityData, $company, $fromDate, $toDate)
    {
        $spreadsheet = new Spreadsheet();
        
        // Set document properties
        $spreadsheet->getProperties()
            ->setCreator($company->name ?? 'SmartFinance')
            ->setLastModifiedBy($company->name ?? 'SmartFinance')
            ->setTitle('Changes in Equity Report')
            ->setSubject('Changes in Equity from ' . Carbon::parse($fromDate)->format('F d, Y') . ' to ' . Carbon::parse($toDate)->format('F d, Y'))
            ->setDescription('Changes in Equity Report generated on ' . now()->format('F d, Y \a\t g:i A'));

        // Create worksheet
        $worksheet = $spreadsheet->getActiveSheet();
        $worksheet->setTitle('Changes in Equity');

        // Set headers
        $worksheet->setCellValue('A1', $company->name ?? 'SmartFinance');
        $worksheet->setCellValue('A2', 'CHANGES IN EQUITY');
        $worksheet->setCellValue('A3', 'From ' . Carbon::parse($fromDate)->format('F d, Y') . ' to ' . Carbon::parse($toDate)->format('F d, Y'));
        $worksheet->setCellValue('A4', 'Generated: ' . now()->format('F d, Y \a\t g:i A'));

        // Set column headers
        $row = 6;
        $worksheet->setCellValue('A' . $row, 'Date');
        $worksheet->setCellValue('B' . $row, 'Account');
        $worksheet->setCellValue('C' . $row, 'Description');
        $worksheet->setCellValue('D' . $row, 'Nature');
        $worksheet->setCellValue('E' . $row, 'Amount');
        $worksheet->setCellValue('F' . $row, 'Impact');
        $worksheet->setCellValue('G' . $row, 'Category');

        // Style headers
        $worksheet->getStyle('A' . $row . ':G' . $row)->getFont()->setBold(true);
        $worksheet->getStyle('A' . $row . ':G' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('E0E0E0');

        $row++;

        // Add data by category
        foreach ($changesEquityData['grouped_data'] as $categoryName => $transactions) {
            // Add category header
            $worksheet->setCellValue('A' . $row, $categoryName);
            $worksheet->getStyle('A' . $row)->getFont()->setBold(true);
            $worksheet->getStyle('A' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('28A745');
            $worksheet->getStyle('A' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));
            $row++;

            // Add transactions
            foreach ($transactions as $transaction) {
                $worksheet->setCellValue('A' . $row, Carbon::parse($transaction['date'])->format('M d, Y'));
                $worksheet->setCellValue('B' . $row, $transaction['account_name'] . ' (' . $transaction['account_code'] . ')');
                $worksheet->setCellValue('C' . $row, $transaction['description']);
                $worksheet->setCellValue('D' . $row, ucfirst($transaction['nature']));
                $worksheet->setCellValue('E' . $row, $transaction['amount']);
                $worksheet->setCellValue('F' . $row, $transaction['impact']);
                $worksheet->setCellValue('G' . $row, $categoryName);

                // Format numbers
                $worksheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
                $worksheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

                // Color code impact
                if ($transaction['impact'] > 0) {
                    $worksheet->getStyle('F' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_GREEN));
                } else {
                    $worksheet->getStyle('F' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED));
                }

                $row++;
            }

            // Add category total
            $worksheet->setCellValue('A' . $row, 'Total for ' . $categoryName);
            $worksheet->getStyle('A' . $row)->getFont()->setBold(true);
            $worksheet->setCellValue('F' . $row, $changesEquityData['category_totals'][$categoryName]['net_change']);
            $worksheet->getStyle('F' . $row)->getFont()->setBold(true);
            $worksheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $row++;
            $row++; // Add spacing
        }

        // Add summary section
        $row++;
        $worksheet->setCellValue('A' . $row, 'SUMMARY');
        $worksheet->getStyle('A' . $row)->getFont()->setBold(true);
        $worksheet->getStyle('A' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('007BFF');
        $worksheet->getStyle('A' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));
        $row++;

        $worksheet->setCellValue('A' . $row, 'Opening Balance');
        $worksheet->setCellValue('B' . $row, $changesEquityData['opening_balance']);
        $worksheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        $row++;

        $worksheet->setCellValue('A' . $row, 'Net Change');
        $worksheet->setCellValue('B' . $row, $changesEquityData['overall_total']);
        $worksheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        $row++;

        $worksheet->setCellValue('A' . $row, 'Closing Balance');
        $worksheet->setCellValue('B' . $row, $changesEquityData['closing_balance']);
        $worksheet->getStyle('B' . $row)->getFont()->setBold(true);
        $worksheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

        // Auto-size columns
        foreach (range('A', 'G') as $col) {
            $worksheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Create Excel file
        $writer = new Xlsx($spreadsheet);
        $filename = 'changes_in_equity_' . $fromDate . '_to_' . $toDate . '.xlsx';
        
        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
