<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\BankAccount;
use App\Models\CashCollateral;
use App\Models\Loan;
use Carbon\Carbon;

class BotLiquidAssetsController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));

        // Debug: Log the current data
        \Log::info('=== BOT LIQUID ASSETS REPORT DEBUG ===');
        
        // Get bank account balances from GL transactions
        $bankAccounts = BankAccount::all();
        $totalBankBalance = 0;
        
        // Calculate balance for each bank account from GL transactions
        $bankAccountsWithBalance = $bankAccounts->map(function($account) {
            $debitTotal = \DB::table('gl_transactions')
                ->where('chart_account_id', $account->chart_account_id)
                ->where('nature', 'debit')
                ->sum('amount');
            $creditTotal = \DB::table('gl_transactions')
                ->where('chart_account_id', $account->chart_account_id)
                ->where('nature', 'credit')
                ->sum('amount');
            $balance = $debitTotal - $creditTotal;
            
            return [
                'id' => $account->id,
                'name' => $account->name,
                'balance' => $balance,
                'account_number' => $account->account_number,
                'chart_account_id' => $account->chart_account_id
            ];
        });
        
        $totalBankBalance = $bankAccountsWithBalance->sum('balance');
        
        \Log::info('Bank Accounts found:', [
            'count' => $bankAccounts->count(),
            'total_balance' => $totalBankBalance,
            'accounts' => $bankAccountsWithBalance->toArray()
        ]);
        
        // Get cash collateral balance
        $totalCashCollateral = CashCollateral::sum('amount');
        
        \Log::info('Cash Collateral found:', [
            'total_amount' => $totalCashCollateral
        ]);
        
        // Get total loans outstanding
        $totalLoansOutstanding = Loan::where('status', 'active')->sum('amount_total');
        
        \Log::info('Loans Outstanding:', [
            'total_amount' => $totalLoansOutstanding
        ]);
        
        // Calculate liquid assets
        $totalAvailableLiquidAssets = $totalBankBalance + $totalCashCollateral;
        $totalAssets = $totalAvailableLiquidAssets + $totalLoansOutstanding;
        
        // BOT requirement: Minimum 20% of total assets must be liquid
        $requiredMinimumLiquidAssets = $totalAssets * 0.20;
        $excessDeficiency = $totalAvailableLiquidAssets - $requiredMinimumLiquidAssets;
        $liquidAssetRatio = $totalAssets > 0 ? ($totalAvailableLiquidAssets / $totalAssets) * 100 : 0;
        
        \Log::info('Liquid Assets Calculation:', [
            'total_available_liquid_assets' => $totalAvailableLiquidAssets,
            'total_assets' => $totalAssets,
            'required_minimum' => $requiredMinimumLiquidAssets,
            'excess_deficiency' => $excessDeficiency,
            'liquid_asset_ratio' => $liquidAssetRatio
        ]);
        
        // Prepare data for view
        $liquidAssetsData = [
            'total_available_liquid_assets' => $totalAvailableLiquidAssets,
            'total_assets' => $totalAssets,
            'required_minimum_liquid_assets' => $requiredMinimumLiquidAssets,
            'excess_deficiency' => $excessDeficiency,
            'liquid_asset_ratio' => $liquidAssetRatio,
            'bank_accounts' => $bankAccountsWithBalance,
            'cash_collateral' => $totalCashCollateral,
            'loans_outstanding' => $totalLoansOutstanding
        ];

        return view('reports.bot.liquid-assets', compact('user', 'asOfDate', 'liquidAssetsData'));
    }

    public function export(Request $request): StreamedResponse
    {
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));
        $filename = 'BOT_Liquid_Assets_' . $asOfDate . '.xlsx';
        
        // Get the same data as the index method
        $bankAccounts = BankAccount::all();
        
        // Calculate balance for each bank account from GL transactions
        $bankAccountsWithBalance = $bankAccounts->map(function($account) {
            $debitTotal = \DB::table('gl_transactions')
                ->where('chart_account_id', $account->chart_account_id)
                ->where('nature', 'debit')
                ->sum('amount');
            $creditTotal = \DB::table('gl_transactions')
                ->where('chart_account_id', $account->chart_account_id)
                ->where('nature', 'credit')
                ->sum('amount');
            $balance = $debitTotal - $creditTotal;
            
            return [
                'id' => $account->id,
                'name' => $account->name,
                'balance' => $balance,
                'account_number' => $account->account_number,
                'chart_account_id' => $account->chart_account_id
            ];
        });
        
        $totalBankBalance = $bankAccountsWithBalance->sum('balance');
        $totalCashCollateral = CashCollateral::sum('amount');
        $totalLoansOutstanding = Loan::where('status', 'active')->sum('amount_total');
        
        $totalAvailableLiquidAssets = $totalBankBalance + $totalCashCollateral;
        $totalAssets = $totalAvailableLiquidAssets + $totalLoansOutstanding;
        $requiredMinimumLiquidAssets = $totalAssets * 0.20;
        $excessDeficiency = $totalAvailableLiquidAssets - $requiredMinimumLiquidAssets;
        $liquidAssetRatio = $totalAssets > 0 ? ($totalAvailableLiquidAssets / $totalAssets) * 100 : 0;
        
        return response()->streamDownload(function () use ($bankAccountsWithBalance, $totalBankBalance, $totalCashCollateral, $totalLoansOutstanding, $totalAvailableLiquidAssets, $totalAssets, $requiredMinimumLiquidAssets, $excessDeficiency, $liquidAssetRatio, $asOfDate) {
            $this->generateExcelContent($bankAccountsWithBalance, $totalBankBalance, $totalCashCollateral, $totalLoansOutstanding, $totalAvailableLiquidAssets, $totalAssets, $requiredMinimumLiquidAssets, $excessDeficiency, $liquidAssetRatio, $asOfDate);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ]);
    }
    
    private function generateExcelContent($bankAccounts, $totalBankBalance, $totalCashCollateral, $totalLoansOutstanding, $totalAvailableLiquidAssets, $totalAssets, $requiredMinimumLiquidAssets, $excessDeficiency, $liquidAssetRatio, $asOfDate)
    {
        $output = fopen('php://output', 'w');
        
        // Header
        fputcsv($output, ['BOT COMPUTATION OF LIQUID ASSETS']);
        fputcsv($output, ['']);
        fputcsv($output, ['BOT FORM: MSP2-05 To be submitted Quarterly (Amount in TZS)']);
        fputcsv($output, ['AS AT: ' . \Carbon\Carbon::parse($asOfDate)->format('d/m/Y')]);
        fputcsv($output, ['']);
        
        // Main Report Table
        fputcsv($output, ['Sno', 'REQUIRED MINIMUM AMOUNT OF LIQUID ASSETS', 'AMOUNT (TZS)', 'VALIDATION']);
        fputcsv($output, ['A:', 'TOTAL AVAILABLE LIQUID ASSETS', number_format($totalAvailableLiquidAssets, 2), '']);
        fputcsv($output, ['(a)', 'Cash in hand', number_format(0, 2), '']);
        fputcsv($output, ['(b)', 'Balances with Banks and Financial Institutions', number_format($totalBankBalance, 2), 'C3=MSP2_01C3']);
        fputcsv($output, ['(c)', 'Cash Collateral', number_format($totalCashCollateral, 2), '']);
        fputcsv($output, ['(d)', 'MNOs Float Cash Balances', number_format(0, 2), '']);
        fputcsv($output, ['(e)', 'Treasury Bills (Unencumbered)', number_format(0, 2), '']);
        fputcsv($output, ['(f)', 'Other Government Securities with Residual Maturity of One Year or Less (Unencumbered)', number_format(0, 2), '']);
        fputcsv($output, ['(g)', 'Private Securities with Residual Maturity of One Year or Less (Unencumbered)', number_format(0, 2), '']);
        fputcsv($output, ['(h)', 'Other Liquid Assets Maturing within 12 Months', number_format(0, 2), '']);
        fputcsv($output, ['']);
        fputcsv($output, ['B.', 'TOTAL ASSETS', number_format($totalAssets, 2), '']);
        fputcsv($output, ['C.', 'Required Minimum Liquid Assets (20%*B)', number_format($requiredMinimumLiquidAssets, 2), '']);
        fputcsv($output, ['D.', 'Excess (Deficiency) Liquid Assets (A-C)', number_format($excessDeficiency, 2), '']);
        fputcsv($output, ['E.', 'Liquid Asset Ratio (A / B)', number_format($liquidAssetRatio, 2) . '%', '']);
        fputcsv($output, ['']);
        
        // Bank Account Details
        fputcsv($output, ['BANK ACCOUNT DETAILS']);
        fputcsv($output, ['Account Name', 'Account Number', 'Balance (TZS)', 'Chart Account ID']);
        foreach ($bankAccounts as $account) {
            fputcsv($output, [
                $account['name'],
                $account['account_number'],
                number_format($account['balance'], 2),
                $account['chart_account_id']
            ]);
        }
        fputcsv($output, ['']);
        
        // Summary
        fputcsv($output, ['SUMMARY']);
        fputcsv($output, ['Item', 'Amount (TZS)']);
        fputcsv($output, ['Total Bank Balance', number_format($totalBankBalance, 2)]);
        fputcsv($output, ['Total Cash Collateral', number_format($totalCashCollateral, 2)]);
        fputcsv($output, ['Total Loans Outstanding', number_format($totalLoansOutstanding, 2)]);
        fputcsv($output, ['Total Available Liquid Assets', number_format($totalAvailableLiquidAssets, 2)]);
        fputcsv($output, ['Total Assets', number_format($totalAssets, 2)]);
        fputcsv($output, ['Required Minimum (20%)', number_format($requiredMinimumLiquidAssets, 2)]);
        fputcsv($output, ['Excess/Deficiency', number_format($excessDeficiency, 2)]);
        fputcsv($output, ['Liquid Asset Ratio', number_format($liquidAssetRatio, 2) . '%']);
        
        fclose($output);
    }
} 