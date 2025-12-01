<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ChartAccount;
use App\Models\AccountClassGroup;
use App\Models\GlTransaction;
use App\Models\BankReconciliation;
use App\Models\Journal;
use App\Models\Payment;
use App\Models\Penalty;
use App\Models\Receipt;
use App\Services\LoanPenaltyService;

class DashboardController extends Controller
{
    /**
     * Endpoint for monthly collections (expected, collected, arrears) for current year
     */
    public function monthlyCollections()
    {
        $year = now()->year;
        $months = [];
        $expected = [];
        $collected = [];
        $arrears = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthLabel = date('M', mktime(0, 0, 0, $m, 1));
            $months[] = $monthLabel;
            // Expected: sum of all schedules due in this month (no branch/company filter)
            $exp = \App\Models\LoanSchedule::whereYear('due_date', $year)
                ->whereMonth('due_date', $m)
                ->sum('principal');
            $exp += \App\Models\LoanSchedule::whereYear('due_date', $year)
                ->whereMonth('due_date', $m)
                ->sum('interest');
            $expected[] = $exp;
            // Collected: sum of repayments made for schedules due in this month (no branch/company filter)
            $repayments = \DB::table('repayments')
                ->join('loan_schedules', 'repayments.loan_schedule_id', '=', 'loan_schedules.id')
                ->whereYear('loan_schedules.due_date', $year)
                ->whereMonth('loan_schedules.due_date', $m)
                ->sum(\DB::raw('repayments.principal + repayments.interest'));
            $collected[] = $repayments;
            // Arrears: expected - collected
            $arrears[] = max(0, $exp - $repayments);
        }
        return response()->json([
            'months' => $months,
            'expected' => $expected,
            'collected' => $collected,
            'arrears' => $arrears
        ]);
    }
    /**
     * Endpoint for delinquency loan buckets (current year)
     */
    public function delinquencyLoanBuckets(Request $request)
    {
        $year = now()->year;
        $company = auth()->user()->company;
        $user = auth()->user();
        
        // Get branch filter from request
        $selectedBranchId = $request->get('branch_id');
        
        // Get user's assigned branches
        $userBranchIds = $user->branches()->where('company_id', $company->id)->pluck('branches.id')->toArray();
        
        // If no assigned branches, use all company branches
        if (empty($userBranchIds)) {
            $userBranchIds = \App\Models\Branch::where('company_id', $company->id)->pluck('id')->toArray();
        }
        
        // Define buckets (days overdue)
        $buckets = [
            '1-30 days' => [1, 30],
            '31-60 days' => [31, 60],
            '61-90 days' => [61, 90],
            '91-180 days' => [91, 180],
            '181-360 days' => [181, 360],
            '361+ days' => [361, 10000],
        ];
        $labels = [];
        $values = [];
        foreach ($buckets as $label => [$min, $max]) {
            $query = \App\Models\Loan::whereYear('disbursed_on', $year)
                ->whereHas('branch', function($q) use ($company) {
                    $q->where('company_id', $company->id);
                })
                ->where('status', 'active');
            
            // Apply branch filter
            if ($selectedBranchId) {
                $query->where('branch_id', $selectedBranchId);
            } else {
                // If no specific branch selected, filter by user's assigned branches
                if (!empty($userBranchIds)) {
                    $query->whereIn('branch_id', $userBranchIds);
                }
            }
            
            $count = $query->whereHas('schedule', function($q) use ($min, $max) {
                    $q->whereRaw('DATEDIFF(CURDATE(), due_date) BETWEEN ? AND ?', [$min, $max]);
                })
                ->count();
            $labels[] = $label;
            $values[] = $count;
        }
        return response()->json([
            'labels' => $labels,
            'values' => $values
        ]);
    }
    /**
     * Endpoint for loan product disbursement data (current year)
     */
    public function loanProductDisbursement(Request $request)
    {
        $year = now()->year;
        $company = auth()->user()->company;
        $user = auth()->user();
        
        // Get branch filter from request
        $selectedBranchId = $request->get('branch_id');
        
        // Get user's assigned branches
        $userBranchIds = $user->branches()->where('company_id', $company->id)->pluck('branches.id')->toArray();
        
        // If no assigned branches, use all company branches
        if (empty($userBranchIds)) {
            $userBranchIds = \App\Models\Branch::where('company_id', $company->id)->pluck('id')->toArray();
        }
        
        $products = \App\Models\LoanProduct::all();

        $productNames = [];
        $amounts = [];
        foreach ($products as $product) {
            $query = \App\Models\Loan::where('product_id', $product->id)
                ->whereYear('disbursed_on', $year)
                ->whereHas('branch', function($q) use ($company) {
                    $q->where('company_id', $company->id);
                });
            
            // Apply branch filter
            if ($selectedBranchId) {
                $query->where('branch_id', $selectedBranchId);
            } else {
                // If no specific branch selected, filter by user's assigned branches
                if (!empty($userBranchIds)) {
                    $query->whereIn('branch_id', $userBranchIds);
                }
            }
            
            $total = $query->sum('amount');
            $productNames[] = $product->name;
            $amounts[] = $total;
        }
        return response()->json([
            'products' => $productNames,
            'amounts' => $amounts
        ]);
    }
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            // Redirect to login or show an error
            return redirect()->route('login')->with('error', 'Please login to access the dashboard.');
        }
        $company = $user->company;
        
        // Get branch filter
        $selectedBranchId = $request->get('branch_id', $user->branch_id);
        
        // Get available branches for the filter - only user's assigned branches
        $branches = $user->branches()->where('company_id', $company->id)->get();
        
        // Get user's assigned branch IDs for filtering
        $userBranchIds = $branches->pluck('id')->toArray();
        
        // Get balance sheet data
        $balanceSheetData = $this->getBalanceSheetData($selectedBranchId, $userBranchIds);
        
        // Get comprehensive financial report data
        $financialReportData = $this->getFinancialReportData($selectedBranchId, $userBranchIds);
        
        // Get current month
        $currentMonth = now()->format('Y-m');

        // Get recent activities - filter by company through branch and current month
        $recentJournals = Journal::whereHas('branch', function($query) use ($company) {
            $query->where('company_id', $company->id);
        })->when($selectedBranchId, function($query) use ($selectedBranchId) {
            return $query->where('branch_id', $selectedBranchId);
        }, function($query) use ($userBranchIds) {
            return $query->whereIn('branch_id', $userBranchIds);
        })
        ->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$currentMonth])
        ->with(['user', 'branch'])
        ->latest()
        ->take(5)
        ->get();
        
        $recentPayments = Payment::whereHas('branch', function($query) use ($company) {
            $query->where('company_id', $company->id);
        })->when($selectedBranchId, function($query) use ($selectedBranchId) {
            return $query->where('branch_id', $selectedBranchId);
        }, function($query) use ($userBranchIds) {
            return $query->whereIn('branch_id', $userBranchIds);
        })
        ->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$currentMonth])
        ->with(['user', 'branch'])
        ->latest()
        ->take(5)
        ->get();
        
        $recentReceipts = Receipt::whereHas('branch', function($query) use ($company) {
            $query->where('company_id', $company->id);
        })->when($selectedBranchId, function($query) use ($selectedBranchId) {
            return $query->where('branch_id', $selectedBranchId);
        }, function($query) use ($userBranchIds) {
            return $query->whereIn('branch_id', $userBranchIds);
        })
        ->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$currentMonth])
        ->with(['user', 'branch', 'customer'])
        ->latest()
        ->take(5)
        ->get();
        
        $loans_status_stats = ['active', 'written_off', 'defaulted', 'completed','complete_topup'];
        // Loan statistics for Total Loan Amount (only active and completed)
        $loansForTotalAmount = \App\Models\Loan::whereHas('branch', function($query) use ($company) {
            $query->where('company_id', $company->id);
        })->when($selectedBranchId, function($query) use ($selectedBranchId) {
            return $query->where('branch_id', $selectedBranchId);
        }, function($query) use ($userBranchIds) {
            return $query->whereIn('branch_id', $userBranchIds);
        })->whereIn('status', ['active', 'completed'])->get();
        
        // All loans for other calculations
        $loans = \App\Models\Loan::whereHas('branch', function($query) use ($company) {
            $query->where('company_id', $company->id);
        })->when($selectedBranchId, function($query) use ($selectedBranchId) {
            return $query->where('branch_id', $selectedBranchId);
        }, function($query) use ($userBranchIds) {
            return $query->whereIn('branch_id', $userBranchIds);
        })->whereIn('status', $loans_status_stats)->get();
        
        // Loans for detailed interest calculations (same statuses as report)
        $loansForInterest = \App\Models\Loan::with(['customer', 'branch', 'loanOfficer', 'schedule.repayments'])
            ->whereHas('branch', function($query) use ($company) {
                $query->where('company_id', $company->id);
            })->when($selectedBranchId, function($query) use ($selectedBranchId) {
                return $query->where('branch_id', $selectedBranchId);
            }, function($query) use ($userBranchIds) {
                return $query->whereIn('branch_id', $userBranchIds);
            })->whereIn('status', ['active', 'written_off', 'defaulted'])->get();

        $totalLoanAmount = $loansForTotalAmount->sum('amount_total');
        $totalPrincipal = $loans->sum('amount');
        $totalInterest = $loans->sum('interest_amount');

        // Repaid principal and interest
        $repaidPrincipal = 0;
        $repaidInterest = 0;
        $outstandingPrincipal = 0;
        $outstandingInterest = 0;
        
        // Detailed interest breakdown
        $accruedInterest = 0;
        $notDueInterest = 0;
        $paidInterest = 0;
        $outstandingInterestDetailed = 0;
        
        $currentDate = \Carbon\Carbon::now();
        $currentMonth = $currentDate->format('Y-m');
        
        foreach ($loansForInterest as $loan) {
            $loanAccruedInterest = 0;
            $loanNotDueInterest = 0;
            $loanOutstandingInterest = 0;
            $loanPaidInterest = 0;
            
            if ($loan->schedule && $loan->schedule->count() > 0) {
                foreach ($loan->schedule as $schedule) {
                    $principalPaid = $schedule->repayments->sum('principal');
                    $interestPaid = $schedule->repayments->sum('interest');
                    $repaidPrincipal += $principalPaid;
                    $repaidInterest += $interestPaid;
                    $outstandingPrincipal += max(0, $schedule->principal - $principalPaid);
                    $outstandingInterest += max(0, $schedule->interest - $interestPaid);
                    
                    // Calculate detailed interest breakdown per schedule
                    $scheduleDate = \Carbon\Carbon::parse($schedule->due_date);
                    $scheduleMonth = $scheduleDate->format('Y-m');
                    $scheduleInterest = $schedule->interest ?? 0;
                    
                    if ($scheduleMonth <= $currentMonth) {
                        // Interest is due up to this month - what's not paid is outstanding
                        $loanOutstandingInterest += max(0, $scheduleInterest - $interestPaid);
                    } else {
                        // Interest is not yet due
                        $loanNotDueInterest += $scheduleInterest;
                    }
                    
                    $loanPaidInterest += $interestPaid;
                }
            } else {
                // Fallback to simple calculation if no schedule
                $loanOutstandingInterest = max(0, ($loan->interest_amount ?? 0) - $loanPaidInterest);
                $loanNotDueInterest = 0;
                $loanAccruedInterest = 0;
            }
            
            // Calculate accrued interest for this loan (interest earned but not yet due)
            $loanStartDate = \Carbon\Carbon::parse($loan->disbursed_on);
            $monthsElapsed = $loanStartDate->diffInMonths($currentDate);
            $totalLoanMonths = $loan->period ?? 1;
            
            if ($monthsElapsed > 0 && $monthsElapsed < $totalLoanMonths) {
                // Calculate proportional interest earned but not yet due for this loan
                $loanAccruedInterest = ($loanNotDueInterest * $monthsElapsed) / $totalLoanMonths;
            }
            
            // Add this loan's amounts to totals
            $accruedInterest += $loanAccruedInterest;
            $notDueInterest += $loanNotDueInterest;
            $outstandingInterestDetailed += $loanOutstandingInterest;
            $paidInterest += $loanPaidInterest;
        }

        $penaltyBalance = LoanPenaltyService::getTotalPenaltyBalance($selectedBranchId);
        info('penaltyBalance'.$penaltyBalance);

        // Get previous year comparative data
        $previousYearData = $this->getPreviousYearData($selectedBranchId, $userBranchIds);

        return view('dashboard', compact(
            'balanceSheetData',
            'financialReportData',
            'recentJournals',
            'recentPayments', 
            'recentReceipts',
            'penaltyBalance',
            'previousYearData',
            'totalLoanAmount',
            'totalPrincipal',
            'totalInterest',
            'repaidPrincipal',
            'repaidInterest',
            'outstandingPrincipal',
            'outstandingInterest',
            'accruedInterest',
            'notDueInterest',
            'paidInterest',
            'outstandingInterestDetailed',
            'branches',
            'selectedBranchId'
        ));
    }
    
    private function getBalanceSheetData($selectedBranchId = null, $userBranchIds = [])
    {
        $company = auth()->user()->company;
        
        // Get balance sheet data directly from gl_transactions
        $query = DB::table('gl_transactions')
            ->join('chart_accounts', 'gl_transactions.chart_account_id', '=', 'chart_accounts.id')
            ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
            ->join('account_class', 'account_class_groups.class_id', '=', 'account_class.id')
            ->where('account_class_groups.company_id', $company->id);
        
        // Apply branch filter
        if ($selectedBranchId) {
            $query->where('gl_transactions.branch_id', $selectedBranchId);
        } else {
            // If no specific branch selected, filter by user's assigned branches
            $query->whereIn('gl_transactions.branch_id', $userBranchIds);
        }
        
        $balanceSheetData = $query
            ->select(
                'account_class.name as class_name',
                'account_class_groups.group_code as class_code',
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "debit" THEN gl_transactions.amount ELSE 0 END) as total_debit'),
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "credit" THEN gl_transactions.amount ELSE 0 END) as total_credit'),
                DB::raw('COUNT(DISTINCT chart_accounts.id) as account_count')
            )
            ->groupBy('account_class.id', 'account_class.name', 'account_class_groups.group_code')
            ->get()
            ->map(function ($item) {
                // Calculate balance based on account class
                $balance = 0;
                switch (strtolower($item->class_name)) {
                    case 'assets':
                        $balance = $item->total_debit - $item->total_credit; // Assets: debit increases
                        break;
                    case 'liabilities':
                        $balance = $item->total_credit - $item->total_debit; // Liabilities: credit increases
                        break;
                    case 'equity':
                        $balance = $item->total_credit - $item->total_debit; // Equity: credit increases
                        break;
                    case 'income':
                    case 'revenue':
                        $balance = $item->total_credit - $item->total_debit; // Revenue: credit increases
                        break;
                    case 'expenses':
                    case 'expense':
                        $balance = $item->total_debit - $item->total_credit; // Expenses: debit increases
                        break;
                    default:
                        $balance = $item->total_debit - $item->total_credit;
                }
                
                return [
                    'class_name' => $item->class_name,
                    'class_code' => $item->class_code,
                    'balance' => $balance,
                    'account_count' => $item->account_count
                ];
            })
            ->sortByDesc(function ($item) {
                return abs($item['balance']);
            })
            ->values()
            ->toArray();
            
        return $balanceSheetData;
    }
    
    private function getFinancialReportData($selectedBranchId = null, $userBranchIds = [])
    {
        $company = auth()->user()->company;
        
        // Get all chart accounts with their balances grouped by account class
        $query = DB::table('gl_transactions')
            ->join('chart_accounts', 'gl_transactions.chart_account_id', '=', 'chart_accounts.id')
            ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
            ->join('account_class', 'account_class_groups.class_id', '=', 'account_class.id')
            ->where('account_class_groups.company_id', $company->id);
        
        // Apply branch filter
        if ($selectedBranchId) {
            $query->where('gl_transactions.branch_id', $selectedBranchId);
        } else {
            // If no specific branch selected, filter by user's assigned branches
            $query->whereIn('gl_transactions.branch_id', $userBranchIds);
        }
        
        $chartAccountsData = $query
            ->select(
                'chart_accounts.id as account_id',
                'chart_accounts.account_name as account',
                'account_class.name as class_name',
                'account_class_groups.name as group_name',
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "debit" THEN gl_transactions.amount ELSE 0 END) as debit_total'),
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "credit" THEN gl_transactions.amount ELSE 0 END) as credit_total')
            )
            ->groupBy('chart_accounts.id', 'chart_accounts.account_name', 'account_class.name', 'account_class_groups.name')
            ->get();
            
        // Group by account class and calculate balances
        $chartAccountsAssets = [];
        $chartAccountsLiabilities = [];
        $chartAccountsEquitys = [];
        $chartAccountsRevenues = [];
        $chartAccountsExpense = [];
        
        foreach ($chartAccountsData as $account) {
            // Calculate balance based on account class
            $balance = 0;
            
            // Categorize based on account class
            switch (strtolower($account->class_name)) {
                case 'assets':
                    $balance = $account->debit_total - $account->credit_total; // Assets: debit increases
                    $chartAccountsAssets[$account->group_name][] = [
                        'account_id' => $account->account_id,
                        'account' => $account->account,
                        'sum' => $balance
                    ];
                    break;
                case 'liabilities':
                    $balance = $account->credit_total - $account->debit_total; // Liabilities: credit increases
                    $chartAccountsLiabilities[$account->group_name][] = [
                        'account_id' => $account->account_id,
                        'account' => $account->account,
                        'sum' => $balance
                    ];
                    break;
                case 'equity':
                    $balance = $account->credit_total - $account->debit_total; // Equity: credit increases
                    $chartAccountsEquitys[$account->group_name][] = [
                        'account_id' => $account->account_id,
                        'account' => $account->account,
                        'sum' => $balance
                    ];
                    break;
                case 'income':
                case 'revenue':
                    $balance = $account->credit_total - $account->debit_total; // Revenue: credit increases
                    $chartAccountsRevenues[$account->group_name][] = [
                        'account_id' => $account->account_id,
                        'account' => $account->account,
                        'sum' => $balance
                    ];
                    break;
                case 'expenses':
                case 'expense':
                    $balance = $account->debit_total - $account->credit_total; // Expenses: debit increases
                    $chartAccountsExpense[$account->group_name][] = [
                        'account_id' => $account->account_id,
                        'account' => $account->account,
                        'sum' => $balance
                    ];
                    break;
            }
        }
        
        // Calculate profit/loss
        $sumRevenue = collect($chartAccountsRevenues)->flatten(1)->sum('sum');
        $sumExpense = collect($chartAccountsExpense)->flatten(1)->sum('sum');
        $profitLoss = $sumRevenue - $sumExpense;
        
        return [
            'chartAccountsAssets' => $chartAccountsAssets,
            'chartAccountsLiabilities' => $chartAccountsLiabilities,
            'chartAccountsEquitys' => $chartAccountsEquitys,
            'chartAccountsRevenues' => $chartAccountsRevenues,
            'chartAccountsExpense' => $chartAccountsExpense,
            'profitLoss' => $profitLoss
        ];
    }
    
    private function getPreviousYearData($selectedBranchId = null, $userBranchIds = [])
    {
        $company = auth()->user()->company;
        $currentYear = date('Y');
        $previousYear = $currentYear - 1;
        
        // Get previous year financial data by account
        $query = DB::table('gl_transactions')
            ->join('chart_accounts', 'gl_transactions.chart_account_id', '=', 'chart_accounts.id')
            ->join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
            ->join('account_class', 'account_class_groups.class_id', '=', 'account_class.id')
            ->where('account_class_groups.company_id', $company->id)
            ->whereYear('gl_transactions.date', $previousYear);
        
        // Apply branch filter
        if ($selectedBranchId) {
            $query->where('gl_transactions.branch_id', $selectedBranchId);
        } else {
            // If no specific branch selected, filter by user's assigned branches
            $query->whereIn('gl_transactions.branch_id', $userBranchIds);
        }
        
        $previousYearData = $query
            ->select(
                'chart_accounts.id as account_id',
                'chart_accounts.account_name as account',
                'account_class.name as class_name',
                'account_class_groups.name as group_name',
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "debit" THEN gl_transactions.amount ELSE 0 END) as debit_total'),
                DB::raw('SUM(CASE WHEN gl_transactions.nature = "credit" THEN gl_transactions.amount ELSE 0 END) as credit_total')
            )
            ->groupBy('chart_accounts.id', 'chart_accounts.account_name', 'account_class.name', 'account_class_groups.name')
            ->get();
            
        // Group by account class and calculate balances
        $previousYearAssets = [];
        $previousYearLiabilities = [];
        $previousYearEquitys = [];
        $previousYearRevenues = [];
        $previousYearExpense = [];
        
        foreach ($previousYearData as $account) {
            // Calculate balance based on account class
            $balance = 0;
            
            // Categorize based on account class
            switch (strtolower($account->class_name)) {
                case 'assets':
                    $balance = $account->debit_total - $account->credit_total; // Assets: debit increases
                    $previousYearAssets[$account->group_name][] = [
                        'account_id' => $account->account_id,
                        'account' => $account->account,
                        'sum' => $balance
                    ];
                    break;
                case 'liabilities':
                    $balance = $account->credit_total - $account->debit_total; // Liabilities: credit increases
                    $previousYearLiabilities[$account->group_name][] = [
                        'account_id' => $account->account_id,
                        'account' => $account->account,
                        'sum' => $balance
                    ];
                    break;
                case 'equity':
                    $balance = $account->credit_total - $account->debit_total; // Equity: credit increases
                    $previousYearEquitys[$account->group_name][] = [
                        'account_id' => $account->account_id,
                        'account' => $account->account,
                        'sum' => $balance
                    ];
                    break;
                case 'income':
                case 'revenue':
                    $balance = $account->credit_total - $account->debit_total; // Revenue: credit increases
                    $previousYearRevenues[$account->group_name][] = [
                        'account_id' => $account->account_id,
                        'account' => $account->account,
                        'sum' => $balance
                    ];
                    break;
                case 'expenses':
                case 'expense':
                    $balance = $account->debit_total - $account->credit_total; // Expenses: debit increases
                    $previousYearExpense[$account->group_name][] = [
                        'account_id' => $account->account_id,
                        'account' => $account->account,
                        'sum' => $balance
                    ];
                    break;
            }
        }
        
        // Calculate previous year profit/loss
        $sumRevenue = collect($previousYearRevenues)->flatten(1)->sum('sum');
        $sumExpense = collect($previousYearExpense)->flatten(1)->sum('sum');
        $previousYearProfitLoss = $sumRevenue - $sumExpense;
        
        return [
            'year' => $previousYear,
            'chartAccountsAssets' => $previousYearAssets,
            'chartAccountsLiabilities' => $previousYearLiabilities,
            'chartAccountsEquitys' => $previousYearEquitys,
            'chartAccountsRevenues' => $previousYearRevenues,
            'chartAccountsExpense' => $previousYearExpense,
            'profitLoss' => $previousYearProfitLoss
        ];
    }

    /**
     * Handle bulk SMS sending from dashboard
     */
    public function sendBulkSms(Request $request)
    {
        $request->validate([
            'branch_id' => 'required',
            'message_title' => 'required|string|max:100',
            'bulk_message_content' => 'required|string|max:500',
            'custom_title' => 'nullable|string|max:100',
        ]);

        $branchId = $request->branch_id;
        $title = $request->message_title;
        $customTitle = $request->custom_title;
        $messageContent = $request->bulk_message_content;

        // If 'Custom' is selected, use the custom title
        if ($title === 'Custom' && $customTitle) {
            $title = $customTitle;
        }

        // Get customers for the selected branch or all branches
        $customersQuery = \App\Models\Customer::query();
        if ($branchId !== 'all') {
            $customersQuery->where('branch_id', $branchId);
        }
        $customers = $customersQuery->whereNotNull('phone1')->get();

        $valid = 0;
        $invalid = 0;
        $duplicates = 0;
        $sentNumbers = [];
        $responses = [];

        foreach ($customers as $customer) {
            $phone = preg_replace('/[^0-9+]/', '', $customer->phone1);
            if (empty($phone) || in_array($phone, $sentNumbers)) {
                $invalid++;
                if (in_array($phone, $sentNumbers)) $duplicates++;
                continue;
            }
            $sentNumbers[] = $phone;
            $fullMessage = $title . ": " . $messageContent;
            //$smsResponse = \App\Helpers\SmsHelper::send($phone, $fullMessage);
            $responses[] = $smsResponse;
            $valid++;
            // Log SMS
            \DB::table('sms_logs')->insert([
                'customer_id' => $customer->id,
                'phone_number' => $phone,
                'message' => $fullMessage,
                'response' => $smsResponse,
                'sent_by' => auth()->id(),
                'sent_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "Bulk SMS sent successfully!",
            'response' => [
                'message' => 'Message Submitted Successfully',
                'valid' => $valid,
                'invalid' => $invalid,
                'duplicates' => $duplicates,
                'details' => $responses
            ]
        ]);
    }
}