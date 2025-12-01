<?php

namespace App\Http\Controllers\Accounting\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CashFlowReportController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->can('view cash flow report')) {
            abort(403, 'Unauthorized access to this report.');
        }
        
        $user = Auth::user();
        $company = $user->company;
        
        // Get branches visible to the user: only assigned branches
        $branches = $user->branches()
            ->where('branches.company_id', $company->id)
            ->select('branches.id', 'branches.name')
            ->get();

        // Set default values
        $fromDate = $request->get('from_date', now()->startOfYear()->format('Y-m-d'));
        $toDate = $request->get('to_date', now()->format('Y-m-d'));
        $branchParam = $request->get('branch_id');
        $branchId = ($branches->count() > 1 && $branchParam === 'all') ? 'all' : ($branchParam ?: ($branches->first()->id ?? null));
        $cashFlowCategoryId = $request->get('cash_flow_category_id', '');

        // Get cash flow categories
        $cashFlowCategories = DB::table('cash_flow_categories')->get();

        // Get cash flow data
        $cashFlowData = $this->getCashFlowData($fromDate, $toDate, $branchId, $cashFlowCategoryId);

        return view('accounting.reports.cash-flow.index', compact(
            'cashFlowData',
            'branches',
            'fromDate',
            'toDate',
            'branchId',
            'cashFlowCategoryId',
            'cashFlowCategories',
            'user'
        ));
    }

    private function getCashFlowData($fromDate, $toDate, $branchId, $cashFlowCategoryId)
    {
        $user = Auth::user();
        $company = $user->company;

        // Build the base query for cash flow accounts
        $query = DB::table('gl_transactions')
            ->join('chart_accounts', 'gl_transactions.chart_account_id', '=', 'chart_accounts.id')
            ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
            ->join('account_class', 'account_class_groups.class_id', '=', 'account_class.id')
            ->leftJoin('cash_flow_categories', 'chart_accounts.cash_flow_category_id', '=', 'cash_flow_categories.id')
            ->where('account_class_groups.company_id', $company->id)
            ->where('chart_accounts.has_cash_flow', true)
            ->whereBetween('gl_transactions.date', [$fromDate, $toDate]);

        // Add branch filter for assigned branches / all assigned
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

        // Add cash flow category filter if specified
        if ($cashFlowCategoryId) {
            $query->where('chart_accounts.cash_flow_category_id', $cashFlowCategoryId);
        }

        // Get the data
        $cashFlowTransactions = $query->select(
            'chart_accounts.id as account_id',
            'chart_accounts.account_name',
            'chart_accounts.account_code',
            'cash_flow_categories.name as cash_flow_category_name',
            'cash_flow_categories.description as cash_flow_category_description',
            'gl_transactions.date',
            'gl_transactions.nature',
            'gl_transactions.amount',
            'gl_transactions.description as transaction_description',
            'gl_transactions.transaction_type',
            'gl_transactions.transaction_id'
        )
        ->orderBy('gl_transactions.date')
        ->orderBy('cash_flow_categories.name')
        ->orderBy('chart_accounts.account_name')
        ->get();

        // Group data by cash flow category
        $groupedData = [];
        $categoryTotals = [];
        $overallTotal = 0;

        foreach ($cashFlowTransactions as $transaction) {
            $categoryName = $transaction->cash_flow_category_name ?? 'Uncategorized';
            
            if (!isset($groupedData[$categoryName])) {
                $groupedData[$categoryName] = [];
                $categoryTotals[$categoryName] = [
                    'debit_total' => 0,
                    'credit_total' => 0,
                    'net_change' => 0
                ];
            }

            // Calculate the cash flow impact
            // For cash flow: credit increases cash, debit decreases cash
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

        // Get opening cash balance (cash balance before the report period)
        $openingBalance = $this->getOpeningCashBalance($fromDate, $branchId, $cashFlowCategoryId);

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
                'cash_flow_category_id' => $cashFlowCategoryId
            ]
        ];
    }

    private function getOpeningCashBalance($fromDate, $branchId, $cashFlowCategoryId)
    {
        $user = Auth::user();
        $company = $user->company;

        $query = DB::table('gl_transactions')
            ->join('chart_accounts', 'gl_transactions.chart_account_id', '=', 'chart_accounts.id')
            ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
            ->where('account_class_groups.company_id', $company->id)
            ->where('chart_accounts.has_cash_flow', true)
            ->where('gl_transactions.date', '<', $fromDate);

        // Add branch filter for opening balance
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

        // Add cash flow category filter if specified
        if ($cashFlowCategoryId) {
            $query->where('chart_accounts.cash_flow_category_id', $cashFlowCategoryId);
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
        $cashFlowCategoryId = $request->get('cash_flow_category_id', '');
        $exportType = $request->get('export_type', 'pdf');

        // Get cash flow data
        $cashFlowData = $this->getCashFlowData($fromDate, $toDate, $branchId, $cashFlowCategoryId);

        if ($exportType === 'excel') {
            return $this->exportExcel($cashFlowData, $company, $fromDate, $toDate);
        } else {
            return $this->exportPdf($cashFlowData, $company, $fromDate, $toDate);
        }
    }

    private function exportPdf($cashFlowData, $company, $fromDate, $toDate)
    {
        $user = Auth::user();
        
        // Get branches for header: use user's assigned branches
        $branches = $user->branches()
            ->where('branches.company_id', $company->id)
            ->select('branches.id', 'branches.name')
            ->get();
        
        // Resolve branch name for display
        $branchId = $cashFlowData['filters']['branch_id'] ?? null;
        $branchName = 'All Branches';
        if ($branchId && $branchId !== 'all') {
            $branch = $branches->firstWhere('id', $branchId);
            $branchName = $branch->name ?? 'Unknown Branch';
        } elseif (($branches->count() ?? 0) <= 1 && $branchId === 'all') {
            // If only one assigned branch, show its name instead of All
            $branchName = optional($branches->first())->name ?? 'All Branches';
        }
        
        // Generate PDF
        $pdf = \PDF::loadView('accounting.reports.cash-flow.pdf', compact(
            'cashFlowData', 
            'company', 
            'branches',
            'branchName',
            'fromDate',
            'toDate'
        ));
        $pdf->setPaper('A4', 'portrait');
        
        $filename = 'cash_flow_' . $fromDate . '_to_' . $toDate . '.pdf';
        return $pdf->download($filename);
    }

    private function exportExcel($cashFlowData, $company, $fromDate, $toDate)
    {
        $spreadsheet = new Spreadsheet();
        
        // Set document properties
        $spreadsheet->getProperties()
            ->setCreator($company->name ?? 'SmartFinance')
            ->setLastModifiedBy($company->name ?? 'SmartFinance')
            ->setTitle('Cash Flow Report')
            ->setSubject('Cash Flow from ' . Carbon::parse($fromDate)->format('F d, Y') . ' to ' . Carbon::parse($toDate)->format('F d, Y'))
            ->setDescription('Cash Flow Report generated on ' . now()->format('F d, Y \a\t g:i A'));

        // Create worksheet
        $worksheet = $spreadsheet->getActiveSheet();
        $worksheet->setTitle('Cash Flow');

        // Set headers
        $worksheet->setCellValue('A1', $company->name ?? 'SmartFinance');
        $worksheet->setCellValue('A2', 'CASH FLOW STATEMENT');
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
        foreach ($cashFlowData['grouped_data'] as $categoryName => $transactions) {
            // Add category header
            $worksheet->setCellValue('A' . $row, $categoryName);
            $worksheet->getStyle('A' . $row)->getFont()->setBold(true);
            $worksheet->getStyle('A' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('007BFF');
            $worksheet->getStyle('A' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));
            $row++;

            // Add transactions
            foreach ($transactions as $transaction) {
                $worksheet->setCellValue('A' . $row, Carbon::parse($transaction['date'])->format('M d, Y'));
                $worksheet->setCellValue('B' . $row, $transaction['account_name'] . ' (' . $transaction['account_code'] . ')');
                $worksheet->setCellValue('C' . $row, $transaction['description'] ?: 'No description');
                $worksheet->setCellValue('D' . $row, ucfirst($transaction['nature']));
                $worksheet->setCellValue('E' . $row, $transaction['amount']);
                $worksheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
                $worksheet->setCellValue('F' . $row, $transaction['impact']);
                $worksheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
                $worksheet->setCellValue('G' . $row, $categoryName);

                // Color code the impact
                if ($transaction['impact'] >= 0) {
                    $worksheet->getStyle('F' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_GREEN));
                } else {
                    $worksheet->getStyle('F' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED));
                }

                $row++;
            }

            // Add category total
            $worksheet->setCellValue('A' . $row, 'Total for ' . $categoryName);
            $worksheet->getStyle('A' . $row)->getFont()->setBold(true);
            $worksheet->setCellValue('F' . $row, $cashFlowData['category_totals'][$categoryName]['net_change']);
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

        $worksheet->setCellValue('A' . $row, 'Opening Cash Balance');
        $worksheet->setCellValue('B' . $row, $cashFlowData['opening_balance']);
        $worksheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        $row++;

        $worksheet->setCellValue('A' . $row, 'Net Cash Flow');
        $worksheet->setCellValue('B' . $row, $cashFlowData['overall_total']);
        $worksheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        $row++;

        $worksheet->setCellValue('A' . $row, 'Closing Cash Balance');
        $worksheet->setCellValue('B' . $row, $cashFlowData['closing_balance']);
        $worksheet->getStyle('B' . $row)->getFont()->setBold(true);
        $worksheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

        // Auto-size columns
        foreach (range('A', 'G') as $col) {
            $worksheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Create Excel file
        $writer = new Xlsx($spreadsheet);
        $filename = 'cash_flow_' . $fromDate . '_to_' . $toDate . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
}
