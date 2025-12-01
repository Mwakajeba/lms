<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function index()
    {
        try {
            $user = Auth::user();
            $branchId = $user->branch_id;
            
            // Get current month and year
            $currentMonth = request('month', now()->month);
            $currentYear = request('year', now()->year);
            
            \Log::info('Calendar request', [
                'month' => $currentMonth,
                'year' => $currentYear,
                'branch_id' => $branchId
            ]);
        
        // Get loan schedules for the month
        try {
            $loanSchedules = DB::table('loan_schedules')
                ->join('loans', 'loan_schedules.loan_id', '=', 'loans.id')
                ->join('customers', 'loan_schedules.customer_id', '=', 'customers.id')
                ->where('loans.branch_id', $branchId)
                ->whereMonth('loan_schedules.due_date', $currentMonth)
                ->whereYear('loan_schedules.due_date', $currentYear)
                ->select(
                    'loan_schedules.due_date',
                    'customers.name as customer_name',
                    'loans.id as loan_id',
                    DB::raw('(loan_schedules.principal + loan_schedules.interest + loan_schedules.fee_amount + loan_schedules.penalty_amount) as amount_due'),
                    'loans.status as loan_status'
                )
                ->get()
                ->groupBy('due_date');
            
            \Log::info('Loan schedules loaded', ['count' => $loanSchedules->flatten()->count()]);
        } catch (\Exception $e) {
            \Log::error('Error loading loan schedules: ' . $e->getMessage());
            $loanSchedules = collect();
        }
        
        // Get repayments for the month
        try {
            $repayments = DB::table('repayments')
                ->join('loans', 'repayments.loan_id', '=', 'loans.id')
                ->join('customers', 'repayments.customer_id', '=', 'customers.id')
                ->where('loans.branch_id', $branchId)
                ->whereMonth('repayments.payment_date', $currentMonth)
                ->whereYear('repayments.payment_date', $currentYear)
                ->select(
                    'repayments.payment_date',
                    'customers.name as customer_name',
                    'loans.id as loan_id',
                    DB::raw('(repayments.principal + repayments.interest) as amount_paid')
                )
                ->get()
                ->groupBy('payment_date');
            
            \Log::info('Repayments loaded', ['count' => $repayments->flatten()->count()]);
        } catch (\Exception $e) {
            \Log::error('Error loading repayments: ' . $e->getMessage());
            $repayments = collect();
        }
        
        // Get loan disbursements for the month
        try {
            $disbursements = DB::table('loans')
                ->join('customers', 'loans.customer_id', '=', 'customers.id')
                ->where('loans.branch_id', $branchId)
                ->whereMonth('loans.disbursed_on', $currentMonth)
                ->whereYear('loans.disbursed_on', $currentYear)
                ->select(
                    'loans.disbursed_on',
                    'customers.name as customer_name',
                    'loans.amount',
                    'loans.id as loan_id'
                )
                ->get()
                ->groupBy('disbursed_on');
            
            \Log::info('Disbursements loaded', ['count' => $disbursements->flatten()->count()]);
        } catch (\Exception $e) {
            \Log::error('Error loading disbursements: ' . $e->getMessage());
            $disbursements = collect();
        }
        
        // Get cash collateral deposits (from receipts table)
        try {
            $deposits = DB::table('receipts')
                ->join('cash_collaterals', 'receipts.reference', '=', 'cash_collaterals.id')
                ->join('customers', 'cash_collaterals.customer_id', '=', 'customers.id')
                ->where('receipts.reference_type', 'Deposit')
                ->where('receipts.branch_id', $branchId)
                ->whereMonth('receipts.date', $currentMonth)
                ->whereYear('receipts.date', $currentYear)
                ->select(
                    'receipts.date',
                    'customers.name as customer_name',
                    'receipts.amount',
                    DB::raw("'deposit' as type")
                )
                ->get()
                ->groupBy(function($item) {
                    return Carbon::parse($item->date)->format('Y-m-d');
                });
            
            \Log::info('Deposits loaded', ['count' => $deposits->flatten()->count()]);
        } catch (\Exception $e) {
            \Log::error('Error loading deposits: ' . $e->getMessage());
            $deposits = collect();
        }
        
        // Get cash collateral withdrawals (from payments table)
        try {
            $withdrawals = DB::table('payments')
                ->join('cash_collaterals', 'payments.reference', '=', 'cash_collaterals.id')
                ->join('customers', 'cash_collaterals.customer_id', '=', 'customers.id')
                ->where('payments.reference_type', 'Withdrawal')
                ->where('payments.branch_id', $branchId)
                ->whereMonth('payments.date', $currentMonth)
                ->whereYear('payments.date', $currentYear)
                ->select(
                    'payments.date',
                    'customers.name as customer_name',
                    'payments.amount',
                    DB::raw("'withdrawal' as type")
                )
                ->get()
                ->groupBy(function($item) {
                    return Carbon::parse($item->date)->format('Y-m-d');
                });
            
            \Log::info('Withdrawals loaded', ['count' => $withdrawals->flatten()->count()]);
        } catch (\Exception $e) {
            \Log::error('Error loading withdrawals: ' . $e->getMessage());
            $withdrawals = collect();
        }
        
        // Merge all cash transactions
        $cashTransactions = collect();
        foreach ($deposits as $date => $items) {
            $cashTransactions[$date] = $items;
        }
        foreach ($withdrawals as $date => $items) {
            if (!isset($cashTransactions[$date])) {
                $cashTransactions[$date] = collect();
            }
            $cashTransactions[$date] = $cashTransactions[$date]->merge($items);
        }
        
        // Generate calendar data
        $calendar = $this->generateCalendar($currentMonth, $currentYear, $loanSchedules, $repayments, $disbursements, $cashTransactions);
        
        // Return JSON for AJAX requests
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'calendar' => $calendar,
                'currentMonth' => $currentMonth,
                'currentYear' => $currentYear,
                'loanSchedules' => $loanSchedules,
                'repayments' => $repayments,
                'disbursements' => $disbursements,
                'cashTransactions' => $cashTransactions
            ]);
        }
        
        // Return view for direct access
        return view('calendar.index', compact(
            'calendar',
            'currentMonth',
            'currentYear',
            'loanSchedules',
            'repayments',
            'disbursements',
            'cashTransactions'
        ));
        } catch (\Exception $e) {
            \Log::error('Calendar error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error loading calendar: ' . $e->getMessage()
                ], 500);
            }
            
            abort(500, 'Error loading calendar');
        }
    }
    
    private function generateCalendar($month, $year, $loanSchedules, $repayments, $disbursements, $cashTransactions)
    {
        $firstDay = Carbon::create($year, $month, 1);
        $lastDay = $firstDay->copy()->endOfMonth();
        $startDate = $firstDay->copy()->startOfWeek(Carbon::SUNDAY);
        $endDate = $lastDay->copy()->endOfWeek(Carbon::SATURDAY);
        
        $calendar = [];
        $currentDate = $startDate->copy();
        
        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d');
            $isCurrentMonth = $currentDate->month == $month;
            
            $calendar[] = [
                'date' => $currentDate->copy(),
                'day' => $currentDate->day,
                'isCurrentMonth' => $isCurrentMonth,
                'isToday' => $currentDate->isToday(),
                'loanSchedules' => $loanSchedules[$dateKey] ?? collect(),
                'repayments' => $repayments[$dateKey] ?? collect(),
                'disbursements' => $disbursements[$dateKey] ?? collect(),
                'cashTransactions' => $cashTransactions[$dateKey] ?? collect(),
            ];
            
            $currentDate->addDay();
        }
        
        return $calendar;
    }
} 