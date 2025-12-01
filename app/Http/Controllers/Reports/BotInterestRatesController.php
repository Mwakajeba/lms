<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\Loan;
use App\Models\LoanProduct;
use Carbon\Carbon;

class BotInterestRatesController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));

        // Debug: Log the current data
        \Log::info('=== BOT INTEREST RATES REPORT DEBUG ===');
        
        // Get all loan products with their interest methods
        $loanProducts = LoanProduct::all();
        \Log::info('Loan Products found:', [
            'count' => $loanProducts->count(),
            'products' => $loanProducts->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'interest_method' => $product->interest_method,
                    'min_rate' => $product->minimum_interest_rate,
                    'max_rate' => $product->maximum_interest_rate
                ];
            })->toArray()
        ]);

        // Get all active loans with their products
        $activeLoans = Loan::with(['product', 'customer'])
            ->where('status', 'active')
            ->get();
        
        \Log::info('Active Loans found:', [
            'count' => $activeLoans->count(),
            'loans' => $activeLoans->map(function($loan) {
                return [
                    'id' => $loan->id,
                    'amount' => $loan->amount,
                    'amount_total' => $loan->amount_total,
                    'product_name' => $loan->product->name ?? 'No product',
                    'interest_method' => $loan->product->interest_method ?? 'No method',
                    'min_rate' => $loan->product->minimum_interest_rate ?? 0,
                    'max_rate' => $loan->product->maximum_interest_rate ?? 0
                ];
            })->toArray()
        ]);

        // Categorize loans by interest method
        $interestMethodData = [];
        
        foreach ($activeLoans as $loan) {
            $interestMethod = $loan->product->interest_method ?? 'Unknown';
            $amount = $loan->amount_total ?? $loan->amount ?? 0;
            $minRate = $loan->product->minimum_interest_rate ?? 0;
            $maxRate = $loan->product->maximum_interest_rate ?? 0;
            
            if (!isset($interestMethodData[$interestMethod])) {
                $interestMethodData[$interestMethod] = [
                    'outstanding_amount' => 0,
                    'loan_count' => 0,
                    'min_rates' => [],
                    'max_rates' => [],
                    'total_principal' => 0
                ];
            }
            
            $interestMethodData[$interestMethod]['outstanding_amount'] += $amount;
            $interestMethodData[$interestMethod]['loan_count']++;
            $interestMethodData[$interestMethod]['min_rates'][] = $minRate;
            $interestMethodData[$interestMethod]['max_rates'][] = $maxRate;
            $interestMethodData[$interestMethod]['total_principal'] += $loan->amount ?? 0;
        }
        
        \Log::info('Interest Method Data:', [
            'methods' => array_keys($interestMethodData),
            'data' => $interestMethodData
        ]);

        // Generate report rows based on interest methods
        $rows = [];
        
        foreach ($interestMethodData as $method => $data) {
            // Calculate weighted average interest rate
            $waRate = 0;
            if ($data['total_principal'] > 0) {
                $waRate = array_sum($data['min_rates']) / count($data['min_rates']);
            }
            
            // Get nominal rates (lowest and highest)
            $nomLow = !empty($data['min_rates']) ? min($data['min_rates']) : 0;
            $nomHigh = !empty($data['max_rates']) ? max($data['max_rates']) : 0;
            
            // Determine if this is straight line or reducing balance
            $isStraightLine = strpos(strtolower($method), 'straight') !== false;
            $isReducingBalance = strpos(strtolower($method), 'reducing') !== false;
            
            if ($isStraightLine) {
                $rows[] = [
                    'outstanding' => $data['outstanding_amount'],
                    'wa_straight' => $waRate,
                    'nom_straight_low' => $nomLow,
                    'nom_straight_high' => $nomHigh,
                    'wa_reducing' => 0,
                    'nom_reducing_low' => 0,
                    'nom_reducing_high' => 0,
                    'method' => $method,
                    'loan_count' => $data['loan_count']
                ];
            } elseif ($isReducingBalance) {
                $rows[] = [
                    'outstanding' => $data['outstanding_amount'],
                    'wa_straight' => 0,
                    'nom_straight_low' => 0,
                    'nom_straight_high' => 0,
                    'wa_reducing' => $waRate,
                    'nom_reducing_low' => $nomLow,
                    'nom_reducing_high' => $nomHigh,
                    'method' => $method,
                    'loan_count' => $data['loan_count']
                ];
            } else {
                // For other methods, categorize as reducing balance (default)
                $rows[] = [
                    'outstanding' => $data['outstanding_amount'],
                    'wa_straight' => 0,
                    'nom_straight_low' => 0,
                    'nom_straight_high' => 0,
                    'wa_reducing' => $waRate,
                    'nom_reducing_low' => $nomLow,
                    'nom_reducing_high' => $nomHigh,
                    'method' => $method,
                    'loan_count' => $data['loan_count']
                ];
            }
        }
        
        // If no rows generated, create a default row
        if (empty($rows)) {
            $rows[] = [
                'outstanding' => 0,
                'wa_straight' => 0,
                'nom_straight_low' => 0,
                'nom_straight_high' => 0,
                'wa_reducing' => 0,
                'nom_reducing_low' => 0,
                'nom_reducing_high' => 0,
                'method' => 'No loans found',
                'loan_count' => 0
            ];
        }
        
        \Log::info('Final Report Rows:', [
            'rows_count' => count($rows),
            'rows' => $rows
        ]);

        return view('reports.bot.interest-rates', compact('user', 'asOfDate', 'rows', 'interestMethodData'));
    }

    public function export(Request $request): StreamedResponse
    {
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));
        $filename = 'BOT_Interest_Rates_' . $asOfDate . '.xls';
        $fullPath = base_path('resources/views/reports/bot-interest-rates.xls');
        
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