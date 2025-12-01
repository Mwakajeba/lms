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

class CustomerListReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get filter parameters
        $branchId = $request->get('branch_id', 'all');
        $regionId = $request->get('region_id', 'all');
        $districtId = $request->get('district_id', 'all');
        $category = $request->get('category', 'all');
        $sex = $request->get('sex', 'all');
        $hasLoans = $request->get('has_loans', 'all');
        $hasCollateral = $request->get('has_collateral', 'all');
        $registrationDateFrom = $request->get('registration_date_from', '');
        $registrationDateTo = $request->get('registration_date_to', '');

        // Get user's assigned branches
        $branches = $user->branches()
            ->where('branches.company_id', $company->id)
            ->select('branches.id', 'branches.name')
            ->get();

        // If user has exactly one branch, force-select it
        if (($branches->count() ?? 0) === 1) {
            $branchId = $branches->first()->id;
        }
        
        // Get regions and districts
        $regions = \App\Models\Region::orderBy('name')->get();
        $districts = \App\Models\District::orderBy('name')->get();

        // Get customers data
        $customersData = $this->getCustomersData($branchId, $regionId, $districtId, $category, $sex, $hasLoans, $hasCollateral, $registrationDateFrom, $registrationDateTo);

        return view('reports.customers.list', compact(
            'customersData',
            'branchId',
            'regionId',
            'districtId',
            'category',
            'sex',
            'hasLoans',
            'hasCollateral',
            'registrationDateFrom',
            'registrationDateTo',
            'branches',
            'regions',
            'districts',
            'user'
        ));
    }

    private function getCustomersData($branchId, $regionId, $districtId, $category, $sex, $hasLoans, $hasCollateral, $registrationDateFrom, $registrationDateTo)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get user's assigned branch IDs for filtering
        $assignedBranchIds = $user->branches()
            ->where('branches.company_id', $company->id)
            ->pluck('branches.id')
            ->toArray();

        // Build query for customers
        $query = \App\Models\Customer::with(['region', 'district', 'branch', 'loans', 'collaterals'])
            ->where('company_id', $company->id)
            ->whereIn('branch_id', $assignedBranchIds);

        // Apply filters
        if ($branchId !== 'all') {
            $query->where('branch_id', $branchId);
        }

        if ($regionId !== 'all') {
            $query->where('region_id', $regionId);
        }

        if ($districtId !== 'all') {
            $query->where('district_id', $districtId);
        }

        if ($category !== 'all') {
            $query->where('category', $category);
        }

        if ($sex !== 'all') {
            $query->where('sex', $sex);
        }

        if ($hasLoans !== 'all') {
            if ($hasLoans === 'yes') {
                $query->whereHas('loans');
            } else {
                $query->whereDoesntHave('loans');
            }
        }

        if ($hasCollateral !== 'all') {
            if ($hasCollateral === 'yes') {
                $query->where('has_cash_collateral', true);
            } else {
                $query->where('has_cash_collateral', false);
            }
        }

        if ($registrationDateFrom) {
            $query->where('dateRegistered', '>=', $registrationDateFrom);
        }

        if ($registrationDateTo) {
            $query->where('dateRegistered', '<=', $registrationDateTo);
        }

        $customers = $query->orderBy('name')->get();

        // Calculate summary statistics
        $summary = [
            'total_customers' => $customers->count(),
            'male_customers' => $customers->where('sex', 'male')->count(),
            'female_customers' => $customers->where('sex', 'female')->count(),
            'customers_with_loans' => $customers->filter(function($customer) {
                return $customer->loans->count() > 0;
            })->count(),
            'customers_with_collateral' => $customers->where('has_cash_collateral', true)->count(),
            'total_loans' => $customers->sum(function($customer) {
                return $customer->loans->count();
            }),
            'total_loan_amount' => $customers->sum(function($customer) {
                return $customer->loans->sum('amount');
            }),
            'total_collateral_amount' => $customers->sum(function($customer) {
                return $customer->collaterals->sum('amount');
            })
        ];

        return [
            'data' => $customers,
            'summary' => $summary
        ];
    }

    public function export(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get filter parameters
        $branchId = $request->get('branch_id', 'all');
        $regionId = $request->get('region_id', 'all');
        $districtId = $request->get('district_id', 'all');
        $category = $request->get('category', 'all');
        $sex = $request->get('sex', 'all');
        $hasLoans = $request->get('has_loans', 'all');
        $hasCollateral = $request->get('has_collateral', 'all');
        $registrationDateFrom = $request->get('registration_date_from', '');
        $registrationDateTo = $request->get('registration_date_to', '');

        // Get customers data
        $customersData = $this->getCustomersData($branchId, $regionId, $districtId, $category, $sex, $hasLoans, $hasCollateral, $registrationDateFrom, $registrationDateTo);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', 'Customer List Report');
        $sheet->setCellValue('A2', 'Company: ' . $company->name);
        $sheet->setCellValue('A3', 'Generated: ' . now()->format('Y-m-d H:i:s'));

        // Set column headers
        $headers = ['#', 'Customer No', 'Name', 'Phone', 'Email', 'Region', 'District', 'Branch', 'Category', 'Sex', 'Date Registered', 'Has Loans', 'Loan Count', 'Total Loan Amount', 'Has Collateral', 'Collateral Amount'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '5', $header);
            $col++;
        }

        // Add data
        $row = 6;
        foreach ($customersData['data'] as $index => $customer) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $customer->customerNo);
            $sheet->setCellValue('C' . $row, $customer->name);
            $sheet->setCellValue('D' . $row, $customer->phone1);
            $sheet->setCellValue('E' . $row, $customer->email ?? 'N/A');
            $sheet->setCellValue('F' . $row, $customer->region->name ?? 'N/A');
            $sheet->setCellValue('G' . $row, $customer->district->name ?? 'N/A');
            $sheet->setCellValue('H' . $row, $customer->branch->name ?? 'N/A');
            $sheet->setCellValue('I' . $row, $customer->category ?? 'N/A');
            $sheet->setCellValue('J' . $row, ucfirst($customer->sex));
            $sheet->setCellValue('K' . $row, $customer->dateRegistered ? $customer->dateRegistered->format('d/m/Y') : 'N/A');
            $sheet->setCellValue('L' . $row, $customer->loans->count() > 0 ? 'Yes' : 'No');
            $sheet->setCellValue('M' . $row, $customer->loans->count());
            $sheet->setCellValue('N' . $row, number_format($customer->loans->sum('amount'), 2));
            $sheet->setCellValue('O' . $row, $customer->has_cash_collateral ? 'Yes' : 'No');
            $sheet->setCellValue('P' . $row, number_format($customer->collaterals->sum('amount'), 2));
            $row++;
        }

        // Add summary
        $row += 2;
        $sheet->setCellValue('A' . $row, 'SUMMARY');
        $sheet->setCellValue('B' . $row, 'Total Customers: ' . $customersData['summary']['total_customers']);
        $row++;
        $sheet->setCellValue('B' . $row, 'Male Customers: ' . $customersData['summary']['male_customers']);
        $row++;
        $sheet->setCellValue('B' . $row, 'Female Customers: ' . $customersData['summary']['female_customers']);
        $row++;
        $sheet->setCellValue('B' . $row, 'Customers with Loans: ' . $customersData['summary']['customers_with_loans']);
        $row++;
        $sheet->setCellValue('B' . $row, 'Customers with Collateral: ' . $customersData['summary']['customers_with_collateral']);
        $row++;
        $sheet->setCellValue('B' . $row, 'Total Loans: ' . $customersData['summary']['total_loans']);
        $row++;
        $sheet->setCellValue('B' . $row, 'Total Loan Amount: ' . number_format($customersData['summary']['total_loan_amount'], 2));
        $row++;
        $sheet->setCellValue('B' . $row, 'Total Collateral Amount: ' . number_format($customersData['summary']['total_collateral_amount'], 2));

        // Auto-size columns
        foreach (range('A', 'P') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $filename = 'customer_list_report_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'customer_list_report');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }

    public function exportPdf(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get filter parameters
        $branchId = $request->get('branch_id', 'all');
        $regionId = $request->get('region_id', 'all');
        $districtId = $request->get('district_id', 'all');
        $category = $request->get('category', 'all');
        $sex = $request->get('sex', 'all');
        $hasLoans = $request->get('has_loans', 'all');
        $hasCollateral = $request->get('has_collateral', 'all');
        $registrationDateFrom = $request->get('registration_date_from', '');
        $registrationDateTo = $request->get('registration_date_to', '');

        // Get customers data
        $customersData = $this->getCustomersData($branchId, $regionId, $districtId, $category, $sex, $hasLoans, $hasCollateral, $registrationDateFrom, $registrationDateTo);

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

        $pdf = Pdf::loadView('reports.customers.pdf', [
            'customersData' => $customersData,
            'company' => $company,
            'branchName' => $branchName,
            'regionName' => $regionName,
            'districtName' => $districtName,
            'category' => $category,
            'sex' => $sex,
            'hasLoans' => $hasLoans,
            'hasCollateral' => $hasCollateral,
            'registrationDateFrom' => $registrationDateFrom,
            'registrationDateTo' => $registrationDateTo,
            'user' => $user
        ])->setPaper('a4', 'landscape');

        $filename = 'customer_list_report_' . now()->format('Y-m-d_H-i-s') . '.pdf';
        return $pdf->download($filename);
    }
}
