<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Barryvdh\DomPDF\Facade\Pdf;

class CustomerActivityReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get filter parameters
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $branchId = $request->get('branch_id', 'all');
        $customerId = $request->get('customer_id', 'all');
        $activityType = $request->get('activity_type', 'all');
        $transactionType = $request->get('transaction_type', 'all');

        // Get user's assigned branches
        $branches = $user->branches()->where('company_id', $company->id)->get();
        
        // Get customers for filter
        $customers = \App\Models\Customer::where('company_id', $company->id)
            ->orderBy('name')
            ->get();

        // Get activity data
        $activityData = $this->getActivityData($startDate, $endDate, $branchId, $customerId, $activityType, $transactionType);

        return view('reports.customers.activity', compact(
            'activityData',
            'startDate',
            'endDate',
            'branchId',
            'customerId',
            'activityType',
            'transactionType',
            'branches',
            'customers',
            'user'
        ));
    }

    private function getActivityData($startDate, $endDate, $branchId, $customerId, $activityType, $transactionType)
    {
        $user = Auth::user();
        $company = $user->company;

        $activities = collect();

        // Get loan activities
        if ($activityType === 'all' || $activityType === 'loans') {
            $loanQuery = \App\Models\Loan::with(['customer', 'product', 'branch'])
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

            if ($branchId !== 'all') {
                $loanQuery->where('branch_id', $branchId);
            }

            if ($customerId !== 'all') {
                $loanQuery->where('customer_id', $customerId);
            }

            $loans = $loanQuery->get();

            foreach ($loans as $loan) {
                $activities->push([
                    'id' => 'loan_' . $loan->id,
                    'date' => $loan->created_at,
                    'customer_id' => $loan->customer_id,
                    'customer_name' => $loan->customer->name,
                    'customer_no' => $loan->customer->customerNo,
                    'branch_name' => $loan->branch->name,
                    'activity_type' => 'Loan Application',
                    'transaction_type' => 'loan_application',
                    'description' => 'Loan application for ' . number_format($loan->amount, 2) . ' - ' . $loan->product->name,
                    'amount' => $loan->amount,
                    'status' => $loan->status,
                    'reference_id' => $loan->id,
                    'created_by' => $loan->created_by ?? 'System'
                ]);
            }
        }

        // Get repayment activities
        if ($activityType === 'all' || $activityType === 'repayments') {
            $repaymentQuery = \App\Models\Repayment::with(['customer', 'loan', 'loan.branch'])
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

            if ($branchId !== 'all') {
                $repaymentQuery->whereHas('loan', function($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                });
            }

            if ($customerId !== 'all') {
                $repaymentQuery->where('customer_id', $customerId);
            }

            $repayments = $repaymentQuery->get();

            foreach ($repayments as $repayment) {
                $activities->push([
                    'id' => 'repayment_' . $repayment->id,
                    'date' => $repayment->created_at,
                    'customer_id' => $repayment->customer_id,
                    'customer_name' => $repayment->customer->name,
                    'customer_no' => $repayment->customer->customerNo,
                    'branch_name' => $repayment->loan->branch->name,
                    'activity_type' => 'Loan Repayment',
                    'transaction_type' => 'loan_repayment',
                    'description' => 'Repayment of ' . number_format($repayment->amount, 2) . ' for Loan #' . $repayment->loan_id,
                    'amount' => $repayment->amount,
                    'status' => $repayment->status ?? 'completed',
                    'reference_id' => $repayment->id,
                    'created_by' => $repayment->created_by ?? 'System'
                ]);
            }
        }

        // Get collateral activities
        if ($activityType === 'all' || $activityType === 'collaterals') {
            $collateralQuery = \App\Models\CashCollateral::with(['customer', 'customer.branch'])
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

            if ($branchId !== 'all') {
                $collateralQuery->whereHas('customer', function($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                });
            }

            if ($customerId !== 'all') {
                $collateralQuery->where('customer_id', $customerId);
            }

            $collaterals = $collateralQuery->get();

            foreach ($collaterals as $collateral) {
                $activities->push([
                    'id' => 'collateral_' . $collateral->id,
                    'date' => $collateral->created_at,
                    'customer_id' => $collateral->customer_id,
                    'customer_name' => $collateral->customer->name,
                    'customer_no' => $collateral->customer->customerNo,
                    'branch_name' => $collateral->customer->branch->name,
                    'activity_type' => 'Collateral Deposit',
                    'transaction_type' => 'collateral_deposit',
                    'description' => 'Collateral deposit of ' . number_format($collateral->amount, 2) . ' - ' . $collateral->type,
                    'amount' => $collateral->amount,
                    'status' => $collateral->status ?? 'active',
                    'reference_id' => $collateral->id,
                    'created_by' => $collateral->created_by ?? 'System'
                ]);
            }
        }

        // Get GL transaction activities
        if ($activityType === 'all' || $activityType === 'transactions') {
            $glQuery = DB::table('gl_transactions as gl')
                ->join('customers as c', 'gl.customer_id', '=', 'c.id')
                ->leftJoin('branches as b', 'c.branch_id', '=', 'b.id')
                ->whereBetween('gl.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->where('c.company_id', $company->id);

            if ($branchId !== 'all') {
                $glQuery->where('c.branch_id', $branchId);
            }

            if ($customerId !== 'all') {
                $glQuery->where('gl.customer_id', $customerId);
            }

            if ($transactionType !== 'all') {
                $glQuery->where('gl.transaction_type', $transactionType);
            }

            $glTransactions = $glQuery->select(
                'gl.id',
                'gl.created_at',
                'gl.customer_id',
                'c.name as customer_name',
                'c.customerNo as customer_no',
                'b.name as branch_name',
                'gl.transaction_type',
                'gl.description',
                'gl.amount',
                'gl.nature',
                'gl.transaction_id as reference_id'
            )->get();

            foreach ($glTransactions as $transaction) {
                $activities->push([
                    'id' => 'gl_' . $transaction->id,
                    'date' => $transaction->created_at,
                    'customer_id' => $transaction->customer_id,
                    'customer_name' => $transaction->customer_name,
                    'customer_no' => $transaction->customer_no,
                    'branch_name' => $transaction->branch_name,
                    'activity_type' => 'GL Transaction',
                    'transaction_type' => $transaction->transaction_type,
                    'description' => $transaction->description,
                    'amount' => $transaction->amount,
                    'status' => $transaction->nature,
                    'reference_id' => $transaction->reference_id,
                    'created_by' => 'System'
                ]);
            }
        }

        // Sort activities by date (newest first)
        $activities = $activities->sortByDesc('date');

        // Calculate summary statistics
        $summary = [
            'total_activities' => $activities->count(),
            'loan_applications' => $activities->where('transaction_type', 'loan_application')->count(),
            'loan_repayments' => $activities->where('transaction_type', 'loan_repayment')->count(),
            'collateral_deposits' => $activities->where('transaction_type', 'collateral_deposit')->count(),
            'gl_transactions' => $activities->where('activity_type', 'GL Transaction')->count(),
            'total_amount' => $activities->sum('amount'),
            'unique_customers' => $activities->pluck('customer_id')->unique()->count(),
            'unique_branches' => $activities->pluck('branch_name')->unique()->count()
        ];

        return [
            'data' => $activities,
            'summary' => $summary
        ];
    }

    public function export(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get filter parameters
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $branchId = $request->get('branch_id', 'all');
        $customerId = $request->get('customer_id', 'all');
        $activityType = $request->get('activity_type', 'all');
        $transactionType = $request->get('transaction_type', 'all');

        // Get activity data
        $activityData = $this->getActivityData($startDate, $endDate, $branchId, $customerId, $activityType, $transactionType);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', 'Customer Activity Report');
        $sheet->setCellValue('A2', 'Company: ' . $company->name);
        $sheet->setCellValue('A3', 'Period: ' . $startDate . ' to ' . $endDate);
        $sheet->setCellValue('A4', 'Generated: ' . now()->format('Y-m-d H:i:s'));

        // Set column headers
        $headers = ['#', 'Date', 'Customer No', 'Customer Name', 'Branch', 'Activity Type', 'Transaction Type', 'Description', 'Amount', 'Status', 'Reference ID', 'Created By'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '6', $header);
            $col++;
        }

        // Add data
        $row = 7;
        foreach ($activityData['data'] as $index => $activity) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, Carbon::parse($activity['date'])->format('d/m/Y H:i:s'));
            $sheet->setCellValue('C' . $row, $activity['customer_no']);
            $sheet->setCellValue('D' . $row, $activity['customer_name']);
            $sheet->setCellValue('E' . $row, $activity['branch_name']);
            $sheet->setCellValue('F' . $row, $activity['activity_type']);
            $sheet->setCellValue('G' . $row, $activity['transaction_type']);
            $sheet->setCellValue('H' . $row, $activity['description']);
            $sheet->setCellValue('I' . $row, number_format($activity['amount'], 2));
            $sheet->setCellValue('J' . $row, ucfirst($activity['status']));
            $sheet->setCellValue('K' . $row, $activity['reference_id']);
            $sheet->setCellValue('L' . $row, $activity['created_by']);
            $row++;
        }

        // Add summary
        $row += 2;
        $sheet->setCellValue('A' . $row, 'SUMMARY');
        $sheet->setCellValue('B' . $row, 'Total Activities: ' . $activityData['summary']['total_activities']);
        $row++;
        $sheet->setCellValue('B' . $row, 'Loan Applications: ' . $activityData['summary']['loan_applications']);
        $row++;
        $sheet->setCellValue('B' . $row, 'Loan Repayments: ' . $activityData['summary']['loan_repayments']);
        $row++;
        $sheet->setCellValue('B' . $row, 'Collateral Deposits: ' . $activityData['summary']['collateral_deposits']);
        $row++;
        $sheet->setCellValue('B' . $row, 'GL Transactions: ' . $activityData['summary']['gl_transactions']);
        $row++;
        $sheet->setCellValue('B' . $row, 'Total Amount: ' . number_format($activityData['summary']['total_amount'], 2));
        $row++;
        $sheet->setCellValue('B' . $row, 'Unique Customers: ' . $activityData['summary']['unique_customers']);
        $row++;
        $sheet->setCellValue('B' . $row, 'Unique Branches: ' . $activityData['summary']['unique_branches']);

        // Auto-size columns
        foreach (range('A', 'L') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $filename = 'customer_activity_report_' . $startDate . '_to_' . $endDate . '.xlsx';
        
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'customer_activity_report');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }

    public function exportPdf(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get filter parameters
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $branchId = $request->get('branch_id', 'all');
        $customerId = $request->get('customer_id', 'all');
        $activityType = $request->get('activity_type', 'all');
        $transactionType = $request->get('transaction_type', 'all');

        // Get activity data
        $activityData = $this->getActivityData($startDate, $endDate, $branchId, $customerId, $activityType, $transactionType);

        // Get filter labels for display
        $branchName = 'All Branches';
        if ($branchId !== 'all') {
            $branch = \App\Models\Branch::find($branchId);
            $branchName = $branch ? $branch->name : 'Unknown Branch';
        }

        $customerName = 'All Customers';
        if ($customerId !== 'all') {
            $customer = \App\Models\Customer::find($customerId);
            $customerName = $customer ? $customer->name : 'Unknown Customer';
        }

        $activityTypeName = ucfirst($activityType);
        $transactionTypeName = ucfirst($transactionType);

        $pdf = Pdf::loadView('reports.customers.activity-pdf', [
            'activityData' => $activityData,
            'company' => $company,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'branchName' => $branchName,
            'customerName' => $customerName,
            'activityTypeName' => $activityTypeName,
            'transactionTypeName' => $transactionTypeName,
            'user' => $user
        ]);

        $filename = 'customer_activity_report_' . $startDate . '_to_' . $endDate . '.pdf';
        return $pdf->download($filename);
    }
}
