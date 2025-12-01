<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\Loan;
use App\Models\Repayment;
use Carbon\Carbon;

class BotIncomeStatementController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));

        // Debug: Log the current data
        \Log::info('=== BOT INCOME STATEMENT REPORT DEBUG ===');
        
        // Calculate quarter start and end dates
        $quarterEnd = Carbon::parse($asOfDate)->endOfQuarter();
        $quarterStart = Carbon::parse($asOfDate)->startOfQuarter();
        $yearStart = Carbon::parse($asOfDate)->startOfYear();
        
        \Log::info('Date ranges:', [
            'as_of_date' => $asOfDate,
            'quarter_start' => $quarterStart->format('Y-m-d'),
            'quarter_end' => $quarterEnd->format('Y-m-d'),
            'year_start' => $yearStart->format('Y-m-d')
        ]);
        
        // Calculate interest income from loan repayments
        $quarterlyInterestIncome = Repayment::whereBetween('payment_date', [$quarterStart, $quarterEnd])
            ->sum('interest');
        $ytdInterestIncome = Repayment::whereBetween('payment_date', [$yearStart, $quarterEnd])
            ->sum('interest');
        
        // Interest income breakdown
        $interestLoansToClients = $quarterlyInterestIncome; // All interest is from client loans
        $interestLoansToMFSPs = 0; // Placeholder
        $interestGovtSecurities = 0; // Placeholder
        $interestBankDeposits = 0; // Placeholder
        $interestOthers = 0; // Placeholder
        
        $totalInterestIncome = $quarterlyInterestIncome;
        
        \Log::info('Interest Income Calculation:', [
            'quarterly_interest_income' => $quarterlyInterestIncome,
            'ytd_interest_income' => $ytdInterestIncome,
            'interest_loans_to_clients' => $interestLoansToClients,
            'total_interest_income' => $totalInterestIncome
        ]);
        
        // Interest expense (placeholders for now)
        $interestExpense = 0; // Would need borrowings table
        $netInterestIncome = $totalInterestIncome - $interestExpense;
        
        // Bad debts and provisions (placeholders for now)
        $badDebtsWrittenOff = 0; // Would need write-offs table
        $provisionForBadDebts = 0; // Would need provisions table
        
        // Non-interest income (placeholders for now)
        $nonInterestIncome = 0; // Would need other income tables
        
        // Non-interest expenses (placeholders for now)
        $nonInterestExpenses = 0; // Would need expense tables
        
        // Calculate net income
        $netIncomeBeforeTax = $netInterestIncome + $nonInterestIncome - $badDebtsWrittenOff - $provisionForBadDebts - $nonInterestExpenses;
        $incomeTax = 0; // Placeholder
        $netIncomeAfterTax = $netIncomeBeforeTax - $incomeTax;
        
        \Log::info('Income Statement Calculation:', [
            'interest_expense' => $interestExpense,
            'net_interest_income' => $netInterestIncome,
            'bad_debts_written_off' => $badDebtsWrittenOff,
            'provision_for_bad_debts' => $provisionForBadDebts,
            'non_interest_income' => $nonInterestIncome,
            'non_interest_expenses' => $nonInterestExpenses,
            'net_income_before_tax' => $netIncomeBeforeTax,
            'income_tax' => $incomeTax,
            'net_income_after_tax' => $netIncomeAfterTax
        ]);
        
        // Prepare data for view
        $data = [
            'interest_income' => $totalInterestIncome,
            'interest_expense' => $interestExpense,
            'net_interest_income' => $netInterestIncome,
            'bad_debts_written_off' => $badDebtsWrittenOff,
            'provision_for_bad_debts' => $provisionForBadDebts,
            'non_interest_income' => $nonInterestIncome,
            'non_interest_expenses' => $nonInterestExpenses,
            'net_income_before_tax' => $netIncomeBeforeTax,
            'income_tax' => $incomeTax,
            'net_income_after_tax' => $netIncomeAfterTax,
            // Detailed breakdown for view
            'interest_loans_to_clients' => $interestLoansToClients,
            'interest_loans_to_mfsps' => $interestLoansToMFSPs,
            'interest_govt_securities' => $interestGovtSecurities,
            'interest_bank_deposits' => $interestBankDeposits,
            'interest_others' => $interestOthers,
            'quarter_start' => $quarterStart,
            'quarter_end' => $quarterEnd,
            'quarterly_interest_income' => $quarterlyInterestIncome,
            'ytd_interest_income' => $ytdInterestIncome
        ];

        return view('reports.bot.income-statement', compact('user', 'asOfDate', 'data'));
    }

    public function export(Request $request): StreamedResponse
    {
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));
        $filename = 'BOT_Income_Statement_' . $asOfDate . '.xlsx';
        
        // Get the same data as the index method
        $quarterEnd = Carbon::parse($asOfDate)->endOfQuarter();
        $quarterStart = Carbon::parse($asOfDate)->startOfQuarter();
        $yearStart = Carbon::parse($asOfDate)->startOfYear();
        
        // Calculate interest income from loan repayments
        $quarterlyInterestIncome = Repayment::whereBetween('payment_date', [$quarterStart, $quarterEnd])
            ->sum('interest');
        $ytdInterestIncome = Repayment::whereBetween('payment_date', [$yearStart, $quarterEnd])
            ->sum('interest');
        
        // Interest income breakdown
        $interestLoansToClients = $quarterlyInterestIncome;
        $interestLoansToMFSPs = 0;
        $interestGovtSecurities = 0;
        $interestBankDeposits = 0;
        $interestOthers = 0;
        
        $totalInterestIncome = $quarterlyInterestIncome;
        
        // Interest expense (placeholders for now)
        $interestExpense = 0;
        $netInterestIncome = $totalInterestIncome - $interestExpense;
        
        // Bad debts and provisions (placeholders for now)
        $badDebtsWrittenOff = 0;
        $provisionForBadDebts = 0;
        
        // Non-interest income (placeholders for now)
        $nonInterestIncome = 0;
        
        // Non-interest expenses (placeholders for now)
        $nonInterestExpenses = 0;
        
        // Calculate net income
        $netIncomeBeforeTax = $netInterestIncome + $nonInterestIncome - $badDebtsWrittenOff - $provisionForBadDebts - $nonInterestExpenses;
        $incomeTax = 0;
        $netIncomeAfterTax = $netIncomeBeforeTax - $incomeTax;
        
        return response()->streamDownload(function () use ($quarterStart, $quarterEnd, $totalInterestIncome, $interestExpense, $netInterestIncome, $badDebtsWrittenOff, $provisionForBadDebts, $nonInterestIncome, $nonInterestExpenses, $netIncomeBeforeTax, $incomeTax, $netIncomeAfterTax, $interestLoansToClients, $interestLoansToMFSPs, $interestGovtSecurities, $interestBankDeposits, $interestOthers, $quarterlyInterestIncome, $ytdInterestIncome, $asOfDate) {
            $this->generateExcelContent($quarterStart, $quarterEnd, $totalInterestIncome, $interestExpense, $netInterestIncome, $badDebtsWrittenOff, $provisionForBadDebts, $nonInterestIncome, $nonInterestExpenses, $netIncomeBeforeTax, $incomeTax, $netIncomeAfterTax, $interestLoansToClients, $interestLoansToMFSPs, $interestGovtSecurities, $interestBankDeposits, $interestOthers, $quarterlyInterestIncome, $ytdInterestIncome, $asOfDate);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ]);
    }
    
    private function generateExcelContent($quarterStart, $quarterEnd, $totalInterestIncome, $interestExpense, $netInterestIncome, $badDebtsWrittenOff, $provisionForBadDebts, $nonInterestIncome, $nonInterestExpenses, $netIncomeBeforeTax, $incomeTax, $netIncomeAfterTax, $interestLoansToClients, $interestLoansToMFSPs, $interestGovtSecurities, $interestBankDeposits, $interestOthers, $quarterlyInterestIncome, $ytdInterestIncome, $asOfDate)
    {
        $output = fopen('php://output', 'w');
        
        // Header
        fputcsv($output, ['BOT STATEMENT OF INCOME AND EXPENSE']);
        fputcsv($output, ['']);
        fputcsv($output, ['BOT FORM MSP2-02: To be submitted Quarterly (Amount in TZS)']);
        fputcsv($output, ['FOR THE QUARTER ENDED: ' . \Carbon\Carbon::parse($asOfDate)->format('d/m/Y')]);
        fputcsv($output, ['']);
        
        // Main Income Statement Table
        fputcsv($output, ['Sno', 'Particular', 'Quarterly Amount', 'Year To Date Amount']);
        fputcsv($output, ['1.', 'INTEREST INCOME', number_format($totalInterestIncome, 2), number_format($ytdInterestIncome, 2)]);
        fputcsv($output, ['a.', 'Interest - Loans to Clients', number_format($interestLoansToClients, 2), number_format($interestLoansToClients, 2)]);
        fputcsv($output, ['b.', 'Interest - Loans to Microfinance Service Providers', number_format($interestLoansToMFSPs, 2), number_format($interestLoansToMFSPs, 2)]);
        fputcsv($output, ['c.', 'Interest - Investments in Govt Securities', number_format($interestGovtSecurities, 2), number_format($interestGovtSecurities, 2)]);
        fputcsv($output, ['d.', 'Interest - Bank Deposits', number_format($interestBankDeposits, 2), number_format($interestBankDeposits, 2)]);
        fputcsv($output, ['e.', 'Interest - Others', number_format($interestOthers, 2), number_format($interestOthers, 2)]);
        fputcsv($output, ['']);
        
        fputcsv($output, ['2.', 'INTEREST EXPENSE', number_format($interestExpense, 2), number_format($interestExpense, 2)]);
        fputcsv($output, ['a.', 'Interest - Borrowings from Banks & Financial Institutions in Tanzania', '0.00', '0.00']);
        fputcsv($output, ['b.', 'Interest - Borrowing from Microfinance Service Providers in Tanzania', '0.00', '0.00']);
        fputcsv($output, ['c.', 'Interest - Borrowings from Abroad', '0.00', '0.00']);
        fputcsv($output, ['d.', 'Interest - Borrowing from Shareholders', '0.00', '0.00']);
        fputcsv($output, ['e.', 'Interest - Others', '0.00', '0.00']);
        fputcsv($output, ['']);
        
        fputcsv($output, ['3.', 'NET INTEREST INCOME (1 less 2)', number_format($netInterestIncome, 2), number_format($netInterestIncome, 2)]);
        fputcsv($output, ['']);
        
        fputcsv($output, ['4.', 'BAD DEBTS WRITTEN OFF NOT PROVIDED FOR', number_format($badDebtsWrittenOff, 2), number_format($badDebtsWrittenOff, 2)]);
        fputcsv($output, ['5.', 'PROVISION FOR BAD AND DOUBTFUL DEBTS', number_format($provisionForBadDebts, 2), number_format($provisionForBadDebts, 2)]);
        fputcsv($output, ['']);
        
        fputcsv($output, ['6.', 'NON-INTEREST INCOME', number_format($nonInterestIncome, 2), number_format($nonInterestIncome, 2)]);
        fputcsv($output, ['a.', 'Commissions', '0.00', '0.00']);
        fputcsv($output, ['b.', 'Fees', '0.00', '0.00']);
        fputcsv($output, ['c.', 'Rental Income on Premises', '0.00', '0.00']);
        fputcsv($output, ['d.', 'Dividends on Equity Investment', '0.00', '0.00']);
        fputcsv($output, ['e.', 'Income from Recovery of Charged off Assets and Acquired Assets', '0.00', '0.00']);
        fputcsv($output, ['f.', 'Other Income', '0.00', '0.00']);
        fputcsv($output, ['']);
        
        fputcsv($output, ['7.', 'NON-INTEREST EXPENSES', number_format($nonInterestExpenses, 2), number_format($nonInterestExpenses, 2)]);
        fputcsv($output, ['a.', 'Managements\' Salaries and Benefits', '0.00', '0.00']);
        fputcsv($output, ['b.', 'Employees\' Salaries and Benefits', '0.00', '0.00']);
        fputcsv($output, ['c.', 'Wages', '0.00', '0.00']);
        fputcsv($output, ['d.', 'Pensions Contributions', '0.00', '0.00']);
        fputcsv($output, ['e.', 'Skills and Development Levy', '0.00', '0.00']);
        fputcsv($output, ['f.', 'Rental Expense on Premises and Equipment', '0.00', '0.00']);
        fputcsv($output, ['g.', 'Depreciation - Premises and Equipment', '0.00', '0.00']);
        fputcsv($output, ['h.', 'Amortization - Leasehold Rights and Equipments', '0.00', '0.00']);
        fputcsv($output, ['i.', 'Foreclosure and Litigation Expenses', '0.00', '0.00']);
        fputcsv($output, ['j.', 'Management Fees', '0.00', '0.00']);
        fputcsv($output, ['k.', 'Auditors Fees', '0.00', '0.00']);
        fputcsv($output, ['l.', 'Taxes', '0.00', '0.00']);
        fputcsv($output, ['m.', 'License Fees', '0.00', '0.00']);
        fputcsv($output, ['n.', 'Insurance', '0.00', '0.00']);
        fputcsv($output, ['o.', 'Utilities Expenses', '0.00', '0.00']);
        fputcsv($output, ['p.', 'Other Non-Interest Expenses', '0.00', '0.00']);
        fputcsv($output, ['']);
        
        fputcsv($output, ['8.', 'NET INCOME / (LOSS) BEFORE INCOME TAX (3+6 Less 4,5 and 7)', number_format($netIncomeBeforeTax, 2), number_format($netIncomeBeforeTax, 2)]);
        fputcsv($output, ['9.', 'INTEREST TAX / PROVISION', number_format($incomeTax, 2), number_format($incomeTax, 2)]);
        fputcsv($output, ['10.', 'NET INCOME / (LOSS) AFTER INCOME TAX (8 less 9)', number_format($netIncomeAfterTax, 2), number_format($netIncomeAfterTax, 2)]);
        fputcsv($output, ['']);
        
        // Summary
        fputcsv($output, ['SUMMARY']);
        fputcsv($output, ['Item', 'Quarterly Amount', 'Year To Date Amount']);
        fputcsv($output, ['Total Interest Income', number_format($totalInterestIncome, 2), number_format($ytdInterestIncome, 2)]);
        fputcsv($output, ['Total Interest Expense', number_format($interestExpense, 2), number_format($interestExpense, 2)]);
        fputcsv($output, ['Net Interest Income', number_format($netInterestIncome, 2), number_format($netInterestIncome, 2)]);
        fputcsv($output, ['Net Income Before Tax', number_format($netIncomeBeforeTax, 2), number_format($netIncomeBeforeTax, 2)]);
        fputcsv($output, ['Net Income After Tax', number_format($netIncomeAfterTax, 2), number_format($netIncomeAfterTax, 2)]);
        fputcsv($output, ['']);
        
        // Period Information
        fputcsv($output, ['PERIOD INFORMATION']);
        fputcsv($output, ['Quarter Start', $quarterStart->format('d/m/Y')]);
        fputcsv($output, ['Quarter End', $quarterEnd->format('d/m/Y')]);
        fputcsv($output, ['Report Date', \Carbon\Carbon::parse($asOfDate)->format('d/m/Y')]);
        
        fclose($output);
    }
} 