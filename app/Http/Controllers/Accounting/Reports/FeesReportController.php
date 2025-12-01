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

class FeesReportController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->can('view fees report')) {
            abort(403, 'Unauthorized access to this report.');
        }
        
        $user = Auth::user();
        $company = $user->company;

        // Get filter parameters
        $startDate = $request->get('start_date', now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $branchId = $request->get('branch_id', 'all');
        $feeId = $request->get('fee_id', 'all');

        // Get user's assigned branches
        $branches = $user->branches()
            ->where('branches.company_id', $company->id)
            ->get();

        // If user has only one assigned branch, force-select it
        if (($branches->count() ?? 0) === 1) {
            $branchId = $branches->first()->id;
        }
        
        // Get all fees from fees table
        $fees = \App\Models\Fee::where('company_id', $company->id)
            ->where('status', 'active')
            ->get();

        // Get fees data
        $feesData = $this->getFeesData($startDate, $endDate, $branchId, $feeId);

        return view('accounting.reports.fees.index', compact(
            'feesData',
            'startDate',
            'endDate',
            'branchId',
            'feeId',
            'branches',
            'fees',
            'user'
        ));
    }

    private function getFeesData($startDate, $endDate, $branchId, $feeId)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get chart account IDs from fees table
        $feeQuery = \App\Models\Fee::where('company_id', $company->id)
            ->where('status', 'active');

        // Apply fee filter
        if ($feeId !== 'all') {
            $feeQuery->where('id', $feeId);
        }

        $chartAccountIds = $feeQuery->pluck('chart_account_id')->toArray();

        // If no chart account IDs found, return empty result
        if (empty($chartAccountIds)) {
            return [
                'data' => collect([]),
                'summary' => [
                    'total_debit' => 0,
                    'total_credit' => 0,
                    'total_transactions' => 0,
                    'unique_fees' => 0,
                    'unique_customers' => 0,
                    'balance' => 0
                ]
            ];
        }

        // Build query for GL transactions using chart account IDs from fees
        $query = DB::table('gl_transactions as gl')
            ->join('chart_accounts as ca', 'gl.chart_account_id', '=', 'ca.id')
            ->leftJoin('customers as c', 'gl.customer_id', '=', 'c.id')
            ->leftJoin('branches as b', 'gl.branch_id', '=', 'b.id')
            ->whereIn('gl.chart_account_id', $chartAccountIds)
            ->whereBetween('gl.date', [$startDate, $endDate]);

        // Constrain to user's assigned branches always
        $assignedBranchIds = $user->branches()
            ->where('branches.company_id', $company->id)
            ->pluck('branches.id')
            ->toArray();

        // If user has no branches assigned, return empty result
        if (empty($assignedBranchIds)) {
            return [
                'data' => collect([]),
                'summary' => [
                    'total_debit' => 0,
                    'total_credit' => 0,
                    'total_transactions' => 0,
                    'unique_fees' => 0,
                    'unique_customers' => 0,
                    'balance' => 0
                ]
            ];
        }

        if ($branchId === 'all') {
            // Sum across only assigned branches
            $query->whereIn('gl.branch_id', $assignedBranchIds);
        } else {
            // Ensure selected branch is within assigned branches
            $query->where('gl.branch_id', $branchId)
                  ->whereIn('gl.branch_id', $assignedBranchIds);
        }

        $query->select(
            'gl.id as transaction_id',
            'gl.date',
            'gl.amount',
            'gl.nature',
            'gl.description',
            'gl.transaction_id as reference_id',
            'gl.transaction_type',
            'gl.chart_account_id',
            'ca.account_name as chart_account_name',
            'ca.account_code',
            'c.name as customer_name',
            'b.name as branch_name'
        );

        $results = $query->orderBy('gl.date', 'desc')->get();

        // Get fee information for each chart account that actually has transactions
        $chartAccountIdsWithTransactions = $results->pluck('chart_account_id')->unique()->toArray();
        
        $feesByChartAccount = \App\Models\Fee::whereIn('chart_account_id', $chartAccountIdsWithTransactions)
            ->where('company_id', $company->id)
            ->where('status', 'active')
            ->get()
            ->groupBy('chart_account_id');

        // Add fee information to results - only show fees that have transactions
        $results = $results->map(function ($item) use ($feesByChartAccount) {
            $chartAccountId = $item->chart_account_id;
            $fees = $feesByChartAccount->get($chartAccountId, collect([]));
            
            // Only include fees that have transactions in this period
            $feeNames = $fees->pluck('name')->toArray();
            $item->fee_name = implode(', ', $feeNames);
            $item->fee_ids = $fees->pluck('id')->toArray();
            
            return $item;
        });

        // Calculate summary totals
        $summary = [
            'total_debit' => $results->where('nature', 'debit')->sum('amount'),
            'total_credit' => $results->where('nature', 'credit')->sum('amount'),
            'total_transactions' => $results->count(),
            'unique_fees' => $results->pluck('fee_name')->unique()->count(),
            'unique_customers' => $results->pluck('customer_name')->filter()->unique()->count()
        ];

        // Calculate balance
        $balance = $summary['total_credit'] - $summary['total_debit'];
        $summary['balance'] = $balance;

        return [
            'data' => $results,
            'summary' => $summary
        ];
    }

    public function export(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get filter parameters
        $startDate = $request->get('start_date', now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $branchId = $request->get('branch_id', 'all');
        $feeId = $request->get('fee_id', 'all');

        // Get fees data
        $feesData = $this->getFeesData($startDate, $endDate, $branchId, $feeId);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', 'Fees Report - GL Transactions');
        $sheet->setCellValue('A2', 'Company: ' . $company->name);
        $sheet->setCellValue('A3', 'Period: ' . $startDate . ' to ' . $endDate);
        $sheet->setCellValue('A4', 'Generated: ' . now()->format('Y-m-d H:i:s'));

        // Set column headers
        $headers = ['#', 'Date', 'Fee Name', 'Chart Account', 'Account Code', 'Customer', 'Branch', 'Nature', 'Amount', 'Description', 'Reference ID', 'Transaction Type'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '6', $header);
            $col++;
        }

        // Add data
        $row = 7;
        foreach ($feesData['data'] as $index => $item) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $item->date);
            $sheet->setCellValue('C' . $row, $item->fee_name);
            $sheet->setCellValue('D' . $row, $item->chart_account_name);
            $sheet->setCellValue('E' . $row, $item->account_code);
            $sheet->setCellValue('F' . $row, $item->customer_name);
            $sheet->setCellValue('G' . $row, $item->branch_name);
            $sheet->setCellValue('H' . $row, ucfirst($item->nature));
            $sheet->setCellValue('I' . $row, number_format($item->amount, 2));
            $sheet->setCellValue('J' . $row, $item->description);
            $sheet->setCellValue('K' . $row, $item->reference_id);
            $sheet->setCellValue('L' . $row, $item->transaction_type);
            $row++;
        }

        // Add summary
        $row += 2;
        $sheet->setCellValue('A' . $row, 'SUMMARY');
        $sheet->setCellValue('B' . $row, 'Total Debit: ' . number_format($feesData['summary']['total_debit'], 2));
        $row++;
        $sheet->setCellValue('B' . $row, 'Total Credit: ' . number_format($feesData['summary']['total_credit'], 2));
        $row++;
        $sheet->setCellValue('B' . $row, 'Total Transactions: ' . $feesData['summary']['total_transactions']);
        $row++;
        $sheet->setCellValue('B' . $row, 'Unique Fees: ' . $feesData['summary']['unique_fees']);
        $row++;
        $sheet->setCellValue('B' . $row, 'Unique Customers: ' . $feesData['summary']['unique_customers']);
        $row++;
        $sheet->setCellValue('B' . $row, 'Balance: ' . number_format($feesData['summary']['balance'], 2));

        // Auto-size columns
        foreach (range('A', 'L') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $filename = 'fees_report_' . $startDate . '_to_' . $endDate . '.xlsx';
        
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'fees_report');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }

    public function exportPdf(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get filter parameters
        $startDate = $request->get('start_date', now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $branchId = $request->get('branch_id', 'all');
        $feeId = $request->get('fee_id', 'all');

        // Get fees data
        $feesData = $this->getFeesData($startDate, $endDate, $branchId, $feeId);

        // Get filter labels for display
        $branchName = 'All Branches';
        if ($branchId !== 'all') {
            $branch = \App\Models\Branch::find($branchId);
            $branchName = $branch ? $branch->name : 'Unknown Branch';
        }

        $feeName = 'All Fees';
        if ($feeId !== 'all') {
            $fee = \App\Models\Fee::find($feeId);
            $feeName = $fee ? $fee->name : 'Unknown Fee';
        }

        $pdf = Pdf::loadView('accounting.reports.fees.pdf', [
            'feesData' => $feesData,
            'company' => $company,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'branchName' => $branchName,
            'feeName' => $feeName,
            'user' => $user
        ]);

        $filename = 'fees_report_' . $startDate . '_to_' . $endDate . '.pdf';
        return $pdf->download($filename);
    }
}
