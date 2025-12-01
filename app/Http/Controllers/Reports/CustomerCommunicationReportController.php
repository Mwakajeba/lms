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

class CustomerCommunicationReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get filter parameters
        $startDate = $request->get('start_date', now()->subMonths(3)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $branchId = $request->get('branch_id', 'all');
        $customerId = $request->get('customer_id', 'all');
        $communicationType = $request->get('communication_type', 'all');
        $status = $request->get('status', 'all');

        // Get user's assigned branches
        $branches = $user->branches()->where('company_id', $company->id)->get();
        
        // Get customers for filter
        $customers = \App\Models\Customer::where('company_id', $company->id)
            ->orderBy('name')
            ->get();

        // Get communication data
        $communicationData = $this->getCommunicationData($startDate, $endDate, $branchId, $customerId, $communicationType, $status);

        return view('reports.customers.communication', compact(
            'communicationData',
            'startDate',
            'endDate',
            'branchId',
            'customerId',
            'communicationType',
            'status',
            'branches',
            'customers',
            'user'
        ));
    }

    private function getCommunicationData($startDate, $endDate, $branchId, $customerId, $communicationType, $status)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get all communication activities
        $communications = collect();

        // 1. Loan Applications
        $loanApplications = $this->getLoanApplications($startDate, $endDate, $branchId, $customerId);
        $communications = $communications->merge($loanApplications);

        // 2. Loan Repayments
        $loanRepayments = $this->getLoanRepayments($startDate, $endDate, $branchId, $customerId);
        $communications = $communications->merge($loanRepayments);

        // 3. Collateral Deposits
        $collateralDeposits = $this->getCollateralDeposits($startDate, $endDate, $branchId, $customerId);
        $communications = $communications->merge($collateralDeposits);

        // 4. GL Transactions
        $glTransactions = $this->getGlTransactions($startDate, $endDate, $branchId, $customerId);
        $communications = $communications->merge($glTransactions);

        // 5. Activity Logs
        $activityLogs = $this->getActivityLogs($startDate, $endDate, $branchId, $customerId);
        $communications = $communications->merge($activityLogs);

        // Apply communication type filter
        if ($communicationType !== 'all') {
            $communications = $communications->filter(function ($item) use ($communicationType) {
                return $item['type'] === $communicationType;
            });
        }

        // Apply status filter
        if ($status !== 'all') {
            $communications = $communications->filter(function ($item) use ($status) {
                return $item['status'] === $status;
            });
        }

        // Sort by date (newest first)
        $communications = $communications->sortByDesc('date');

        // Calculate summary statistics
        $summary = $this->calculateCommunicationSummary($communications);

        return [
            'data' => $communications,
            'summary' => $summary
        ];
    }

    private function getLoanApplications($startDate, $endDate, $branchId, $customerId)
    {
        $query = DB::table('loans as l')
            ->join('customers as c', 'l.customer_id', '=', 'c.id')
            ->join('branches as b', 'l.branch_id', '=', 'b.id')
            ->join('loan_products as lp', 'l.product_id', '=', 'lp.id')
            ->where('c.company_id', Auth::user()->company->id)
            ->whereBetween('l.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if ($branchId !== 'all') {
            $query->where('l.branch_id', $branchId);
        }

        if ($customerId !== 'all') {
            $query->where('l.customer_id', $customerId);
        }

        $loans = $query->select(
            'l.id as reference_id',
            'l.created_at as date',
            'c.name as customer_name',
            'c.customerNo as customer_no',
            'b.name as branch_name',
            'lp.name as product_name',
            'l.amount',
            'l.status'
        )->get();

        return $loans->map(function ($loan) {
            return [
                'id' => 'loan_' . $loan->reference_id,
                'date' => Carbon::parse($loan->date),
                'customer_name' => $loan->customer_name,
                'customer_no' => $loan->customer_no,
                'branch_name' => $loan->branch_name,
                'type' => 'loan_application',
                'type_label' => 'Loan Application',
                'reference' => 'Loan #' . $loan->reference_id,
                'description' => 'Application for ' . $loan->product_name,
                'amount' => $loan->amount,
                'status' => $loan->status,
                'status_label' => ucfirst($loan->status),
                'user_name' => 'System',
                'communication_method' => 'System Generated'
            ];
        });
    }

    private function getLoanRepayments($startDate, $endDate, $branchId, $customerId)
    {
        $query = DB::table('receipts as r')
            ->join('customers as c', 'r.payee_id', '=', 'c.id')
            ->join('branches as b', 'r.branch_id', '=', 'b.id')
            ->where('r.reference_type', 'loan_repayment')
            ->where('c.company_id', Auth::user()->company->id)
            ->whereBetween('r.date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if ($branchId !== 'all') {
            $query->where('r.branch_id', $branchId);
        }

        if ($customerId !== 'all') {
            $query->where('r.payee_id', $customerId);
        }

        $receipts = $query->select(
            'r.id as reference_id',
            'r.date',
            'r.reference',
            'c.name as customer_name',
            'c.customerNo as customer_no',
            'b.name as branch_name',
            'r.amount',
            'r.description',
            'r.approved'
        )->get();

        return $receipts->map(function ($receipt) {
            return [
                'id' => 'receipt_' . $receipt->reference_id,
                'date' => Carbon::parse($receipt->date),
                'customer_name' => $receipt->customer_name,
                'customer_no' => $receipt->customer_no,
                'branch_name' => $receipt->branch_name,
                'type' => 'loan_repayment',
                'type_label' => 'Loan Repayment',
                'reference' => $receipt->reference,
                'description' => $receipt->description,
                'amount' => $receipt->amount,
                'status' => $receipt->approved ? 'completed' : 'pending',
                'status_label' => $receipt->approved ? 'Completed' : 'Pending',
                'user_name' => 'System',
                'communication_method' => 'Payment Receipt'
            ];
        });
    }

    private function getCollateralDeposits($startDate, $endDate, $branchId, $customerId)
    {
        $query = DB::table('cash_collaterals as cc')
            ->join('customers as c', 'cc.customer_id', '=', 'c.id')
            ->join('branches as b', 'cc.branch_id', '=', 'b.id')
            ->where('c.company_id', Auth::user()->company->id)
            ->whereBetween('cc.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if ($branchId !== 'all') {
            $query->where('cc.branch_id', $branchId);
        }

        if ($customerId !== 'all') {
            $query->where('cc.customer_id', $customerId);
        }

        $collaterals = $query->select(
            'cc.id as reference_id',
            'cc.created_at as date',
            'c.name as customer_name',
            'c.customerNo as customer_no',
            'b.name as branch_name',
            'cc.amount'
        )->get();

        return $collaterals->map(function ($collateral) {
            return [
                'id' => 'collateral_' . $collateral->reference_id,
                'date' => Carbon::parse($collateral->date),
                'customer_name' => $collateral->customer_name,
                'customer_no' => $collateral->customer_no,
                'branch_name' => $collateral->branch_name,
                'type' => 'collateral_deposit',
                'type_label' => 'Collateral Deposit',
                'reference' => 'Collateral #' . $collateral->reference_id,
                'description' => 'Cash collateral deposit',
                'amount' => $collateral->amount,
                'status' => 'completed',
                'status_label' => 'Completed',
                'user_name' => 'System',
                'communication_method' => 'System Generated'
            ];
        });
    }

    private function getGlTransactions($startDate, $endDate, $branchId, $customerId)
    {
        $query = DB::table("gl_transactions as gl")
            ->join("customers as c", "gl.customer_id", "=", "c.id")
            ->join("branches as b", "gl.branch_id", "=", "b.id")
            ->join("chart_accounts as ca", "gl.chart_account_id", "=", "ca.id")
            ->where("c.company_id", Auth::user()->company->id)
            ->whereBetween("gl.date", [$startDate . " 00:00:00", $endDate . " 23:59:59"]);

        if ($branchId !== "all") {
            $query->where("gl.branch_id", $branchId);
        }

        if ($customerId !== "all") {
            $query->where("gl.customer_id", $customerId);
        }

        $transactions = $query->select(
            "gl.id as reference_id",
            "gl.date",
            "c.name as customer_name",
            "c.customerNo as customer_no",
            "b.name as branch_name",
            "gl.amount",
            "gl.description",
            "gl.nature",
            "gl.transaction_type",
            "gl.transaction_id",
            "ca.account_name"
        )->get();

        return $transactions->map(function ($transaction) {
            // Determine the origin source
            $origin = $this->getTransactionOrigin($transaction->transaction_type, $transaction->transaction_id);
            
            return [
                "id" => "gl_" . $transaction->reference_id,
                "date" => Carbon::parse($transaction->date),
                "customer_name" => $transaction->customer_name,
                "customer_no" => $transaction->customer_no,
                "branch_name" => $transaction->branch_name,
                "type" => "gl_transaction",
                "type_label" => "GL Transaction",
                "reference" => "GL #" . $transaction->reference_id . " (" . $transaction->transaction_type . ")",
                "description" => $transaction->description ?: $transaction->account_name . " - " . $transaction->transaction_type,
                "amount" => $transaction->amount,
                "status" => "completed",
                "status_label" => "Completed",
                "user_name" => "System",
                "communication_method" => "System Generated",
                "origin" => $origin,
                "origin_label" => $this->getOriginLabel($transaction->transaction_type),
                "nature" => $transaction->nature,
                "nature_label" => ucfirst($transaction->nature)
            ];
        });
    }

    private function getTransactionOrigin($transactionType, $transactionId)
    {
        switch (strtolower($transactionType)) {
            case "receipt":
                return "receipt_" . $transactionId;
            case "payment":
                return "payment_" . $transactionId;
            case "journal":
                return "journal_" . $transactionId;
            case "loan disbursement":
                return "loan_" . $transactionId;
            default:
                return "unknown_" . $transactionId;
        }
    }

    private function getOriginLabel($transactionType)
    {
        switch (strtolower($transactionType)) {
            case "receipt":
                return "Receipt";
            case "payment":
                return "Payment";
            case "journal":
                return "Journal";
            case "loan disbursement":
                return "Loan Disbursement";
            default:
                return ucfirst($transactionType);
        }
    }

    private function getActivityLogs($startDate, $endDate, $branchId, $customerId)
    {
        $query = DB::table("activity_logs as al")
            ->join("customers as c", "al.user_id", "=", "c.id")
            ->join("branches as b", "c.branch_id", "=", "b.id")
            ->join("users as u", "al.user_id", "=", "u.id")
            ->where("al.model", "Customer")
            ->where("c.company_id", Auth::user()->company->id)
            ->whereBetween("al.activity_time", [$startDate . " 00:00:00", $endDate . " 23:59:59"]);

        if ($branchId !== "all") {
            $query->where("c.branch_id", $branchId);
        }

        if ($customerId !== "all") {
            $query->where("al.user_id", $customerId);
        }

        $logs = $query->select(
            "al.id as reference_id",
            "al.activity_time as date",
            "c.name as customer_name",
            "c.customerNo as customer_no",
            "b.name as branch_name",
            "al.description",
            "al.action",
            "u.name as user_name"
        )->get();

        return $logs->map(function ($log) {
            return [
                "id" => "log_" . $log->reference_id,
                "date" => Carbon::parse($log->date),
                "customer_name" => $log->customer_name,
                "customer_no" => $log->customer_no,
                "branch_name" => $log->branch_name,
                "type" => "activity_log",
                "type_label" => "Activity Log",
                "reference" => "Log #" . $log->reference_id,
                "description" => $log->description,
                "amount" => null,
                "status" => "completed",
                "status_label" => "Completed",
                "user_name" => $log->user_name,
                "communication_method" => "User Action"
            ];
        });
    }

    private function calculateCommunicationSummary($communications)
    {
        $totalCommunications = $communications->count();
        
        $typeDistribution = $communications->groupBy('type')->map(function ($group) use ($totalCommunications) {
            return [
                'count' => $group->count(),
                'percentage' => $totalCommunications > 0 ? round(($group->count() / $totalCommunications) * 100, 2) : 0
            ];
        });

        $statusDistribution = $communications->groupBy('status')->map(function ($group) use ($totalCommunications) {
            return [
                'count' => $group->count(),
                'percentage' => $totalCommunications > 0 ? round(($group->count() / $totalCommunications) * 100, 2) : 0
            ];
        });

        $branchDistribution = $communications->groupBy('branch_name')->map(function ($group) use ($totalCommunications) {
            return [
                'count' => $group->count(),
                'percentage' => $totalCommunications > 0 ? round(($group->count() / $totalCommunications) * 100, 2) : 0
            ];
        });

        $totalAmount = $communications->where('amount', '!=', null)->sum('amount');

        $uniqueCustomers = $communications->groupBy('customer_id')->count();

        $dailyCommunications = $communications->groupBy(function ($item) {
            return $item['date']->format('Y-m-d');
        })->map(function ($group) {
            return $group->count();
        })->sortKeys();

        return [
            'total_communications' => $totalCommunications,
            'type_distribution' => $typeDistribution,
            'status_distribution' => $statusDistribution,
            'branch_distribution' => $branchDistribution,
            'total_amount' => $totalAmount,
            'unique_customers' => $uniqueCustomers,
            'daily_communications' => $dailyCommunications
        ];
    }

    public function export(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get filter parameters
        $startDate = $request->get('start_date', now()->subMonths(3)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $branchId = $request->get('branch_id', 'all');
        $customerId = $request->get('customer_id', 'all');
        $communicationType = $request->get('communication_type', 'all');
        $status = $request->get('status', 'all');

        // Get communication data
        $communicationData = $this->getCommunicationData($startDate, $endDate, $branchId, $customerId, $communicationType, $status);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', 'Customer Communication Report');
        $sheet->setCellValue('A2', 'Company: ' . $company->name);
        $sheet->setCellValue('A3', 'Period: ' . $startDate . ' to ' . $endDate);
        $sheet->setCellValue('A4', 'Generated: ' . now()->format('Y-m-d H:i:s'));

        // Set column headers
        $headers = ['#', 'Date', 'Customer No', 'Customer Name', 'Branch', 'Type', 'Reference', 'Description', 'Amount', 'Status', 'User', 'Method', 'Origin', 'Nature'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '6', $header);
            $col++;
        }

        // Add data
        $row = 7;
        foreach ($communicationData['data'] as $index => $communication) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $communication['date']->format('d/m/Y H:i'));
            $sheet->setCellValue('C' . $row, $communication['customer_no']);
            $sheet->setCellValue('D' . $row, $communication['customer_name']);
            $sheet->setCellValue('E' . $row, $communication['branch_name']);
            $sheet->setCellValue('F' . $row, $communication['type_label']);
            $sheet->setCellValue('G' . $row, $communication['reference']);
            $sheet->setCellValue('H' . $row, $communication['description']);
            $sheet->setCellValue('I' . $row, $communication['amount'] ? number_format($communication['amount'], 2) : '');
            $sheet->setCellValue('J' . $row, $communication['status_label']);
            $sheet->setCellValue('K' . $row, $communication['user_name']);
            $sheet->setCellValue('L' . $row, $communication['communication_method']);
            $row++;
        }

        // Add summary
        $row += 2;
        $sheet->setCellValue('A' . $row, 'SUMMARY');
        $sheet->setCellValue('B' . $row, 'Total Communications: ' . $communicationData['summary']['total_communications']);
        $row++;
        $sheet->setCellValue('B' . $row, 'Unique Customers: ' . $communicationData['summary']['unique_customers']);
        $row++;
        $sheet->setCellValue('B' . $row, 'Total Amount: ' . number_format($communicationData['summary']['total_amount'], 2));

        // Auto-size columns
        foreach (range('A', 'N') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $filename = 'customer_communication_report_' . $startDate . '_to_' . $endDate . '.xlsx';
        
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'customer_communication_report');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }

    public function exportPdf(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get filter parameters
        $startDate = $request->get('start_date', now()->subMonths(3)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $branchId = $request->get('branch_id', 'all');
        $customerId = $request->get('customer_id', 'all');
        $communicationType = $request->get('communication_type', 'all');
        $status = $request->get('status', 'all');

        // Get communication data
        $communicationData = $this->getCommunicationData($startDate, $endDate, $branchId, $customerId, $communicationType, $status);

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

        $communicationTypeName = ucfirst(str_replace('_', ' ', $communicationType));
        $statusName = ucfirst($status);

        $pdf = Pdf::loadView('reports.customers.communication-pdf', [
            'communicationData' => $communicationData,
            'company' => $company,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'branchName' => $branchName,
            'customerName' => $customerName,
            'communicationTypeName' => $communicationTypeName,
            'statusName' => $statusName,
            'user' => $user
        ]);

        $filename = 'customer_communication_report_' . $startDate . '_to_' . $endDate . '.pdf';
        return $pdf->download($filename);
    }
}
