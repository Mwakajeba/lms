<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\Customer;
use App\Models\Branch;
use App\Models\User;
use Carbon\Carbon;

class BotGeographicalDistributionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));

        // Debug: Let's see the actual database structure
        \Log::info('=== DATABASE STRUCTURE DEBUG ===');
        
        // Check regions table structure
        try {
            $regions = \DB::table('regions')->get();
            \Log::info('Regions table data:', [
                'count' => $regions->count(),
                'sample' => $regions->take(5)->toArray(),
                'columns' => \DB::getSchemaBuilder()->getColumnListing('regions')
            ]);
        } catch (\Exception $e) {
            \Log::error('Error accessing regions table:', ['error' => $e->getMessage()]);
        }
        
        // Check districts table structure
        try {
            $districts = \DB::table('districts')->get();
            \Log::info('Districts table data:', [
                'count' => $districts->count(),
                'sample' => $districts->take(5)->toArray(),
                'columns' => \DB::getSchemaBuilder()->getColumnListing('districts')
            ]);
        } catch (\Exception $e) {
            \Log::error('Error accessing districts table:', ['error' => $e->getMessage()]);
        }
        
        // Check customers table structure
        try {
            $customers = \DB::table('customers')->get();
            \Log::info('Customers table data:', [
                'count' => $customers->count(),
                'sample' => $customers->take(3)->map(function($c) {
                    return [
                        'id' => $c->id,
                        'name' => $c->name ?? 'No name',
                        'region_id' => $c->region_id ?? 'No region_id',
                        'district_id' => $c->district_id ?? 'No district_id',
                        'sex' => $c->sex ?? 'No sex',
                        'dob' => $c->dob ?? 'No dob'
                    ];
                })->toArray(),
                'columns' => \DB::getSchemaBuilder()->getColumnListing('customers')
            ]);
        } catch (\Exception $e) {
            \Log::error('Error accessing customers table:', ['error' => $e->getMessage()]);
        }
        
        // Check loans table structure
        try {
            $loans = \DB::table('loans')->get();
            \Log::info('Loans table data:', [
                'count' => $loans->count(),
                'sample' => $loans->take(3)->map(function($l) {
                    return [
                        'id' => $l->id,
                        'customer_id' => $l->customer_id ?? 'No customer_id',
                        'amount' => $l->amount ?? 'No amount',
                        'amount_total' => $l->amount_total ?? 'No amount_total',
                        'status' => $l->status ?? 'No status'
                    ];
                })->toArray(),
                'columns' => \DB::getSchemaBuilder()->getColumnListing('loans')
            ]);
        } catch (\Exception $e) {
            \Log::error('Error accessing loans table:', ['error' => $e->getMessage()]);
        }
        
        \Log::info('=== END DATABASE STRUCTURE DEBUG ===');
        
        // Get actual geographical areas from database
        $geographicalAreas = [];
        $zanzibarAreas = [];
        
        try {
            // Get all regions
            $regions = \DB::table('regions')->pluck('name', 'id')->toArray();
            $geographicalAreas = array_values($regions);
            
            // Get all districts
            $districts = \DB::table('districts')->pluck('name', 'id')->toArray();
            $geographicalAreas = array_merge($geographicalAreas, array_values($districts));
            
            \Log::info('Dynamic geographical areas loaded:', [
                'regions_count' => count($regions),
                'districts_count' => count($districts),
                'total_areas' => count($geographicalAreas),
                'sample_areas' => array_slice($geographicalAreas, 0, 10)
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading geographical areas:', ['error' => $e->getMessage()]);
            // Fallback to empty arrays
            $geographicalAreas = [];
            $zanzibarAreas = [];
        }

        // Get actual data from database
        $areaData = [];
        
        // Get all customers with their regions, districts, and loans
        $customers = Customer::with(['region', 'district', 'loans.repayments'])
            ->whereHas('loans') // Get all customers with any loans
            ->get();
        
        // Debug: Log the data we're getting
        \Log::info('Geographical Distribution Report Debug', [
            'total_customers' => $customers->count(),
            'customers_with_loans' => $customers->filter(function($c) { return $c->loans->count() > 0; })->count(),
            'sample_customer' => $customers->first() ? [
                'id' => $customers->first()->id,
                'name' => $customers->first()->name,
                'region' => $customers->first()->region->name ?? 'No region',
                'district' => $customers->first()->district->name ?? 'No district',
                'loans_count' => $customers->first()->loans->count(),
                'loan_statuses' => $customers->first()->loans->pluck('status')->toArray(),
                'dob' => $customers->first()->dob,
                'sex' => $customers->first()->sex
            ] : 'No customers found'
        ]);

        // Calculate age and categorize data by geographical area
        foreach ($customers as $customer) {
            // Skip if no date of birth
            if (!$customer->dob) continue;
            
            try {
                $age = Carbon::parse($customer->dob)->age;
                $ageGroup = $age <= 35 ? 'up35' : 'above35';
                $gender = $customer->sex; // Keep original case
                
                // Map gender values to our expected format
                if ($gender === 'M') {
                    $gender = 'male';
                } elseif ($gender === 'F') {
                    $gender = 'female';
                } else {
                    // Skip if gender is not valid
                    continue;
                }
                
                // Get geographical area name (prefer district, fallback to region)
                $districtName = $customer->district->name ?? null;
                $regionName = $customer->region->name ?? null;
                
                // Debug: Log the geographical information
                \Log::info('Customer geographical info', [
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'district_name' => $districtName,
                    'region_name' => $regionName,
                    'district_id' => $customer->district_id,
                    'region_id' => $customer->region_id,
                    'sex' => $customer->sex,
                    'gender_mapped' => $gender,
                    'age' => $age,
                    'age_group' => $ageGroup
                ]);
                
                // Use the actual district or region name from database
                $areaName = $districtName ?? $regionName ?? 'Unknown';
                
                \Log::info('Processing area', [
                    'area_name' => $areaName,
                    'district_name' => $districtName,
                    'region_name' => $regionName,
                    'age_group' => $ageGroup,
                    'gender' => $gender
                ]);
                
                if (!isset($areaData[$areaName])) {
                    $areaData[$areaName] = [
                        'branches' => 0,
                        'employees' => 0,
                        'compulsory_savings' => 0,
                        'borrowers_up35_female' => 0,
                        'borrowers_up35_male' => 0,
                        'borrowers_above35_female' => 0,
                        'borrowers_above35_male' => 0,
                        'loans_up35_female' => 0,
                        'loans_up35_male' => 0,
                        'loans_above35_female' => 0,
                        'loans_above35_male' => 0,
                        'outstanding_up35_female' => 0,
                        'outstanding_up35_male' => 0,
                        'outstanding_above35_female' => 0,
                        'outstanding_above35_male' => 0
                    ];
                    \Log::info('Created new area data for: ' . $areaName);
                }
                
                // Count borrowers by age and gender
                $areaData[$areaName]["borrowers_{$ageGroup}_{$gender}"]++;
                \Log::info('Incremented borrowers counter', [
                    'area' => $areaName,
                    'counter' => "borrowers_{$ageGroup}_{$gender}",
                    'new_value' => $areaData[$areaName]["borrowers_{$ageGroup}_{$gender}"]
                ]);
                
                // Count loans and calculate outstanding amounts (include all loan statuses)
                $customerLoans = $customer->loans; // Get all loans regardless of status
                $areaData[$areaName]["loans_{$ageGroup}_{$gender}"] += $customerLoans->count();
                \Log::info('Incremented loans counter', [
                    'area' => $areaName,
                    'counter' => "loans_{$ageGroup}_{$gender}",
                    'loans_count' => $customerLoans->count(),
                    'new_value' => $areaData[$areaName]["loans_{$ageGroup}_{$gender}"]
                ]);
                
                // Calculate outstanding amount (principal + interest - payments)
                $outstandingAmount = $customerLoans->sum(function($loan) {
                    $totalAmount = $loan->amount_total ?? $loan->amount ?? 0;
                    $paidAmount = $loan->repayments->sum(function($repayment) {
                        return ($repayment->principal ?? 0) + ($repayment->interest ?? 0);
                    });
                    return max(0, $totalAmount - $paidAmount);
                });
                
                $areaData[$areaName]["outstanding_{$ageGroup}_{$gender}"] += $outstandingAmount;
                \Log::info('Added outstanding amount', [
                    'area' => $areaName,
                    'counter' => "outstanding_{$ageGroup}_{$gender}",
                    'outstanding_amount' => $outstandingAmount,
                    'new_value' => $areaData[$areaName]["outstanding_{$ageGroup}_{$gender}"]
                ]);
            } catch (\Exception $e) {
                // Log the error for debugging
                \Log::error('Error processing customer data', [
                    'customer_id' => $customer->id,
                    'error' => $e->getMessage()
                ]);
                // Skip this customer if there's an error processing their data
                continue;
            }
        }
        
        // Get branch and employee data
        $totalBranches = Branch::count();
        $totalEmployees = User::where('role', 'employee')->count();
        
        // Distribute branches and employees across areas based on customer density
        $totalCustomers = array_sum(array_column($areaData, 'borrowers_up35_female')) + 
                         array_sum(array_column($areaData, 'borrowers_up35_male')) + 
                         array_sum(array_column($areaData, 'borrowers_above35_female')) + 
                         array_sum(array_column($areaData, 'borrowers_above35_male'));
        
        foreach ($areaData as $areaName => &$data) {
            $areaCustomers = $data['borrowers_up35_female'] + $data['borrowers_up35_male'] + 
                           $data['borrowers_above35_female'] + $data['borrowers_above35_male'];
            
            if ($totalCustomers > 0) {
                // Distribute branches and employees proportionally to customer density
                $data['branches'] = $areaCustomers > 0 ? max(1, round(($areaCustomers / $totalCustomers) * $totalBranches)) : 0;
                $data['employees'] = $areaCustomers > 0 ? max(1, round(($areaCustomers / $totalCustomers) * $totalEmployees)) : 0;
            } else {
                $data['branches'] = $totalBranches > 0 ? 1 : 0;
                $data['employees'] = $totalEmployees > 0 ? 1 : 0;
            }
        }

        // Get compulsory savings data if available (adjust table name as needed)
        try {
            $savingsData = \DB::table('cash_collaterals')
                ->join('customers', 'cash_collaterals.customer_id', '=', 'customers.id')
                ->join('regions', 'customers.region_id', '=', 'regions.id')
                ->leftJoin('districts', 'customers.district_id', '=', 'districts.id')
                ->selectRaw('
                    COALESCE(districts.name, regions.name) as area_name,
                    SUM(cash_collaterals.amount) as total_savings
                ')
                ->groupBy('area_name')
                ->get()
                ->keyBy('area_name');
            
            // Add savings data to area data
            foreach ($areaData as $areaName => &$data) {
                $data['compulsory_savings'] = $savingsData->get($areaName)->total_savings ?? 0;
            }
        } catch (\Exception $e) {
            // If savings table doesn't exist, keep all values as 0
        }
        
        // Debug: Log the final area data
        \Log::info('Final Area Data', [
            'areas_with_data' => array_keys($areaData),
            'total_areas' => count($areaData),
            'sample_area_data' => !empty($areaData) ? array_values($areaData)[0] : 'No area data'
        ]);
        
        return view('reports.bot.geographical-distribution', compact('user', 'asOfDate', 'geographicalAreas', 'zanzibarAreas', 'areaData'));
    }

    public function export(Request $request): StreamedResponse
    {
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));
        $filename = 'BOT_Geographical_Distribution_' . $asOfDate . '.xls';
        $fullPath = base_path('resources/views/reports/bot-geographical-distribution.xls');
        
        if (!file_exists($fullPath)) {
            return response()->streamDownload(function () {
                echo 'Template not found';
            }, $filename);
        }
        
        return response()->streamDownload(function () use ($fullPath) {
            readfile($fullPath);
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel'
        ]);
    }
} 