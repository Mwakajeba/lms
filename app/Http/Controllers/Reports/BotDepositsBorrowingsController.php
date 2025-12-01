<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\BankAccount;
use App\Models\CashCollateral;
use Carbon\Carbon;

class BotDepositsBorrowingsController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));

        // Debug: Log the current data
        \Log::info('=== BOT DEPOSITS & BORROWINGS REPORT DEBUG ===');
        
        // Calculate quarter start and end dates
        $quarterEnd = Carbon::parse($asOfDate)->endOfQuarter();
        $quarterStart = Carbon::parse($asOfDate)->startOfQuarter();
        
        \Log::info('Date ranges:', [
            'as_of_date' => $asOfDate,
            'quarter_start' => $quarterStart->format('Y-m-d'),
            'quarter_end' => $quarterEnd->format('Y-m-d')
        ]);

        $banksTz = [
            'CRDB BANK PLC',
            'NMB BANK PLC',
            'NATIONAL BANK OF COMMERCE (NBC) LIMITED',
            'ABSA BANK TANZANIA LIMITED',
            'STANDARD CHARTERED BANK TANZANIA LIMITED',
            'STANBIC BANK TANZANIA LIMITED',
            'EXIM BANK (TANZANIA) LIMITED',
            'DIAMOND TRUST BANK TANZANIA LIMITED (DTB)',
            'I&M BANK (T) LIMITED',
            'KCB BANK TANZANIA LIMITED',
            'EQUITY BANK TANZANIA LIMITED',
            'AZANIA BANK LIMITED',
            'TPB BANK PLC',
            'TIB CORPORATE BANK LIMITED',
            'UNITED BANK FOR AFRICA (UBA) TANZANIA LIMITED',
            'BANK OF BARODA (TANZANIA) LIMITED',
            'BANK OF INDIA (TANZANIA) LIMITED',
            'PEOPLE\'S BANK OF ZANZIBAR (PBZ) PLC',
            'DCB COMMERCIAL BANK PLC',
            'MKOMBOZI COMMERCIAL BANK PLC',
            'AMANA BANK LIMITED',
            'DIB BANK TANZANIA PLC',
            'ACCESS BANK TANZANIA LIMITED',
            'CITIBANK TANZANIA LIMITED'
        ];

        // MFSPs - placeholder for now (would need MFSP table for real data)
        $mfsp = ['SELF MICROFINANCE'];
        
        // MNOs - placeholder for now (would need MNO table for real data)
        $mnos = ['AIRTEL MONEY','TIGO PESA','HALOPESA','M-PESA','TTCL PESA'];
        
        // Get bank accounts and their balances from GL transactions
        $bankAccounts = BankAccount::all();
        $bankAccountsWithBalance = $bankAccounts->map(function($account) use ($asOfDate) {
            $debitTotal = \DB::table('gl_transactions')
                ->where('chart_account_id', $account->chart_account_id)
                ->whereDate('date', '<=', $asOfDate)
                ->where('nature', 'debit')
                ->sum('amount');
            $creditTotal = \DB::table('gl_transactions')
                ->where('chart_account_id', $account->chart_account_id)
                ->whereDate('date', '<=', $asOfDate)
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
        
        // Log bank name matching for debugging
        \Log::info('Bank Name Matching Debug:', [
            'bot_bank_names' => $banksTz,
            'database_bank_names' => $bankAccountsWithBalance->pluck('name')->toArray()
        ]);
        
        // Get cash collateral balance (this represents deposits from customers)
        $totalCashCollateral = CashCollateral::sum('amount');
        
        \Log::info('Cash Collateral found:', [
            'total_amount' => $totalCashCollateral
        ]);
        
        // Calculate totals for the report
        $totalDepositsTz = $totalBankBalance; // Show actual balance (positive or negative)
        $totalDepositsForeign = 0; // Placeholder - would need foreign currency data
        $totalDeposits = $totalDepositsTz + $totalDepositsForeign;
        
        $totalBorrowingsTz = 0; // Placeholder - would need borrowings table
        $totalBorrowingsForeign = 0; // Placeholder - would need foreign currency data
        $totalBorrowings = $totalBorrowingsTz + $totalBorrowingsForeign;
        
        \Log::info('Report Totals:', [
            'total_deposits_tz' => $totalDepositsTz,
            'total_deposits_foreign' => $totalDepositsForeign,
            'total_deposits' => $totalDeposits,
            'total_borrowings_tz' => $totalBorrowingsTz,
            'total_borrowings_foreign' => $totalBorrowingsForeign,
            'total_borrowings' => $totalBorrowings
        ]);
        
        // Prepare data for view
        $data = [
            'bank_accounts' => $bankAccountsWithBalance,
            'total_bank_balance' => $totalBankBalance,
            'total_cash_collateral' => $totalCashCollateral,
            'total_deposits_tz' => $totalDepositsTz,
            'total_deposits_foreign' => $totalDepositsForeign,
            'total_deposits' => $totalDeposits,
            'total_borrowings_tz' => $totalBorrowingsTz,
            'total_borrowings_foreign' => $totalBorrowingsForeign,
            'total_borrowings' => $totalBorrowings,
            'quarter_start' => $quarterStart,
            'quarter_end' => $quarterEnd
        ];

        // Get company information for the report header
        $company = $user->company;
        
        return view('reports.bot.deposits-borrowings', compact('user', 'asOfDate', 'banksTz', 'mfsp', 'mnos', 'data', 'company'));
    }
    
    /**
     * Find matching bank account balance using improved LIKE logic
     */
    private function findBankBalance($botBankName, $bankAccounts)
    {
        \Log::info("=== BANK MATCHING DEBUG ===");
        \Log::info("Looking for BOT bank: '{$botBankName}'");
        \Log::info("Available database accounts:", $bankAccounts->pluck('name')->toArray());
        
        foreach ($bankAccounts as $account) {
            \Log::info("Checking account: '{$account['name']}'");
            
            // Extract key words from BOT bank name (e.g., "CRDB BANK PLC" -> "CRDB")
            $botKeyWords = explode(' ', strtoupper($botBankName));
            $accountKeyWords = explode(' ', strtoupper($account['name']));
            
            \Log::info("BOT keywords: " . implode(', ', $botKeyWords));
            \Log::info("Account keywords: " . implode(', ', $accountKeyWords));
            
            // Check if any key words match
            foreach ($botKeyWords as $botWord) {
                if (strlen($botWord) > 2) { // Only check words longer than 2 characters
                    \Log::info("Checking BOT word: '{$botWord}'");
                    foreach ($accountKeyWords as $accountWord) {
                        if (strlen($accountWord) > 2) {
                            \Log::info("  Against account word: '{$accountWord}'");
                            if (stripos($accountWord, $botWord) !== false || 
                                stripos($botWord, $accountWord) !== false) {
                                \Log::info("  ✓ MATCH FOUND: '{$botWord}' matches '{$accountWord}'");
                                \Log::info("Bank match found: '{$botBankName}' matches '{$account['name']}' via keyword '{$botWord}'");
                                return $account['balance'];
                            } else {
                                \Log::info("  ✗ No match: '{$botWord}' vs '{$accountWord}'");
                            }
                        }
                    }
                } else {
                    \Log::info("Skipping short BOT word: '{$botWord}' (length: " . strlen($botWord) . ")");
                }
            }
        }
        
        \Log::info("❌ No bank match found for: '{$botBankName}'");
        
        // Try fallback matching with more flexible approach
        \Log::info("Trying fallback matching...");
        foreach ($bankAccounts as $account) {
            // Try simple contains matching
            if (stripos($account['name'], 'CRDB') !== false && stripos($botBankName, 'CRDB') !== false) {
                \Log::info("Fallback match found: '{$botBankName}' contains 'CRDB' matches '{$account['name']}'");
                return $account['balance'];
            }
            if (stripos($account['name'], 'NMB') !== false && stripos($botBankName, 'NMB') !== false) {
                \Log::info("Fallback match found: '{$botBankName}' contains 'NMB' matches '{$account['name']}'");
                return $account['balance'];
            }
            if (stripos($account['name'], 'NBC') !== false && stripos($botBankName, 'NBC') !== false) {
                \Log::info("Fallback match found: '{$botBankName}' contains 'NBC' matches '{$account['name']}'");
                return $account['balance'];
            }
        }
        
        return 0;
    }

    public function export(Request $request): StreamedResponse
    {
        $user = Auth::user();
        $company = $user->company;
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));
        $filename = 'BOT_Deposits_Borrowings_' . $asOfDate . '.xlsx';
        
        // Get the same data as the index method
        $quarterEnd = Carbon::parse($asOfDate)->endOfQuarter();
        $quarterStart = Carbon::parse($asOfDate)->startOfQuarter();
        
        // Get bank accounts and their balances
        $bankAccounts = BankAccount::all();
        $bankAccountsWithBalance = $bankAccounts->map(function($account) use ($asOfDate) {
            $debitTotal = \DB::table('gl_transactions')
                ->where('chart_account_id', $account->chart_account_id)
                ->whereDate('date', '<=', $asOfDate)
                ->where('nature', 'debit')
                ->sum('amount');
            $creditTotal = \DB::table('gl_transactions')
                ->where('chart_account_id', $account->chart_account_id)
                ->whereDate('date', '<=', $asOfDate)
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
        
        // Calculate totals
        $totalDepositsTz = $totalBankBalance; // Show actual balance (positive or negative)
        $totalDepositsForeign = 0;
        $totalDeposits = $totalDepositsTz + $totalDepositsForeign;
        
        $totalBorrowingsTz = 0;
        $totalBorrowingsForeign = 0;
        $totalBorrowings = $totalBorrowingsTz + $totalBorrowingsForeign;
        
        // Banks in Tanzania
        $banksTz = $bankAccountsWithBalance->map(function($account) {
            return $account['name'];
        })->toArray();
        
        if (empty($banksTz)) {
            $banksTz = [
                'CRDB BANK PLC', 'NMB BANK PLC', 'NATIONAL BANK OF COMMERCE (NBC) LIMITED',
                'ABSA BANK TANZANIA LIMITED', 'STANDARD CHARTERED BANK TANZANIA LIMITED',
                'STANBIC BANK TANZANIA LIMITED', 'EXIM BANK (TANZANIA) LIMITED',
                'DIAMOND TRUST BANK TANZANIA LIMITED (DTB)', 'I&M BANK (T) LIMITED',
                'KCB BANK TANZANIA LIMITED', 'EQUITY BANK TANZANIA LIMITED', 'AZANIA BANK LIMITED',
                'TPB BANK PLC', 'TIB CORPORATE BANK LIMITED', 'UNITED BANK FOR AFRICA (UBA) TANZANIA LIMITED',
                'BANK OF BARODA (TANZANIA) LIMITED', 'BANK OF INDIA (TANZANIA) LIMITED',
                'PEOPLE\'S BANK OF ZANZIBAR (PBZ) PLC', 'DCB COMMERCIAL BANK PLC',
                'MKOMBOZI COMMERCIAL BANK PLC', 'AMANA BANK LIMITED', 'DIB BANK TANZANIA LIMITED',
                'ACCESS BANK TANZANIA LIMITED', 'CITIBANK TANZANIA LIMITED'
            ];
        }
        
        $mfsp = ['SELF MICROFINANCE'];
        $mnos = ['AIRTEL MONEY','TIGO PESA','HALOPESA','M-PESA','TTCL PESA'];
        
        return response()->streamDownload(function () use ($banksTz, $mfsp, $mnos, $totalDepositsTz, $totalDepositsForeign, $totalDeposits, $totalBorrowingsTz, $totalBorrowingsForeign, $totalBorrowings, $asOfDate, $bankAccountsWithBalance, $company) {
            $this->generateExcelContent($banksTz, $mfsp, $mnos, $totalDepositsTz, $totalDepositsForeign, $totalDeposits, $totalBorrowingsTz, $totalBorrowingsForeign, $totalBorrowings, $asOfDate, $bankAccountsWithBalance, $company);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ]);
    }
    
    private function generateExcelContent($banksTz, $mfsp, $mnos, $totalDepositsTz, $totalDepositsForeign, $totalDeposits, $totalBorrowingsTz, $totalBorrowingsForeign, $totalBorrowings, $asOfDate, $bankAccountsWithBalance, $company)
    {
        $output = fopen('php://output', 'w');
        
        // Header
        fputcsv($output, ['BOT DEPOSITS AND BORROWINGS FROM BANKS AND FINANCIAL INSTITUTIONS FOR THE QUARTER ENDED: ' . \Carbon\Carbon::parse($asOfDate)->format('d/m/Y')]);
        fputcsv($output, ['']);
        fputcsv($output, ['NAME OF INSTITUTION: ' . ($company->name ?? 'Company Name Not Set')]);
        fputcsv($output, ['MSP CODE: ' . ($company->msp_code ?? 'MSP Code Not Set')]);
        fputcsv($output, ['']);
        fputcsv($output, ['BOT FORM MSP2-07: To be submitted Quarterly (Amount in TZS)']);
        fputcsv($output, ['']);
        
        // Main Table Header
        fputcsv($output, ['Sno', 'Name of Bank or Financial Institution', 'Deposit Amounts', '', '', 'Borrowed Amount', '', '', 'Validation']);
        fputcsv($output, ['', '', 'TZS', 'Foreign Currency Equivalent in TZS', 'Total Deposits (c+d)', 'TZS', 'Foreign Currency Equivalent in TZS', 'Total Loan (f+g)', '']);
        
        // Banks in Tanzania
        fputcsv($output, ['', 'BANKS IN TANZANIA']);
        
        foreach ($banksTz as $i => $name) {
            $balance = $this->findBankBalance($name, $bankAccountsWithBalance);
            
            $depositTz = $balance > 0 ? $balance : 0;
            $depositForeign = 0;
            $totalDeposit = $depositTz + $depositForeign;
            $borrowingTz = 0;
            $borrowingForeign = 0;
            $totalBorrowing = $borrowingTz + $borrowingForeign;
            
            fputcsv($output, [$i + 1, $name, number_format($depositTz, 2), number_format($depositForeign, 2), number_format($totalDeposit, 2), number_format($borrowingTz, 2), number_format($borrowingForeign, 2), number_format($totalBorrowing, 2), '']);
        }
        
        fputcsv($output, ['', 'TOTAL IN BANKS TANZANIA', number_format($totalDepositsTz, 2), number_format($totalDepositsForeign, 2), number_format($totalDeposits, 2), number_format($totalBorrowingsTz, 2), number_format($totalBorrowingsForeign, 2), number_format($totalBorrowings, 2), 'H30=MSP2_01C37']);
        fputcsv($output, ['']);
        
        // Microfinance Service Providers
        fputcsv($output, ['', 'MICROFINANCE SERVICE PROVIDERS']);
        
        foreach ($mfsp as $j => $name) {
            fputcsv($output, [31 + $j, $name, '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '']);
        }
        
        fputcsv($output, ['', 'TOTAL IN MICROFINANCE SERVICE PROVIDERS', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', 'E46=MSP2_01C6, H46=MSP_01C38']);
        fputcsv($output, ['']);
        
        // Balances with MNOs
        fputcsv($output, ['', 'BALANCES WITH MNOs']);
        
        foreach ($mnos as $k => $name) {
            fputcsv($output, [47 + $k, $name, '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '']);
        }
        
        fputcsv($output, ['', 'TOTAL BALANCES WITH MNOs', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', 'E55=MSP2_01C7']);
        fputcsv($output, ['']);
        
        // Total Balances in Tanzania
        fputcsv($output, ['', 'TOTAL BALANCES IN TANZANIA', number_format($totalDepositsTz, 2), number_format($totalDepositsForeign, 2), number_format($totalDeposits, 2), number_format($totalBorrowingsTz, 2), number_format($totalBorrowingsForeign, 2), number_format($totalBorrowings, 2), 'E57=MSP2_01C3; MSP2_01C6+MSP2_01C7']);
        fputcsv($output, ['']);
        
        // Banks Abroad
        fputcsv($output, ['', 'BANKS ABROAD']);
        
        for ($r = 0; $r < 5; $r++) {
            fputcsv($output, [58 + $r, '', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '']);
        }
        
        fputcsv($output, ['', 'TOTAL IN BANKS ABROAD', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', 'H65=MSP2_01C43']);
        fputcsv($output, ['', 'TOTAL IN BANKS', number_format($totalDepositsTz, 2), number_format($totalDepositsForeign, 2), number_format($totalDeposits, 2), number_format($totalBorrowingsTz, 2), number_format($totalBorrowingsForeign, 2), number_format($totalBorrowings, 2), 'E66=MSP2_01C3']);
        fputcsv($output, ['']);
        
        // Summary
        fputcsv($output, ['SUMMARY']);
        fputcsv($output, ['Category', 'Total Deposits', 'Total Borrowings']);
        fputcsv($output, ['Banks in Tanzania', number_format($totalDeposits, 2), number_format($totalBorrowings, 2)]);
        fputcsv($output, ['MFSPs', '0.00', '0.00']);
        fputcsv($output, ['MNOs', '0.00', '0.00']);
        fputcsv($output, ['Banks Abroad', '0.00', '0.00']);
        fputcsv($output, ['GRAND TOTAL', number_format($totalDeposits, 2), number_format($totalBorrowings, 2)]);
        
        fclose($output);
    }
} 