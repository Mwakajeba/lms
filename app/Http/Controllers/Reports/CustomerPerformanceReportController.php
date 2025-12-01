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

class CustomerPerformanceReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get filter parameters
        $startDate = $request->get('start_date', now()->subMonths(6)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $branchId = $request->get('branch_id', 'all');
        $customerId = $request->get('customer_id', 'all');
        $performanceMetric = $request->get('performance_metric', 'all');
        $riskLevel = $request->get('risk_level', 'all');

        // Get user's assigned branches
        $branches = $user->branches()
            ->where('branches.company_id', $company->id)
            ->select('branches.id', 'branches.name')
            ->get();

        // If user has exactly one branch, force-select it
        if (($branches->count() ?? 0) === 1) {
            $branchId = $branches->first()->id;
        }
        
        // Get customers for filter
        $customers = \App\Models\Customer::where('company_id', $company->id)
            ->orderBy('name')
            ->get();

        // Get performance data
        $performanceData = $this->getPerformanceData($startDate, $endDate, $branchId, $customerId, $performanceMetric, $riskLevel);

        return view('reports.customers.performance', compact(
            'performanceData',
            'startDate',
            'endDate',
            'branchId',
            'customerId',
            'performanceMetric',
            'riskLevel',
            'branches',
            'customers',
            'user'
        ));
    }

    private function getPerformanceData($startDate, $endDate, $branchId, $customerId, $performanceMetric, $riskLevel)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get user's assigned branch IDs for filtering
        $assignedBranchIds = $user->branches()
            ->where('branches.company_id', $company->id)
            ->pluck('branches.id')
            ->toArray();

        // Build base query for customers
        $customerQuery = \App\Models\Customer::with(['region', 'district', 'branch', 'loans', 'repayments', 'collaterals'])
            ->where('company_id', $company->id)
            ->whereIn('branch_id', $assignedBranchIds);

        // Apply filters
        if ($branchId !== 'all') {
            $customerQuery->where('branch_id', $branchId);
        }

        if ($customerId !== 'all') {
            $customerQuery->where('id', $customerId);
        }

        $customers = $customerQuery->get();

        $performanceMetrics = collect();

        foreach ($customers as $customer) {
            // Calculate performance metrics for each customer
            $metrics = $this->calculateCustomerMetrics($customer, $startDate, $endDate);
            
            // Apply performance metric filter
            if ($performanceMetric !== 'all') {
                if ($performanceMetric === 'excellent' && $metrics['performance_score'] < 90) continue;
                if ($performanceMetric === 'good' && ($metrics['performance_score'] < 70 || $metrics['performance_score'] >= 90)) continue;
                if ($performanceMetric === 'average' && ($metrics['performance_score'] < 50 || $metrics['performance_score'] >= 70)) continue;
                if ($performanceMetric === 'poor' && $metrics['performance_score'] >= 50) continue;
            }

            // Apply risk level filter
            if ($riskLevel !== 'all') {
                if ($riskLevel !== $metrics['risk_level']) continue;
            }

            $performanceMetrics->push($metrics);
        }

        // Sort by performance score (highest first)
        $performanceMetrics = $performanceMetrics->sortByDesc('performance_score');

        // Calculate summary statistics
        $summary = [
            'total_customers' => $performanceMetrics->count(),
            'excellent_performers' => $performanceMetrics->where('performance_score', '>=', 90)->count(),
            'good_performers' => $performanceMetrics->whereBetween('performance_score', [70, 89])->count(),
            'average_performers' => $performanceMetrics->whereBetween('performance_score', [50, 69])->count(),
            'poor_performers' => $performanceMetrics->where('performance_score', '<', 50)->count(),
            'low_risk_customers' => $performanceMetrics->where('risk_level', 'low')->count(),
            'medium_risk_customers' => $performanceMetrics->where('risk_level', 'medium')->count(),
            'high_risk_customers' => $performanceMetrics->where('risk_level', 'high')->count(),
            'total_loan_amount' => $performanceMetrics->sum('total_loan_amount'),
            'total_repayments' => $performanceMetrics->sum('total_repayments'),
            'total_collateral' => $performanceMetrics->sum('total_collateral'),
            'average_performance_score' => $performanceMetrics->avg('performance_score'),
            'average_repayment_rate' => $performanceMetrics->avg('repayment_rate'),
            'average_days_overdue' => $performanceMetrics->avg('average_days_overdue')
        ];

        return [
            'data' => $performanceMetrics,
            'summary' => $summary
        ];
    }

    private function calculateCustomerMetrics($customer, $startDate, $endDate)
    {
        // Get customer's loans
        $loans = $customer->loans;
        $totalLoanAmount = $loans->sum('amount');
        
        // Get repayments within date range
        $repayments = DB::table("receipts")->where("reference_type", "loan_repayment")->where("payee_id", $customer->id)
            ->whereBetween('date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->get();
        
        $totalRepayments = $repayments->sum('amount');
        
        // Get collaterals
        $totalCollateral = $customer->collaterals->sum('amount');
        
        // Calculate repayment rate
        $repaymentRate = $totalLoanAmount > 0 ? ($totalRepayments / $totalLoanAmount) * 100 : 0;
        
        // Calculate overdue days
        $overdueDays = $this->calculateOverdueDays($customer);
        $averageDaysOverdue = $overdueDays['average'];
        
        // Calculate performance score (0-100)
        $performanceScore = $this->calculatePerformanceScore($repaymentRate, $averageDaysOverdue, $totalCollateral, $totalLoanAmount);
        
        // Determine risk level
        $riskLevel = $this->determineRiskLevel($performanceScore, $averageDaysOverdue, $repaymentRate);
        
        // Get recent activity count
        $recentActivityCount = $repayments->count();
        
        // Calculate loan utilization
        $loanUtilization = $totalLoanAmount > 0 ? ($totalRepayments / $totalLoanAmount) * 100 : 0;
        
        return [
            'customer_id' => $customer->id,
            'customer_no' => $customer->customerNo,
            'customer_name' => $customer->name,
            'branch_name' => $customer->branch->name ?? 'N/A',
            'region_name' => $customer->region->name ?? 'N/A',
            'district_name' => $customer->district->name ?? 'N/A',
            'date_registered' => $customer->dateRegistered,
            'total_loans' => $loans->count(),
            'total_loan_amount' => $totalLoanAmount,
            'total_repayments' => $totalRepayments,
            'total_collateral' => $totalCollateral,
            'repayment_rate' => $repaymentRate,
            'loan_utilization' => $loanUtilization,
            'average_days_overdue' => $averageDaysOverdue,
            'performance_score' => $performanceScore,
            'risk_level' => $riskLevel,
            'recent_activity_count' => $recentActivityCount,
            'overdue_loans_count' => $overdueDays['count'],
            'active_loans_count' => $loans->where('status', 'active')->count(),
            'completed_loans_count' => $loans->where('status', 'completed')->count()
        ];
    }

    private function calculateOverdueDays($customer)
    {
        $overdueDays = [];
        $totalOverdueDays = 0;
        $overdueCount = 0;

        foreach ($customer->loans as $loan) {
            if ($loan->status === "active") {
                // Get loan schedules that are overdue (due_date < now)
                $schedules = \App\Models\LoanSchedule::where("loan_id", $loan->id)
                    ->where("due_date", "<", now())
                    ->get();

                foreach ($schedules as $schedule) {
                    // Check if there is a repayment for this loan (since receipts table doesn't have schedule_id)
                    $repayment = DB::table("receipts")
                        ->where("reference_type", "loan_repayment")
                        ->where("reference_number", $loan->id)
                        ->where("date", ">=", $schedule->due_date)
                        ->first();

                    // If no repayment found, this schedule is overdue
                    if (!$repayment) {
                        $daysOverdue = now()->diffInDays($schedule->due_date);
                        $overdueDays[] = $daysOverdue;
                        $totalOverdueDays += $daysOverdue;
                        $overdueCount++;
                    }
                }
            }
        }

        $averageOverdueDays = $overdueCount > 0 ? $totalOverdueDays / $overdueCount : 0;

        return [
            "average" => $averageOverdueDays,
            "count" => $overdueCount,
            "total" => $totalOverdueDays
        ];
    }

    private function calculatePerformanceScore($repaymentRate, $averageDaysOverdue, $totalCollateral, $totalLoanAmount)
    {
        $score = 0;
        
        // Repayment rate component (40% weight)
        $repaymentScore = min($repaymentRate, 100);
        $score += ($repaymentScore / 100) * 40;
        
        // Overdue days component (30% weight) - lower is better
        $overdueScore = max(0, 100 - ($averageDaysOverdue * 2)); // Penalty of 2 points per day
        $score += ($overdueScore / 100) * 30;
        
        // Collateral ratio component (20% weight)
        $collateralRatio = $totalLoanAmount > 0 ? ($totalCollateral / $totalLoanAmount) * 100 : 0;
        $collateralScore = min($collateralRatio * 2, 100); // Bonus for collateral
        $score += ($collateralScore / 100) * 20;
        
        // Activity component (10% weight) - based on recent activity
        $activityScore = min(100, 100); // Placeholder for activity score
        $score += ($activityScore / 100) * 10;
        
        return round($score, 2);
    }

    private function determineRiskLevel($performanceScore, $averageDaysOverdue, $repaymentRate)
    {
        if ($performanceScore >= 80 && $averageDaysOverdue <= 7 && $repaymentRate >= 80) {
            return 'low';
        } elseif ($performanceScore >= 60 && $averageDaysOverdue <= 30 && $repaymentRate >= 60) {
            return 'medium';
        } else {
            return 'high';
        }
    }

    public function export(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get filter parameters
        $startDate = $request->get('start_date', now()->subMonths(6)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $branchId = $request->get('branch_id', 'all');
        $customerId = $request->get('customer_id', 'all');
        $performanceMetric = $request->get('performance_metric', 'all');
        $riskLevel = $request->get('risk_level', 'all');

        // Get performance data
        $performanceData = $this->getPerformanceData($startDate, $endDate, $branchId, $customerId, $performanceMetric, $riskLevel);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', 'Customer Performance Report');
        $sheet->setCellValue('A2', 'Company: ' . $company->name);
        $sheet->setCellValue('A3', 'Period: ' . $startDate . ' to ' . $endDate);
        $sheet->setCellValue('A4', 'Generated: ' . now()->format('Y-m-d H:i:s'));

        // Set column headers
        $headers = ['#', 'Customer No', 'Customer Name', 'Branch', 'Region', 'District', 'Date Registered', 'Total Loans', 'Total Loan Amount', 'Total Repayments', 'Total Collateral', 'Repayment Rate (%)', 'Loan Utilization (%)', 'Avg Days Overdue', 'Performance Score', 'Risk Level', 'Recent Activity', 'Overdue Loans', 'Active Loans', 'Completed Loans'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '6', $header);
            $col++;
        }

        // Add data
        $row = 7;
        foreach ($performanceData['data'] as $index => $customer) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $customer['customer_no']);
            $sheet->setCellValue('C' . $row, $customer['customer_name']);
            $sheet->setCellValue('D' . $row, $customer['branch_name']);
            $sheet->setCellValue('E' . $row, $customer['region_name']);
            $sheet->setCellValue('F' . $row, $customer['district_name']);
            $sheet->setCellValue('G' . $row, $customer['date_registered'] ? $customer['date_registered']->format('d/m/Y') : 'N/A');
            $sheet->setCellValue('H' . $row, $customer['total_loans']);
            $sheet->setCellValue('I' . $row, number_format($customer['total_loan_amount'], 2));
            $sheet->setCellValue('J' . $row, number_format($customer['total_repayments'], 2));
            $sheet->setCellValue('K' . $row, number_format($customer['total_collateral'], 2));
            $sheet->setCellValue('L' . $row, number_format($customer['repayment_rate'], 2));
            $sheet->setCellValue('M' . $row, number_format($customer['loan_utilization'], 2));
            $sheet->setCellValue('N' . $row, number_format($customer['average_days_overdue'], 1));
            $sheet->setCellValue('O' . $row, number_format($customer['performance_score'], 2));
            $sheet->setCellValue('P' . $row, ucfirst($customer['risk_level']));
            $sheet->setCellValue('Q' . $row, $customer['recent_activity_count']);
            $sheet->setCellValue('R' . $row, $customer['overdue_loans_count']);
            $sheet->setCellValue('S' . $row, $customer['active_loans_count']);
            $sheet->setCellValue('T' . $row, $customer['completed_loans_count']);
            $row++;
        }

        // Add summary
        $row += 2;
        $sheet->setCellValue('A' . $row, 'SUMMARY');
        $sheet->setCellValue('B' . $row, 'Total Customers: ' . $performanceData['summary']['total_customers']);
        $row++;
        $sheet->setCellValue('B' . $row, 'Excellent Performers: ' . $performanceData['summary']['excellent_performers']);
        $row++;
        $sheet->setCellValue('B' . $row, 'Good Performers: ' . $performanceData['summary']['good_performers']);
        $row++;
        $sheet->setCellValue('B' . $row, 'Average Performers: ' . $performanceData['summary']['average_performers']);
        $row++;
        $sheet->setCellValue('B' . $row, 'Poor Performers: ' . $performanceData['summary']['poor_performers']);
        $row++;
        $sheet->setCellValue('B' . $row, 'Low Risk Customers: ' . $performanceData['summary']['low_risk_customers']);
        $row++;
        $sheet->setCellValue('B' . $row, 'Medium Risk Customers: ' . $performanceData['summary']['medium_risk_customers']);
        $row++;
        $sheet->setCellValue('B' . $row, 'High Risk Customers: ' . $performanceData['summary']['high_risk_customers']);
        $row++;
        $sheet->setCellValue('B' . $row, 'Average Performance Score: ' . number_format($performanceData['summary']['average_performance_score'], 2));
        $row++;
        $sheet->setCellValue('B' . $row, 'Average Repayment Rate: ' . number_format($performanceData['summary']['average_repayment_rate'], 2) . '%');

        // Auto-size columns
        foreach (range('A', 'T') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $filename = 'customer_performance_report_' . $startDate . '_to_' . $endDate . '.xlsx';
        
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'customer_performance_report');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }

    public function exportPdf(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get filter parameters
        $startDate = $request->get('start_date', now()->subMonths(6)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $branchId = $request->get('branch_id', 'all');
        $customerId = $request->get('customer_id', 'all');
        $performanceMetric = $request->get('performance_metric', 'all');
        $riskLevel = $request->get('risk_level', 'all');

        // Get performance data
        $performanceData = $this->getPerformanceData($startDate, $endDate, $branchId, $customerId, $performanceMetric, $riskLevel);

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

        $performanceMetricName = ucfirst($performanceMetric);
        $riskLevelName = ucfirst($riskLevel);

        $pdf = Pdf::loadView('reports.customers.performance-pdf', [
            'performanceData' => $performanceData,
            'company' => $company,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'branchName' => $branchName,
            'customerName' => $customerName,
            'performanceMetricName' => $performanceMetricName,
            'riskLevelName' => $riskLevelName,
            'user' => $user
        ]);

        $filename = 'customer_performance_report_' . $startDate . '_to_' . $endDate . '.pdf';
        return $pdf->download($filename);
    }
}
