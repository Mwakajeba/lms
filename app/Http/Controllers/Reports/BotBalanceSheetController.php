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

class BotBalanceSheetController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));

        // Debug: Log the current data
        \Log::info('=== BOT BALANCE SHEET REPORT DEBUG ===');
        
        // Get bank account balances from GL transactions
        $bankAccounts = BankAccount::all();
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
        
        // Get total loans outstanding with breakdown
        $totalLoansOutstanding = Loan::where('status', 'active')->sum('amount_total');
        
        // Get loans breakdown by type (you can customize this based on your loan structure)
        $loansToClients = Loan::where('status', 'active')->sum('amount_total'); // All active loans are to clients
        $loansToStaff = 0; // Placeholder - would need staff loan identification
        $loansToMFSPs = 0; // Placeholder - would need MFSP loan identification
        $accruedInterest = 0; // Placeholder - would need interest calculation
        $allowanceForLosses = 0; // Placeholder - would need loss calculation
        
        \Log::info('Loans Outstanding:', [
            'total_amount' => $totalLoansOutstanding,
            'loans_to_clients' => $loansToClients,
            'loans_to_staff' => $loansToStaff,
            'loans_to_mfsps' => $loansToMFSPs,
            'accrued_interest' => $accruedInterest,
            'allowance_for_losses' => $allowanceForLosses
        ]);
        
        // Calculate balance sheet components
        $cashAndCashEquivalents = $totalBankBalance + $totalCashCollateral;
        $loansNet = $totalLoansOutstanding - $allowanceForLosses; // Net of allowance
        $propertyPlantEquipmentNet = 0; // Placeholder - would need PPE table
        $otherAssets = 0; // Placeholder - would need other assets tables
        
        $totalAssets = $cashAndCashEquivalents + $loansNet + $propertyPlantEquipmentNet + $otherAssets;
        
        // Liabilities (placeholders for now)
        $borrowings = 0; // Would need borrowings table
        $taxPayables = 0; // Would need tax payables table
        $dividendPayables = 0; // Would need dividend table
        $otherPayables = 0; // Would need other payables table
        
        $totalLiabilities = $borrowings + $totalCashCollateral + $taxPayables + $dividendPayables + $otherPayables;
        
        // Capital (placeholders for now)
        $paidUpCapital = 0; // Would need capital table
        $retainedEarnings = 0; // Would need earnings table
        $profitLoss = 0; // Would need P&L table
        
        $totalCapital = $paidUpCapital + $retainedEarnings + $profitLoss;
        $totalLiabilitiesAndCapital = $totalLiabilities + $totalCapital;
        
        \Log::info('Balance Sheet Calculation:', [
            'cash_and_cash_equivalents' => $cashAndCashEquivalents,
            'loans_net' => $loansNet,
            'total_assets' => $totalAssets,
            'total_liabilities' => $totalLiabilities,
            'total_capital' => $totalCapital,
            'total_liabilities_and_capital' => $totalLiabilitiesAndCapital
        ]);
        
        // Prepare data for view
        $data = [
            'cash_and_cash_equivalents' => $cashAndCashEquivalents,
            'loans_net' => $loansNet,
            'loans_to_clients' => $loansToClients,
            'loans_to_staff' => $loansToStaff,
            'loans_to_mfsps' => $loansToMFSPs,
            'accrued_interest' => $accruedInterest,
            'allowance_for_losses' => $allowanceForLosses,
            'property_plant_equipment_net' => $propertyPlantEquipmentNet,
            'other_assets' => $otherAssets,
            'total_assets' => $totalAssets,
            'borrowings' => $borrowings,
            'cash_collateral' => $totalCashCollateral,
            'tax_payables' => $taxPayables,
            'dividend_payables' => $dividendPayables,
            'other_payables' => $otherPayables,
            'total_liabilities' => $totalLiabilities,
            'paid_up_capital' => $paidUpCapital,
            'retained_earnings' => $retainedEarnings,
            'profit_loss' => $profitLoss,
            'total_liabilities_and_capital' => $totalLiabilitiesAndCapital,
            'bank_accounts' => $bankAccountsWithBalance,
            'total_bank_balance' => $totalBankBalance
        ];

        return view('reports.bot.balance-sheet', compact('user', 'asOfDate', 'data'));
    }

    public function export(Request $request): StreamedResponse
    {
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));
        $filename = 'BOT_Balance_Sheet_' . $asOfDate . '.xlsx';
        
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
        
        // Get loans breakdown by type
        $loansToClients = Loan::where('status', 'active')->sum('amount_total');
        $loansToStaff = 0;
        $loansToMFSPs = 0;
        $accruedInterest = 0;
        $allowanceForLosses = 0;
        
        // Calculate balance sheet components
        $cashAndCashEquivalents = $totalBankBalance + $totalCashCollateral;
        $loansNet = $totalLoansOutstanding - $allowanceForLosses;
        $propertyPlantEquipmentNet = 0;
        $otherAssets = 0;
        
        $totalAssets = $cashAndCashEquivalents + $loansNet + $propertyPlantEquipmentNet + $otherAssets;
        
        // Liabilities
        $borrowings = 0;
        $taxPayables = 0;
        $dividendPayables = 0;
        $otherPayables = 0;
        
        $totalLiabilities = $borrowings + $totalCashCollateral + $taxPayables + $dividendPayables + $otherPayables;
        
        // Capital
        $paidUpCapital = 0;
        $retainedEarnings = 0;
        $profitLoss = 0;
        
        $totalCapital = $paidUpCapital + $retainedEarnings + $profitLoss;
        $totalLiabilitiesAndCapital = $totalLiabilities + $totalCapital;
        
        return response()->streamDownload(function () use ($bankAccountsWithBalance, $totalBankBalance, $totalCashCollateral, $totalLoansOutstanding, $loansToClients, $loansToStaff, $loansToMFSPs, $accruedInterest, $allowanceForLosses, $cashAndCashEquivalents, $loansNet, $propertyPlantEquipmentNet, $otherAssets, $totalAssets, $borrowings, $taxPayables, $dividendPayables, $otherPayables, $totalLiabilities, $paidUpCapital, $retainedEarnings, $profitLoss, $totalCapital, $totalLiabilitiesAndCapital, $asOfDate) {
            $this->generateExcelContent($bankAccountsWithBalance, $totalBankBalance, $totalCashCollateral, $totalLoansOutstanding, $loansToClients, $loansToStaff, $loansToMFSPs, $accruedInterest, $allowanceForLosses, $cashAndCashEquivalents, $loansNet, $propertyPlantEquipmentNet, $otherAssets, $totalAssets, $borrowings, $taxPayables, $dividendPayables, $otherPayables, $totalLiabilities, $paidUpCapital, $retainedEarnings, $profitLoss, $totalCapital, $totalLiabilitiesAndCapital, $asOfDate);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ]);
    }
    
    private function generateExcelContent($bankAccounts, $totalBankBalance, $totalCashCollateral, $totalLoansOutstanding, $loansToClients, $loansToStaff, $loansToMFSPs, $accruedInterest, $allowanceForLosses, $cashAndCashEquivalents, $loansNet, $propertyPlantEquipmentNet, $otherAssets, $totalAssets, $borrowings, $taxPayables, $dividendPayables, $otherPayables, $totalLiabilities, $paidUpCapital, $retainedEarnings, $profitLoss, $totalCapital, $totalLiabilitiesAndCapital, $asOfDate)
    {
        $output = fopen('php://output', 'w');
        
        // Header
        fputcsv($output, ['BOT BALANCE SHEET']);
        fputcsv($output, ['']);
        fputcsv($output, ['BOT FORM MSP2-01: To be submitted Quarterly (Amount in TZS)']);
        fputcsv($output, ['AS AT: ' . \Carbon\Carbon::parse($asOfDate)->format('d/m/Y')]);
        fputcsv($output, ['']);
        
        // Main Balance Sheet Table
        fputcsv($output, ['Sno', 'Particulars', 'Amount (TZS)', 'Validation']);
        fputcsv($output, ['1.', 'CASH AND CASH EQUIVALENTS (sum a:d)', number_format($cashAndCashEquivalents, 2), '']);
        fputcsv($output, ['(a)', 'Cash in Hand', '0.00', '']);
        fputcsv($output, ['(b)', 'Balances with Banks and Financial Institutions (sum i:iii)', number_format($totalBankBalance, 2), '']);
        fputcsv($output, ['', '(i) Non-Agent Banking Balances', number_format($totalBankBalance, 2), '']);
        fputcsv($output, ['', '(ii) Agent-Banking Balances', '0.00', '']);
        fputcsv($output, ['', '(iii) Balances with Microfinance Service Providers', '0.00', '']);
        fputcsv($output, ['(d)', 'MNOs Float Balances', '0.00', '']);
        fputcsv($output, ['']);
        
        fputcsv($output, ['2.', 'INVESTMENT IN DEBT SECURITIES - NET (sum a:d minus e)', '0.00', '']);
        fputcsv($output, ['(a)', 'Treasury Bills', '0.00', '']);
        fputcsv($output, ['(b)', 'Other Government Securities', '0.00', '']);
        fputcsv($output, ['(c)', 'Private Securities', '0.00', '']);
        fputcsv($output, ['(d)', 'Others', '0.00', '']);
        fputcsv($output, ['(e)', 'Allowance for Probable Losses (Deduction)', '0.00', '']);
        fputcsv($output, ['']);
        
        fputcsv($output, ['3.', 'EQUITY INVESTMENTS - NET (a - b)', '0.00', '']);
        fputcsv($output, ['(a)', 'Equity Investment', '0.00', '']);
        fputcsv($output, ['(b)', 'Allowance for Probable Losses (Deduction)', '0.00', '']);
        fputcsv($output, ['']);
        
        fputcsv($output, ['4.', 'LOANS - NET (sum a:d less e)', number_format($loansNet, 2), '']);
        fputcsv($output, ['(a)', 'Loans to Clients', number_format($loansToClients, 2), '']);
        fputcsv($output, ['(b)', 'Loan to Staff and Related Parties', number_format($loansToStaff, 2), '']);
        fputcsv($output, ['(c)', 'Loans to other Microfinance Service Providers', number_format($loansToMFSPs, 2), '']);
        fputcsv($output, ['(d)', 'Accrued Interest on Loans', number_format($accruedInterest, 2), '']);
        fputcsv($output, ['(e)', 'Allowance for Probable Losses (Deduction)', number_format($allowanceForLosses, 2), '']);
        fputcsv($output, ['']);
        
        fputcsv($output, ['5.', 'PROPERTY, PLANT AND EQUIPMENT - NET (a - b)', number_format($propertyPlantEquipmentNet, 2), '']);
        fputcsv($output, ['(a)', 'Property, Plant and Equipment', '0.00', '']);
        fputcsv($output, ['(b)', 'Accumulated Depreciation (Deduction)', '0.00', '']);
        fputcsv($output, ['']);
        
        fputcsv($output, ['6.', 'OTHER ASSETS (sum a:e less f)', number_format($otherAssets, 2), '']);
        fputcsv($output, ['(a)', 'Receivables', '0.00', '']);
        fputcsv($output, ['(b)', 'Prepaid Expenses', '0.00', '']);
        fputcsv($output, ['(c)', 'Deferred Tax Assets', '0.00', '']);
        fputcsv($output, ['(d)', 'Intangible Assets', '0.00', '']);
        fputcsv($output, ['(e)', 'Miscellaneous Assets', '0.00', '']);
        fputcsv($output, ['(f)', 'Allowance for Probable Losses (Deduction)', '0.00', '']);
        fputcsv($output, ['']);
        
        fputcsv($output, ['7.', 'TOTAL ASSETS', number_format($totalAssets, 2), 'C33==C61']);
        fputcsv($output, ['']);
        
        fputcsv($output, ['8.', 'LIABILITIES', '', '']);
        fputcsv($output, ['9.', 'BORROWINGS (sum a:b)', number_format($borrowings, 2), '']);
        fputcsv($output, ['(a)', 'Borrowings in Tanzania (sum i:v)', number_format($borrowings, 2), '']);
        fputcsv($output, ['(a)', '(i) Borrowings from Banks and Financial Institutions', '0.00', '']);
        fputcsv($output, ['(a)', '(ii) Borrowings from Other Microfinance Service Providers', '0.00', '']);
        fputcsv($output, ['(a)', '(iii) Borrowing from Shareholders', '0.00', '']);
        fputcsv($output, ['(a)', '(iv) Borrowing from Public through Debt Securities', '0.00', '']);
        fputcsv($output, ['(a)', '(v) Other Borrowings', '0.00', '']);
        fputcsv($output, ['(b)', 'Borrowings from Abroad (sum i:iii)', '0.00', '']);
        fputcsv($output, ['(b)', '(i) Borrowings from Banks and Financial Institutions', '0.00', '']);
        fputcsv($output, ['(b)', '(ii) Borrowing from Shareholders', '0.00', '']);
        fputcsv($output, ['(b)', '(iii) Other Borrowings', '0.00', '']);
        fputcsv($output, ['']);
        
        fputcsv($output, ['10.', 'CASH COLLATERAL/LOAN INSURANCE GUARANTEES/COMPULSORY SAVINGS', number_format($totalCashCollateral, 2), '']);
        fputcsv($output, ['11.', 'TAX PAYABLES', number_format($taxPayables, 2), '']);
        fputcsv($output, ['12.', 'DIVIDEND PAYABLES', number_format($dividendPayables, 2), '']);
        fputcsv($output, ['13.', 'OTHER PAYABLES AND ACCRUALS', number_format($otherPayables, 2), '']);
        fputcsv($output, ['']);
        
        fputcsv($output, ['14.', 'TOTAL LIABILITIES (sum 9:13)', number_format($totalLiabilities, 2), '']);
        fputcsv($output, ['']);
        
        fputcsv($output, ['15.', 'TOTAL CAPITAL (sum a:i)', '', '']);
        fputcsv($output, ['(a)', 'Paid-up Ordinary Share Capital', number_format($paidUpCapital, 2), '']);
        fputcsv($output, ['(b)', 'Paid-up Preference Shares', '0.00', '']);
        fputcsv($output, ['(c)', 'Capital Grants', '0.00', '']);
        fputcsv($output, ['(d)', 'Donation', '0.00', '']);
        fputcsv($output, ['(e)', 'Share Premium', '0.00', '']);
        fputcsv($output, ['(f)', 'General Reserves', '0.00', '']);
        fputcsv($output, ['(g)', 'Retained Earnings', number_format($retainedEarnings, 2), '']);
        fputcsv($output, ['(i)', 'Profit/Loss', number_format($profitLoss, 2), '']);
        fputcsv($output, ['(j)', 'Other Reserves', '0.00', '']);
        fputcsv($output, ['']);
        
        fputcsv($output, ['16.', 'TOTAL LIABILITIES AND CAPITAL (14+15)', number_format($totalLiabilitiesAndCapital, 2), '']);
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
        fputcsv($output, ['Cash and Cash Equivalents', number_format($cashAndCashEquivalents, 2)]);
        fputcsv($output, ['Total Assets', number_format($totalAssets, 2)]);
        fputcsv($output, ['Total Liabilities', number_format($totalLiabilities, 2)]);
        fputcsv($output, ['Total Capital', number_format($totalCapital, 2)]);
        fputcsv($output, ['Total Liabilities and Capital', number_format($totalLiabilitiesAndCapital, 2)]);
        
        fclose($output);
    }
} 