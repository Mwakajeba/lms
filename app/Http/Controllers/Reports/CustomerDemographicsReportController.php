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

class CustomerDemographicsReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get filter parameters
        $startDate = $request->get('start_date', now()->subYear()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $branchId = $request->get('branch_id', 'all');
        $regionId = $request->get('region_id', 'all');
        $districtId = $request->get('district_id', 'all');
        $gender = $request->get('gender', 'all');
        $category = $request->get('category', 'all');
        $ageGroup = $request->get('age_group', 'all');

        // Get user's assigned branches
        $branches = $user->branches()
            ->where('branches.company_id', $company->id)
            ->select('branches.id', 'branches.name')
            ->get();

        // If user has exactly one branch, force-select it
        if (($branches->count() ?? 0) === 1) {
            $branchId = $branches->first()->id;
        }
        
        // Get regions and districts for filter
        $regions = \App\Models\Region::orderBy('name')->get();
        $districts = \App\Models\District::orderBy('name')->get();

        // Get demographics data
        $demographicsData = $this->getDemographicsData($startDate, $endDate, $branchId, $regionId, $districtId, $gender, $category, $ageGroup);

        return view('reports.customers.demographics', compact(
            'demographicsData',
            'startDate',
            'endDate',
            'branchId',
            'regionId',
            'districtId',
            'gender',
            'category',
            'ageGroup',
            'branches',
            'regions',
            'districts',
            'user'
        ));
    }

    private function getDemographicsData($startDate, $endDate, $branchId, $regionId, $districtId, $gender, $category, $ageGroup)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get user's assigned branch IDs for filtering
        $assignedBranchIds = $user->branches()
            ->where('branches.company_id', $company->id)
            ->pluck('branches.id')
            ->toArray();

        // Build base query for customers
        $customerQuery = \App\Models\Customer::with(['region', 'district', 'branch', 'loans', 'collaterals'])
            ->where('company_id', $company->id)
            ->whereBetween('dateRegistered', [$startDate, $endDate])
            ->whereIn('branch_id', $assignedBranchIds);

        // Apply filters
        if ($branchId !== 'all') {
            $customerQuery->where('branch_id', $branchId);
        }

        if ($regionId !== 'all') {
            $customerQuery->where('region_id', $regionId);
        }

        if ($districtId !== 'all') {
            $customerQuery->where('district_id', $districtId);
        }

        if ($gender !== 'all') {
            $customerQuery->where('sex', $gender);
        }

        if ($category !== 'all') {
            $customerQuery->where('category', $category);
        }

        $customers = $customerQuery->get();

        // Calculate age groups
        $customers = $customers->map(function ($customer) {
            $customer->age = $customer->dob ? $customer->dob ? $customer->dob ? floor($customer->dob->diffInYears(now())) : null : null : null;
            $customer->age_group = $this->getAgeGroup($customer->age);
            return $customer;
        });

        // Apply age group filter
        if ($ageGroup !== 'all') {
            $customers = $customers->filter(function ($customer) use ($ageGroup) {
                return $customer->age_group === $ageGroup;
            });
        }

        // Calculate demographic statistics
        $demographics = $this->calculateDemographicStatistics($customers);

        return [
            'customers' => $customers,
            'statistics' => $demographics
        ];
    }

    private function getAgeGroup($age)
    {
        if ($age === null) return 'Unknown';
        
        if ($age < 18) return 'Under 18';
        if ($age >= 18 && $age <= 25) return '18-25';
        if ($age >= 26 && $age <= 35) return '26-35';
        if ($age >= 36 && $age <= 45) return '36-45';
        if ($age >= 46 && $age <= 55) return '46-55';
        if ($age >= 56 && $age <= 65) return '56-65';
        return 'Over 65';
    }

    private function calculateDemographicStatistics($customers)
    {
        $totalCustomers = $customers->count();

        // Gender distribution
        $genderDistribution = $customers->groupBy('sex')->map(function ($group) use ($totalCustomers) {
            return [
                'count' => $group->count(),
                'percentage' => $totalCustomers > 0 ? round(($group->count() / $totalCustomers) * 100, 2) : 0
            ];
        });

        // Age group distribution
        $ageGroupDistribution = $customers->groupBy('age_group')->map(function ($group) use ($totalCustomers) {
            return [
                'count' => $group->count(),
                'percentage' => $totalCustomers > 0 ? round(($group->count() / $totalCustomers) * 100, 2) : 0
            ];
        });

        // Category distribution
        $categoryDistribution = $customers->groupBy('category')->map(function ($group) use ($totalCustomers) {
            return [
                'count' => $group->count(),
                'percentage' => $totalCustomers > 0 ? round(($group->count() / $totalCustomers) * 100, 2) : 0
            ];
        });

        // Region distribution
        $regionDistribution = $customers->groupBy('region.name')->map(function ($group) use ($totalCustomers) {
            return [
                'count' => $group->count(),
                'percentage' => $totalCustomers > 0 ? round(($group->count() / $totalCustomers) * 100, 2) : 0
            ];
        });

        // District distribution
        $districtDistribution = $customers->groupBy('district.name')->map(function ($group) use ($totalCustomers) {
            return [
                'count' => $group->count(),
                'percentage' => $totalCustomers > 0 ? round(($group->count() / $totalCustomers) * 100, 2) : 0
            ];
        });

        // Branch distribution
        $branchDistribution = $customers->groupBy('branch.name')->map(function ($group) use ($totalCustomers) {
            return [
                'count' => $group->count(),
                'percentage' => $totalCustomers > 0 ? round(($group->count() / $totalCustomers) * 100, 2) : 0
            ];
        });

        // Loan statistics
        $customersWithLoans = $customers->filter(function ($customer) {
            return $customer->loans->count() > 0;
        });

        $customersWithCollateral = $customers->filter(function ($customer) {
            return $customer->collaterals->count() > 0;
        });

        // Average age calculation
        $ages = $customers->pluck('age')->filter();
        $averageAge = $ages->count() > 0 ? round($ages->avg(), 1) : 0;

        // Registration trends (monthly)
        $monthlyRegistrations = $customers->groupBy(function ($customer) {
            return $customer->dateRegistered->format('Y-m');
        })->map(function ($group) {
            return $group->count();
        })->sortKeys();

        return [
            'total_customers' => $totalCustomers,
            'gender_distribution' => $genderDistribution,
            'age_group_distribution' => $ageGroupDistribution,
            'category_distribution' => $categoryDistribution,
            'region_distribution' => $regionDistribution,
            'district_distribution' => $districtDistribution,
            'branch_distribution' => $branchDistribution,
            'customers_with_loans' => $customersWithLoans->count(),
            'customers_with_collateral' => $customersWithCollateral->count(),
            'loan_percentage' => $totalCustomers > 0 ? round(($customersWithLoans->count() / $totalCustomers) * 100, 2) : 0,
            'collateral_percentage' => $totalCustomers > 0 ? round(($customersWithCollateral->count() / $totalCustomers) * 100, 2) : 0,
            'average_age' => $averageAge,
            'monthly_registrations' => $monthlyRegistrations
        ];
    }

    public function export(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get filter parameters
        $startDate = $request->get('start_date', now()->subYear()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $branchId = $request->get('branch_id', 'all');
        $regionId = $request->get('region_id', 'all');
        $districtId = $request->get('district_id', 'all');
        $gender = $request->get('gender', 'all');
        $category = $request->get('category', 'all');
        $ageGroup = $request->get('age_group', 'all');

        // Get demographics data
        $demographicsData = $this->getDemographicsData($startDate, $endDate, $branchId, $regionId, $districtId, $gender, $category, $ageGroup);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', 'Customer Demographics Report');
        $sheet->setCellValue('A2', 'Company: ' . $company->name);
        $sheet->setCellValue('A3', 'Period: ' . $startDate . ' to ' . $endDate);
        $sheet->setCellValue('A4', 'Generated: ' . now()->format('Y-m-d H:i:s'));

        // Add summary statistics
        $row = 6;
        $sheet->setCellValue('A' . $row, 'SUMMARY STATISTICS');
        $row++;
        $sheet->setCellValue('A' . $row, 'Total Customers: ' . $demographicsData['statistics']['total_customers']);
        $row++;
        $sheet->setCellValue('A' . $row, 'Average Age: ' . $demographicsData['statistics']['average_age']);
        $row++;
        $sheet->setCellValue('A' . $row, 'Customers with Loans: ' . $demographicsData['statistics']['customers_with_loans'] . ' (' . $demographicsData['statistics']['loan_percentage'] . '%)');
        $row++;
        $sheet->setCellValue('A' . $row, 'Customers with Collateral: ' . $demographicsData['statistics']['customers_with_collateral'] . ' (' . $demographicsData['statistics']['collateral_percentage'] . '%)');
        $row += 2;

        // Gender Distribution
        $sheet->setCellValue('A' . $row, 'GENDER DISTRIBUTION');
        $row++;
        $sheet->setCellValue('A' . $row, 'Gender');
        $sheet->setCellValue('B' . $row, 'Count');
        $sheet->setCellValue('C' . $row, 'Percentage');
        $row++;
        foreach ($demographicsData['statistics']['gender_distribution'] as $gender => $data) {
            $sheet->setCellValue('A' . $row, ucfirst($gender));
            $sheet->setCellValue('B' . $row, $data['count']);
            $sheet->setCellValue('C' . $row, $data['percentage'] . '%');
            $row++;
        }
        $row += 2;

        // Age Group Distribution
        $sheet->setCellValue('A' . $row, 'AGE GROUP DISTRIBUTION');
        $row++;
        $sheet->setCellValue('A' . $row, 'Age Group');
        $sheet->setCellValue('B' . $row, 'Count');
        $sheet->setCellValue('C' . $row, 'Percentage');
        $row++;
        foreach ($demographicsData['statistics']['age_group_distribution'] as $ageGroup => $data) {
            $sheet->setCellValue('A' . $row, $ageGroup);
            $sheet->setCellValue('B' . $row, $data['count']);
            $sheet->setCellValue('C' . $row, $data['percentage'] . '%');
            $row++;
        }
        $row += 2;

        // Region Distribution
        $sheet->setCellValue('A' . $row, 'REGION DISTRIBUTION');
        $row++;
        $sheet->setCellValue('A' . $row, 'Region');
        $sheet->setCellValue('B' . $row, 'Count');
        $sheet->setCellValue('C' . $row, 'Percentage');
        $row++;
        foreach ($demographicsData['statistics']['region_distribution'] as $region => $data) {
            $sheet->setCellValue('A' . $row, $region);
            $sheet->setCellValue('B' . $row, $data['count']);
            $sheet->setCellValue('C' . $row, $data['percentage'] . '%');
            $row++;
        }
        $row += 2;

        // Branch Distribution
        $sheet->setCellValue('A' . $row, 'BRANCH DISTRIBUTION');
        $row++;
        $sheet->setCellValue('A' . $row, 'Branch');
        $sheet->setCellValue('B' . $row, 'Count');
        $sheet->setCellValue('C' . $row, 'Percentage');
        $row++;
        foreach ($demographicsData['statistics']['branch_distribution'] as $branch => $data) {
            $sheet->setCellValue('A' . $row, $branch);
            $sheet->setCellValue('B' . $row, $data['count']);
            $sheet->setCellValue('C' . $row, $data['percentage'] . '%');
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'C') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $filename = 'customer_demographics_report_' . $startDate . '_to_' . $endDate . '.xlsx';
        
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'customer_demographics_report');
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
        $regionId = $request->get('region_id', 'all');
        $districtId = $request->get('district_id', 'all');
        $gender = $request->get('gender', 'all');
        $category = $request->get('category', 'all');
        $ageGroup = $request->get('age_group', 'all');

        // Get demographics data
        $demographicsData = $this->getDemographicsData($startDate, $endDate, $branchId, $regionId, $districtId, $gender, $category, $ageGroup);

        // Get filter labels for display
        $branchName = 'All Branches';
        if ($branchId !== 'all') {
            $branch = \App\Models\Branch::find($branchId);
            $branchName = $branch ? $branch->name : 'Unknown Branch';
        }

        $regionName = 'All Regions';
        if ($regionId !== 'all') {
            $region = \App\Models\Region::find($regionId);
            $regionName = $region ? $region->name : 'Unknown Region';
        }

        $districtName = 'All Districts';
        if ($districtId !== 'all') {
            $district = \App\Models\District::find($districtId);
            $districtName = $district ? $district->name : 'Unknown District';
        }

        $genderName = ucfirst($gender);
        $categoryName = ucfirst($category);
        $ageGroupName = ucfirst($ageGroup);

        $pdf = Pdf::loadView('reports.customers.demographics-pdf', [
            'demographicsData' => $demographicsData,
            'company' => $company,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'branchName' => $branchName,
            'regionName' => $regionName,
            'districtName' => $districtName,
            'genderName' => $genderName,
            'categoryName' => $categoryName,
            'ageGroupName' => $ageGroupName,
            'user' => $user
        ]);

        $filename = 'customer_demographics_report_' . $startDate . '_to_' . $endDate . '.pdf';
        return $pdf->download($filename);
    }
}
