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

class CustomerRiskAssessmentReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get filter parameters
        $startDate = $request->get('start_date', now()->subYear()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $branchId = $request->get('branch_id', 'all');
        $riskLevel = $request->get('risk_level', 'all');
        $customerId = $request->get('customer_id', 'all');
        $assessmentType = $request->get('assessment_type', 'all');

        // Get user's assigned branches
        $branches = $user->branches()->where('company_id', $company->id)->get();
        
        // Get customers for filter
        $customers = \App\Models\Customer::where('company_id', $company->id)
            ->orderBy('name')
            ->get();

        // Get risk assessment data
        $riskData = $this->getRiskAssessmentData($startDate, $endDate, $branchId, $riskLevel, $customerId, $assessmentType);

        return view('reports.customers.risk-assessment', compact(
            'riskData',
            'startDate',
            'endDate',
            'branchId',
            'riskLevel',
            'customerId',
            'assessmentType',
            'branches',
            'customers',
            'user'
        ));
    }

    private function getRiskAssessmentData($startDate, $endDate, $branchId, $riskLevel, $customerId, $assessmentType)
    {
        $user = Auth::user();
        $company = $user->company;

        // Build base query for customers
        $customerQuery = \App\Models\Customer::with(['region', 'district', 'branch', 'loans', 'collaterals'])
            ->where('company_id', $company->id);

        // Apply filters
        if ($branchId !== 'all') {
            $customerQuery->where('branch_id', $branchId);
        }

        if ($customerId !== 'all') {
            $customerQuery->where('id', $customerId);
        }

        $customers = $customerQuery->get();

        $riskAssessments = collect();

        foreach ($customers as $customer) {
            // Calculate risk assessment for each customer
            $assessment = $this->calculateCustomerRiskAssessment($customer, $startDate, $endDate);
            
            // Apply risk level filter
            if ($riskLevel !== 'all') {
                if ($riskLevel !== $assessment['risk_level']) continue;
            }

            // Apply assessment type filter
            if ($assessmentType !== 'all') {
                if ($assessmentType === 'high_risk' && $assessment['risk_score'] < 70) continue;
                if ($assessmentType === 'medium_risk' && ($assessment['risk_score'] < 40 || $assessment['risk_score'] >= 70)) continue;
                if ($assessmentType === 'low_risk' && $assessment['risk_score'] >= 40) continue;
            }

            $riskAssessments->push($assessment);
        }

        // Sort by risk score (lowest first - highest risk first)
        $riskAssessments = $riskAssessments->sortBy('risk_score');

        // Calculate summary statistics
        $summary = [
            'total_customers' => $riskAssessments->count(),
            'high_risk_customers' => $riskAssessments->where('risk_level', 'high')->count(),
            'medium_risk_customers' => $riskAssessments->where('risk_level', 'medium')->count(),
            'low_risk_customers' => $riskAssessments->where('risk_level', 'low')->count(),
            'average_risk_score' => $riskAssessments->avg('risk_score'),
            'total_loan_amount' => $riskAssessments->sum('total_loan_amount'),
            'total_outstanding_amount' => $riskAssessments->sum('outstanding_amount'),
            'total_collateral_value' => $riskAssessments->sum('total_collateral'),
            'customers_with_overdue' => $riskAssessments->where('has_overdue', true)->count(),
            'customers_without_collateral' => $riskAssessments->where('has_collateral', false)->count(),
            'average_repayment_rate' => $riskAssessments->avg('repayment_rate'),
            'average_days_overdue' => $riskAssessments->avg('average_days_overdue')
        ];

        return [
            'data' => $riskAssessments,
            'summary' => $summary
        ];
    }

    private function calculateCustomerRiskAssessment($customer, $startDate, $endDate)
    {
        // Get customer's loans
        $loans = $customer->loans;
        $totalLoanAmount = $loans->sum('amount');
        
        // Get repayments within date range
        $repayments = DB::table("receipts")
            ->where("reference_type", "loan_repayment")
            ->where("payee_id", $customer->id)
            ->whereBetween('date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->get();
        
        $totalRepayments = $repayments->sum('amount');
        
        // Get collaterals
        $totalCollateral = $customer->collaterals->sum('amount');
        $hasCollateral = $totalCollateral > 0;
        
        // Calculate repayment rate
        $repaymentRate = $totalLoanAmount > 0 ? ($totalRepayments / $totalLoanAmount) * 100 : 0;
        
        // Calculate overdue days and outstanding amount
        $overdueData = $this->calculateOverdueData($customer);
        $averageDaysOverdue = $overdueData['average'];
        $outstandingAmount = $overdueData['outstanding'];
        $hasOverdue = $overdueData['count'] > 0;
        
        // Calculate risk score (0-100, where 0 is highest risk)
        $riskScore = $this->calculateRiskScore($repaymentRate, $averageDaysOverdue, $totalCollateral, $totalLoanAmount, $hasCollateral, $hasOverdue, $customer);
        
        // Determine risk level
        $riskLevel = $this->determineRiskLevel($riskScore);
        
        // Calculate additional risk factors
        $riskFactors = $this->identifyRiskFactors($customer, $repaymentRate, $averageDaysOverdue, $hasCollateral, $hasOverdue);
        
        // Get recent activity
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
            'outstanding_amount' => $outstandingAmount,
            'total_repayments' => $totalRepayments,
            'total_collateral' => $totalCollateral,
            'has_collateral' => $hasCollateral,
            'repayment_rate' => $repaymentRate,
            'loan_utilization' => $loanUtilization,
            'average_days_overdue' => $averageDaysOverdue,
            'has_overdue' => $hasOverdue,
            'overdue_count' => $overdueData['count'],
            'risk_score' => $riskScore,
            'risk_level' => $riskLevel,
            'risk_factors' => $riskFactors,
            'recent_activity_count' => $recentActivityCount,
            'active_loans_count' => $loans->where('status', 'active')->count(),
            'completed_loans_count' => $loans->where('status', 'completed')->count(),
            'age' => $customer->dob ? $customer->dob ? $customer->dob ? floor($customer->dob->diffInYears(now())) : null : null : null,
            'category' => $customer->category,
            'sex' => $customer->sex
        ];
    }

    private function calculateOverdueData($customer)
    {
        $overdueDays = [];
        $totalOverdueDays = 0;
        $overdueCount = 0;
        $outstandingAmount = 0;

        foreach ($customer->loans as $loan) {
            if ($loan->status === "active") {
                // Get loan schedules that are overdue (due_date < now)
                $schedules = \App\Models\LoanSchedule::where("loan_id", $loan->id)
                    ->where("due_date", "<", now())
                    ->get();

                foreach ($schedules as $schedule) {
                    // Check if there is a repayment for this loan
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
                        $outstandingAmount += $schedule->principal + $schedule->interest + $schedule->fee_amount;
                    }
                }
            }
        }

        $averageOverdueDays = $overdueCount > 0 ? $totalOverdueDays / $overdueCount : 0;

        return [
            'average' => $averageOverdueDays,
            'count' => $overdueCount,
            'total' => $totalOverdueDays,
            'outstanding' => $outstandingAmount
        ];
    }

    private function calculateRiskScore($repaymentRate, $averageDaysOverdue, $totalCollateral, $totalLoanAmount, $hasCollateral, $hasOverdue, $customer)
    {
        $score = 0;
        
        // Repayment rate component (30% weight)
        $repaymentScore = min($repaymentRate, 100);
        $score += ($repaymentScore / 100) * 30;
        
        // Overdue days component (25% weight) - lower is better
        $overdueScore = max(0, 100 - ($averageDaysOverdue * 3)); // Penalty of 3 points per day
        $score += ($overdueScore / 100) * 25;
        
        // Collateral ratio component (20% weight)
        $collateralRatio = $totalLoanAmount > 0 ? ($totalCollateral / $totalLoanAmount) * 100 : 0;
        $collateralScore = min($collateralRatio * 2, 100); // Bonus for collateral
        $score += ($collateralScore / 100) * 20;
        
        // Customer age component (10% weight) - older customers are generally lower risk
        $ageScore = 0;
        if ($customer->dob) {
            $age = $customer->dob ? $customer->dob ? floor($customer->dob->diffInYears(now())) : null : null;
            if ($age >= 25 && $age <= 55) {
                $ageScore = 100; // Prime age group
            } elseif ($age >= 18 && $age < 25) {
                $ageScore = 70; // Young adults
            } elseif ($age > 55 && $age <= 65) {
                $ageScore = 80; // Mature adults
            } else {
                $ageScore = 50; // Very young or very old
            }
        }
        $score += ($ageScore / 100) * 10;
        
        // Customer category component (10% weight) - groups are generally lower risk
        $categoryScore = $customer->category === 'group' ? 100 : 70;
        $score += ($categoryScore / 100) * 10;
        
        // Penalty for having overdue loans (5% weight)
        $overduePenalty = $hasOverdue ? 0 : 100;
        $score += ($overduePenalty / 100) * 5;
        
        return round($score, 2);
    }

    private function determineRiskLevel($riskScore)
    {
        if ($riskScore >= 80) {
            return 'low';
        } elseif ($riskScore >= 50) {
            return 'medium';
        } else {
            return 'high';
        }
    }

    private function identifyRiskFactors($customer, $repaymentRate, $averageDaysOverdue, $hasCollateral, $hasOverdue)
    {
        $factors = [];
        
        if ($repaymentRate < 50) {
            $factors[] = 'Low repayment rate';
        }
        
        if ($averageDaysOverdue > 30) {
            $factors[] = 'Frequent overdue payments';
        }
        
        if (!$hasCollateral) {
            $factors[] = 'No collateral provided';
        }
        
        if ($hasOverdue) {
            $factors[] = 'Currently has overdue loans';
        }
        
        if ($customer->category === 'individual') {
            $factors[] = 'Individual customer (higher risk)';
        }
        
        if ($customer->dob) {
            $age = $customer->dob ? $customer->dob ? floor($customer->dob->diffInYears(now())) : null : null;
            if ($age < 25 || $age > 65) {
                $factors[] = 'Age-related risk factor';
            }
        }
        
        return $factors;
    }

    public function export(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get filter parameters
        $startDate = $request->get('start_date', now()->subYear()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $branchId = $request->get('branch_id', 'all');
        $riskLevel = $request->get('risk_level', 'all');
        $customerId = $request->get('customer_id', 'all');
        $assessmentType = $request->get('assessment_type', 'all');

        // Get risk assessment data
        $riskData = $this->getRiskAssessmentData($startDate, $endDate, $branchId, $riskLevel, $customerId, $assessmentType);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', 'Customer Risk Assessment Report');
        $sheet->setCellValue('A2', 'Company: ' . $company->name);
        $sheet->setCellValue('A3', 'Period: ' . $startDate . ' to ' . $endDate);
        $sheet->setCellValue('A4', 'Generated: ' . now()->format('Y-m-d H:i:s'));

        // Set column headers
        $headers = ['#', 'Customer No', 'Customer Name', 'Branch', 'Region', 'District', 'Date Registered', 'Age', 'Category', 'Total Loans', 'Total Loan Amount', 'Outstanding Amount', 'Total Repayments', 'Total Collateral', 'Repayment Rate (%)', 'Loan Utilization (%)', 'Avg Days Overdue', 'Risk Score', 'Risk Level', 'Risk Factors', 'Has Overdue', 'Overdue Count', 'Active Loans', 'Completed Loans'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '6', $header);
            $col++;
        }

        // Add data
        $row = 7;
        foreach ($riskData['data'] as $index => $customer) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $customer['customer_no']);
            $sheet->setCellValue('C' . $row, $customer['customer_name']);
            $sheet->setCellValue('D' . $row, $customer['branch_name']);
            $sheet->setCellValue('E' . $row, $customer['region_name']);
            $sheet->setCellValue('F' . $row, $customer['district_name']);
            $sheet->setCellValue('G' . $row, $customer['date_registered'] ? $customer['date_registered']->format('d/m/Y') : 'N/A');
            $sheet->setCellValue('H' . $row, $customer['age'] ?? 'N/A');
            $sheet->setCellValue('I' . $row, ucfirst($customer['category']));
            $sheet->setCellValue('J' . $row, $customer['total_loans']);
            $sheet->setCellValue('K' . $row, number_format($customer['total_loan_amount'], 2));
            $sheet->setCellValue('L' . $row, number_format($customer['outstanding_amount'], 2));
            $sheet->setCellValue('M' . $row, number_format($customer['total_repayments'], 2));
            $sheet->setCellValue('N' . $row, number_format($customer['total_collateral'], 2));
            $sheet->setCellValue('O' . $row, number_format($customer['repayment_rate'], 2));
            $sheet->setCellValue('P' . $row, number_format($customer['loan_utilization'], 2));
            $sheet->setCellValue('Q' . $row, number_format($customer['average_days_overdue'], 1));
            $sheet->setCellValue('R' . $row, number_format($customer['risk_score'], 2));
            $sheet->setCellValue('S' . $row, ucfirst($customer['risk_level']));
            $sheet->setCellValue('T' . $row, implode(', ', $customer['risk_factors']));
            $sheet->setCellValue('U' . $row, $customer['has_overdue'] ? 'Yes' : 'No');
            $sheet->setCellValue('V' . $row, $customer['overdue_count']);
            $sheet->setCellValue('W' . $row, $customer['active_loans_count']);
            $sheet->setCellValue('X' . $row, $customer['completed_loans_count']);
            $row++;
        }

        // Add summary
        $row += 2;
        $sheet->setCellValue('A' . $row, 'SUMMARY');
        $sheet->setCellValue('B' . $row, 'Total Customers: ' . $riskData['summary']['total_customers']);
        $row++;
        $sheet->setCellValue('B' . $row, 'High Risk Customers: ' . $riskData['summary']['high_risk_customers']);
        $row++;
        $sheet->setCellValue('B' . $row, 'Medium Risk Customers: ' . $riskData['summary']['medium_risk_customers']);
        $row++;
        $sheet->setCellValue('B' . $row, 'Low Risk Customers: ' . $riskData['summary']['low_risk_customers']);
        $row++;
        $sheet->setCellValue('B' . $row, 'Average Risk Score: ' . number_format($riskData['summary']['average_risk_score'], 2));
        $row++;
        $sheet->setCellValue('B' . $row, 'Total Outstanding Amount: ' . number_format($riskData['summary']['total_outstanding_amount'], 2));
        $row++;
        $sheet->setCellValue('B' . $row, 'Customers with Overdue: ' . $riskData['summary']['customers_with_overdue']);

        // Auto-size columns
        foreach (range('A', 'X') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $filename = 'customer_risk_assessment_report_' . $startDate . '_to_' . $endDate . '.xlsx';
        
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'customer_risk_assessment_report');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }

    public function exportPdf(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get filter parameters
        $startDate = $request->get('start_date', now()->subYear()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $branchId = $request->get('branch_id', 'all');
        $riskLevel = $request->get('risk_level', 'all');
        $customerId = $request->get('customer_id', 'all');
        $assessmentType = $request->get('assessment_type', 'all');

        // Get risk assessment data
        $riskData = $this->getRiskAssessmentData($startDate, $endDate, $branchId, $riskLevel, $customerId, $assessmentType);

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

        $riskLevelName = ucfirst($riskLevel);
        $assessmentTypeName = ucfirst($assessmentType);

        $pdf = Pdf::loadView('reports.customers.risk-assessment-pdf', [
            'riskData' => $riskData,
            'company' => $company,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'branchName' => $branchName,
            'customerName' => $customerName,
            'riskLevelName' => $riskLevelName,
            'assessmentTypeName' => $assessmentTypeName,
            'user' => $user
        ]);

        $filename = 'customer_risk_assessment_report_' . $startDate . '_to_' . $endDate . '.pdf';
        return $pdf->download($filename);
    }
}
