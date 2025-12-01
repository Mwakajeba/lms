<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\Loan;
use App\Models\Customer;
use Carbon\Carbon;

class BotLoansDisbursedController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));
        
        // Parse the as of date to determine the quarter
        $date = Carbon::parse($asOfDate);
        $quarter = ceil($date->month / 3);
        $quarterStart = $date->copy()->startOfQuarter();
        $quarterEnd = $date->copy()->endOfQuarter();
        
        // Debug: Log the quarter information
        \Log::info('Quarter Information', [
            'as_of_date' => $asOfDate,
            'quarter' => $quarter,
            'quarter_start' => $quarterStart->format('Y-m-d'),
            'quarter_end' => $quarterEnd->format('Y-m-d')
        ]);

        // Get all sectors from the loans table
        $sectors = Loan::distinct()
            ->whereNotNull('sector')
            ->where('sector', '!=', '')
            ->pluck('sector')
            ->toArray();
        
        // If no sectors found, use default sectors
        if (empty($sectors)) {
            $sectors = [
                'Agriculture',
                'Mining and Quarrying',
                'Manufacturing',
                'Electricity, Gas and Water',
                'Construction',
                'Wholesale and Retail Trade',
                'Hotels and Restaurants',
                'Transport and Communication',
                'Financial Intermediation',
                'Real Estate, Renting and Business Activities',
                'Public Administration and Defence',
                'Education',
                'Health and Social Work',
                'Other Community, Social and Personal Service Activities',
                'Private Households with Employed Persons',
                'Extra-Territorial Organizations and Bodies',
                'Fishing',
                'Forestry',
                'Water',
                'Tourism',
                'Trade',
                'Other Services',
                'Personal (Private)'
            ];
        }
        
        \Log::info('Sectors found in database', [
            'sectors' => $sectors,
            'count' => count($sectors)
        ]);

        // Initialize sector data
        $sectorData = [];
        foreach ($sectors as $sector) {
            $sectorData[$sector] = [
                'female_number' => 0,
                'female_amount' => 0,
                'male_number' => 0,
                'male_amount' => 0
            ];
        }

        // Get loans disbursed in the quarter
        $quarterLoans = Loan::with(['customer'])
            ->whereNotNull('disbursed_on')
            ->whereDate('disbursed_on', '>=', $quarterStart)
            ->whereDate('disbursed_on', '<=', $quarterEnd)
            ->where('status', 'active')
            ->get();
        
        \Log::info('Quarter Loans Query', [
            'quarter_start' => $quarterStart->format('Y-m-d'),
            'quarter_end' => $quarterEnd->format('Y-m-d'),
            'total_loans' => $quarterLoans->count(),
            'sample_loans' => $quarterLoans->take(3)->map(function($loan) {
                return [
                    'id' => $loan->id,
                    'amount' => $loan->amount,
                    'amount_total' => $loan->amount_total,
                    'sector' => $loan->sector,
                    'disbursed_on' => $loan->disbursed_on,
                    'customer_sex' => $loan->customer->sex ?? 'Unknown'
                ];
            })->toArray()
        ]);

        // Process each loan
        foreach ($quarterLoans as $loan) {
            $sector = $loan->sector ?? 'Other Services';
            $gender = $loan->customer->sex ?? 'Unknown';
            $amount = $loan->amount_total ?? $loan->amount ?? 0;
            
            // Map gender values
            if ($gender === 'M') {
                $gender = 'male';
            } elseif ($gender === 'F') {
                $gender = 'female';
            } else {
                // Skip if gender is not valid
                continue;
            }
            
            // Ensure sector exists in our data
            if (!isset($sectorData[$sector])) {
                $sectorData[$sector] = [
                    'female_number' => 0,
                    'female_amount' => 0,
                    'male_number' => 0,
                    'male_amount' => 0
                ];
            }
            
            // Increment counters
            $sectorData[$sector]["{$gender}_number"]++;
            $sectorData[$sector]["{$gender}_amount"] += $amount;
            
            \Log::info('Processing loan', [
                'loan_id' => $loan->id,
                'sector' => $sector,
                'gender' => $gender,
                'amount' => $amount,
                'customer_sex' => $loan->customer->sex
            ]);
        }
        
        // Calculate totals
        $totalData = [
            'female_number' => array_sum(array_column($sectorData, 'female_number')),
            'female_amount' => array_sum(array_column($sectorData, 'female_amount')),
            'male_number' => array_sum(array_column($sectorData, 'male_number')),
            'male_amount' => array_sum(array_column($sectorData, 'male_amount'))
        ];
        
        \Log::info('Final Sector Data', [
            'sectors_with_data' => array_keys(array_filter($sectorData, function($data) {
                return $data['female_number'] > 0 || $data['male_number'] > 0;
            })),
            'total_data' => $totalData
        ]);

        // Get company information for the report header
        $company = $user->company;
        
        return view('reports.bot.loans-disbursed', compact(
            'user', 
            'asOfDate', 
            'sectors', 
            'sectorData', 
            'totalData',
            'quarter',
            'quarterStart',
            'quarterEnd',
            'company'
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        $user = Auth::user();
        $company = $user->company;
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));
        $filename = 'BOT_Loans_Disbursed_' . $asOfDate . '.xls';
        $fullPath = base_path('resources/views/reports/bot-loans-disbursed.xls');
        
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