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

class ExpensesSummaryReportController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->can('view expenses summary report')) {
            abort(403, 'Unauthorized access to this report.');
        }
        
        $user = Auth::user();
        $company = $user->company;

        // Get filter parameters
        $startDate = $request->get('start_date', now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $reportingType = $request->get('reporting_type', 'accrual');
        $branchId = $request->get('branch_id', 'all');
        $groupBy = $request->get('group_by', 'account'); // account, group, date
        $sortBy = $request->get('sort_by', 'amount'); // amount, date, account

        // Get comparative columns from request
        $comparativeColumns = $request->get('comparative_columns', []);

        // Get assigned branches for filtering
        $branches = $user->branches()
            ->where('branches.company_id', $company->id)
            ->select('branches.id', 'branches.name')
            ->get();

        // If user has exactly one branch, force-select it
        if (($branches->count() ?? 0) === 1) {
            $branchId = $branches->first()->id;
        }

        // Get expenses summary data
        $expensesData = $this->getExpensesData($startDate, $endDate, $reportingType, $branchId, $groupBy, $sortBy, $comparativeColumns);

        return view('accounting.reports.expenses-summary.index', compact(
            'expensesData',
            'startDate',
            'endDate',
            'reportingType',
            'branchId',
            'groupBy',
            'sortBy',
            'comparativeColumns',
            'branches',
            'user'
        ));
    }

    private function getExpensesData($startDate, $endDate, $reportingType, $branchId, $groupBy, $sortBy, $comparativeColumns = [])
    {
        $user = Auth::user();
        $company = $user->company;

        // Build the base query for expense accounts
        $query = DB::table('gl_transactions')
            ->join('chart_accounts', 'gl_transactions.chart_account_id', '=', 'chart_accounts.id')
            ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
            ->join('account_class', 'account_class_groups.class_id', '=', 'account_class.id')
            ->where('account_class_groups.company_id', $company->id)
            ->whereBetween('gl_transactions.date', [$startDate, $endDate])
            ->whereIn('account_class.name', ['expenses', 'expense']);

        // Constrain to user's assigned branches always
        $assignedBranchIds = $user->branches()
            ->where('branches.company_id', $company->id)
            ->pluck('branches.id')
            ->toArray();

        if (empty($assignedBranchIds)) {
            return [
                'expenses' => collect([]),
                'comparative' => [],
                'summary' => [
                    'total_expenses' => 0,
                    'total_transactions' => 0,
                    'account_count' => 0,
                    'average_per_transaction' => 0,
                    'average_per_account' => 0
                ],
                'filters' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'reporting_type' => $reportingType,
                    'branch_id' => $branchId,
                    'group_by' => $groupBy,
                    'sort_by' => $sortBy
                ]
            ];
        }

        if ($branchId === 'all') {
            $query->whereIn('gl_transactions.branch_id', $assignedBranchIds);
        } else {
            $query->where('gl_transactions.branch_id', $branchId)
                  ->whereIn('gl_transactions.branch_id', $assignedBranchIds);
        }

        // Add reporting type filter (cash vs accrual)
        if ($reportingType === 'cash') {
            // For cash basis, select all GL transactions that are part of the same transaction when any bank account is involved
            $query->whereExists(function ($subquery) {
                $subquery->select(DB::raw(1))
                    ->from('gl_transactions as gl2')
                    ->whereColumn('gl2.transaction_id', 'gl_transactions.transaction_id')
                    ->whereColumn('gl2.transaction_type', 'gl_transactions.transaction_type')
                    ->whereIn('gl2.chart_account_id', function ($bankSubquery) {
                        $bankSubquery->select('chart_account_id')
                            ->from('bank_accounts');
                    });
            });
        }

        // Select fields based on group by
        if ($groupBy === 'group') {
            $query->select(
                'account_class_groups.id as group_id',
                'account_class_groups.name as group_name',
                'account_class.name as class_name',
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "debit" THEN gl_transactions.amount ELSE 0 END) as total_debit'),
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "credit" THEN gl_transactions.amount ELSE 0 END) as total_credit'),
                DB::raw('COUNT(DISTINCT chart_accounts.id) as account_count'),
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('MIN(gl_transactions.date) as first_transaction_date'),
                DB::raw('MAX(gl_transactions.date) as last_transaction_date')
            )
                ->groupBy('account_class_groups.id', 'account_class_groups.name', 'account_class.name');
        } else {
            // Individual transactions
            $query->select(
                'gl_transactions.id as transaction_id',
                'gl_transactions.date',
                'gl_transactions.amount',
                'gl_transactions.nature',
                'gl_transactions.description',
                'gl_transactions.transaction_id as voucher_id',
                'gl_transactions.transaction_type',
                'chart_accounts.id as account_id',
                'chart_accounts.account_name',
                'chart_accounts.account_code',
                'account_class_groups.name as group_name',
                'account_class.name as class_name'
            )
                ->orderBy('gl_transactions.date', 'asc')
                ->orderBy('gl_transactions.id', 'asc');
        }

        // Add sorting
        if ($groupBy === 'group') {
            if ($sortBy === 'amount') {
                $query->orderBy(DB::raw('SUM(CASE WHEN gl_transactions.nature = "debit" THEN gl_transactions.amount ELSE 0 END)'), 'desc');
            } else {
                $query->orderBy('account_class_groups.name');
            }
        } else {
            // Individual transactions
            if ($sortBy === 'amount') {
                $query->orderBy('gl_transactions.amount', 'desc');
            } elseif ($sortBy === 'date') {
                $query->orderBy('gl_transactions.date', 'desc');
            } else {
                $query->orderBy('chart_accounts.account_code', 'asc');
            }
        }

        $expenses = $query->get();

        // Calculate totals and process data
        $totalExpenses = 0;
        $totalTransactions = 0;
        $accountCount = 0;

        if ($groupBy === 'group') {
            foreach ($expenses as $expense) {
                $expense->net_amount = $expense->total_debit - $expense->total_credit;
                $totalExpenses += $expense->net_amount;
                $totalTransactions += $expense->transaction_count;
                $accountCount += $expense->account_count;
            }
        } else {
            $totalTransactions = $expenses->count();
            $accountCount = $expenses->unique('account_id')->count();

            foreach ($expenses as $expense) {
                if ($expense->nature === 'debit') {
                    $totalExpenses += $expense->amount;
                } else {
                    $totalExpenses -= $expense->amount;
                }
            }
        }

        // Process comparative data
        $comparativeData = [];
        if (!empty($comparativeColumns)) {
            foreach ($comparativeColumns as $column) {
                if (!empty($column['start_date']) && !empty($column['end_date'])) {
                    $comparativeData[$column['name']] = $this->getExpensesData(
                        $column['start_date'],
                        $column['end_date'],
                        $reportingType,
                        $branchId,
                        $groupBy,
                        $sortBy
                    );
                }
            }
        }

        return [
            'expenses' => $expenses,
            'comparative' => $comparativeData,
            'summary' => [
                'total_expenses' => $totalExpenses,
                'total_transactions' => $totalTransactions,
                'account_count' => $accountCount,
                'average_per_transaction' => $totalTransactions > 0 ? $totalExpenses / $totalTransactions : 0,
                'average_per_account' => $accountCount > 0 ? $totalExpenses / $accountCount : 0
            ],
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'reporting_type' => $reportingType,
                'branch_id' => $branchId,
                'group_by' => $groupBy,
                'sort_by' => $sortBy
            ]
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
        $groupBy = $request->get('group_by', 'account');
        $sortBy = $request->get('sort_by', 'amount');
        $exportType = $request->get('export_type', 'pdf');

        // Get comparative columns from request
        $comparativeColumns = $request->get('comparative_columns', []);

        // Get expenses data
        $expensesData = $this->getExpensesData($startDate, $endDate, $reportingType, $branchId, $groupBy, $sortBy, $comparativeColumns);

        if ($exportType === 'pdf') {
            return $this->exportPdf($expensesData, $company, $startDate, $endDate, $reportingType);
        } else {
            return $this->exportExcel($expensesData, $company, $startDate, $endDate, $reportingType);
        }
    }

    private function exportPdf($expensesData, $company, $startDate, $endDate, $reportingType)
    {
        $pdf = PDF::loadView('accounting.reports.expenses-summary.pdf', compact('expensesData', 'company', 'startDate', 'endDate', 'reportingType'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('expenses_summary_report.pdf');
    }

    private function exportExcel($expensesData, $company, $startDate, $endDate, $reportingType)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $sheet->setCellValue('A1', 'EXPENSES SUMMARY REPORT');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);

        // Set company info
        $sheet->setCellValue('A2', 'Company: ' . $company->name);
        $sheet->mergeCells('A2:H2');
        $sheet->setCellValue('A3', 'Period: ' . Carbon::parse($startDate)->format('d/m/Y') . ' to ' . Carbon::parse($endDate)->format('d/m/Y'));
        $sheet->mergeCells('A3:H3');
        $sheet->setCellValue('A4', 'Reporting Type: ' . ucfirst($reportingType));
        $sheet->mergeCells('A4:H4');

        // Set headers based on group by
        $row = 6;
        if ($expensesData['filters']['group_by'] === 'group') {
            $sheet->setCellValue('A' . $row, 'Account Group');
            $sheet->setCellValue('B' . $row, 'Total Debit');
            $sheet->setCellValue('C' . $row, 'Total Credit');
            $sheet->setCellValue('D' . $row, 'Net Amount');
            $sheet->setCellValue('E' . $row, 'Account Count');
            $sheet->setCellValue('F' . $row, 'Transaction Count');
        } else {
            $sheet->setCellValue('A' . $row, 'Date');
            $sheet->setCellValue('B' . $row, 'Account Code');
            $sheet->setCellValue('C' . $row, 'Account Name');
            $sheet->setCellValue('D' . $row, 'Account Group');
            $sheet->setCellValue('E' . $row, 'Description');
            $sheet->setCellValue('F' . $row, 'Amount');
        }

        // Style headers
        $sheet->getStyle('A' . $row . ':H' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':H' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('E0E0E0');

        // Add data
        $row++;
        foreach ($expensesData['expenses'] as $expense) {
            if ($expensesData['filters']['group_by'] === 'group') {
                $sheet->setCellValue('A' . $row, $expense->group_name);
                $sheet->setCellValue('B' . $row, number_format($expense->total_debit, 2));
                $sheet->setCellValue('C' . $row, number_format($expense->total_credit, 2));
                $sheet->setCellValue('D' . $row, number_format($expense->net_amount, 2));
                $sheet->setCellValue('E' . $row, $expense->account_count);
                $sheet->setCellValue('F' . $row, $expense->transaction_count);
            } else {
                $sheet->setCellValue('A' . $row, Carbon::parse($expense->date)->format('d/m/Y'));
                $sheet->setCellValue('B' . $row, $expense->account_code);
                $sheet->setCellValue('C' . $row, $expense->account_name);
                $sheet->setCellValue('D' . $row, $expense->group_name);
                $sheet->setCellValue('E' . $row, $expense->description);
                $sheet->setCellValue('F' . $row, number_format($expense->amount, 2));
            }
            $row++;
        }

        // Add summary
        $row++;
        $sheet->setCellValue('A' . $row, 'SUMMARY');
        $sheet->mergeCells('A' . $row . ':B' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);

        $row++;
        $sheet->setCellValue('A' . $row, 'Total Expenses:');
        $sheet->setCellValue('B' . $row, number_format($expensesData['summary']['total_expenses'], 2));
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);

        $row++;
        $sheet->setCellValue('A' . $row, 'Total Transactions:');
        $sheet->setCellValue('B' . $row, $expensesData['summary']['total_transactions']);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);

        $row++;
        $sheet->setCellValue('A' . $row, 'Account Count:');
        $sheet->setCellValue('B' . $row, $expensesData['summary']['account_count']);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);

        $row++;
        $sheet->setCellValue('A' . $row, 'Average per Transaction:');
        $sheet->setCellValue('B' . $row, number_format($expensesData['summary']['average_per_transaction'], 2));
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);

        $row++;
        $sheet->setCellValue('A' . $row, 'Average per Account:');
        $sheet->setCellValue('B' . $row, number_format($expensesData['summary']['average_per_account'], 2));
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);

        // Auto-size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Create the Excel file
        $writer = new Xlsx($spreadsheet);
        $filename = 'expenses_summary_report_' . date('Y-m-d_H-i-s') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}
