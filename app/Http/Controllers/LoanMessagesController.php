<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Loan;
use App\Models\LoanSchedule;
use App\Models\Repayment;
use App\Models\Customer;

class LoanMessagesController extends Controller
{
    public function getMessages()
    {
        try {
            \Log::info('LoanMessagesController: Starting to fetch messages');
            
            $branchId = auth()->user()->branch_id;
            \Log::info('LoanMessagesController: User branch ID: ' . $branchId);
            
            $today = Carbon::today();
            \Log::info('LoanMessagesController: Today\'s date: ' . $today->toDateString());
            
            // Get due payments for today and tomorrow
            $dueMessages = $this->getDueMessages($branchId, $today);
            \Log::info('LoanMessagesController: Found ' . count($dueMessages) . ' due messages');
            
            // Get loans in arrears
            $arrearsMessages = $this->getArrearsMessages($branchId, $today);
            \Log::info('LoanMessagesController: Found ' . count($arrearsMessages) . ' arrears messages');
            
            // Get pending loan approvals
            $approvalMessages = $this->getApprovalMessages($branchId);
            \Log::info('LoanMessagesController: Found ' . count($approvalMessages) . ' approval messages');
            
            // Combine all messages
            $messages = array_merge($dueMessages, $arrearsMessages, $approvalMessages);
            \Log::info('LoanMessagesController: Total messages: ' . count($messages));
            
            // Update message stats
            $stats = $this->getMessageStats($messages, $today);
            \Log::info('LoanMessagesController: Stats calculated', $stats);
            
            return response()->json([
                'success' => true,
                'messages' => $messages,
                'stats' => $stats
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error fetching loan messages: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching messages: ' . $e->getMessage()
            ], 500);
        }
    }
    
    private function getDueMessages($branchId, $today)
    {
        $dueMessages = [];
        
        // Get payments due today
        $dueToday = DB::table('loan_schedules as ls')
            ->join('loans as l', 'ls.loan_id', '=', 'l.id')
            ->join('customers as c', 'l.customer_id', '=', 'c.id')
            ->where('l.branch_id', $branchId)
            ->where('l.status', 'active')
            ->whereDate('ls.due_date', $today)
            ->select(
                'ls.id',
                'ls.due_date',
                'ls.principal',
                'ls.interest',
                'ls.fee_amount',
                'ls.penalty_amount',
                'l.id as loan_id',
                'l.amount as loan_amount',
                'c.id as customer_id',
                'c.name as customer_name',
                'c.phone1'
            )
            ->get();
            
        foreach ($dueToday as $schedule) {
            $amountDue = $schedule->principal + $schedule->interest + $schedule->fee_amount + $schedule->penalty_amount;
            
            $dueMessages[] = [
                'id' => 'due_' . $schedule->id,
                'type' => 'due',
                'title' => 'Payment Due Today',
                'message' => "Customer {$schedule->customer_name} has a loan payment due today. Amount: TZS " . number_format($amountDue, 2),
                'customer' => $schedule->customer_name,
                'customerId' => $schedule->customer_id,
                'phone' => $schedule->phone1,
                'amount' => $amountDue,
                'dueDate' => $schedule->due_date,
                'loanId' => $schedule->loan_id,
                'isRead' => false,
                'priority' => 'high'
            ];
        }
        
        // Get payments due tomorrow
        $dueTomorrow = DB::table('loan_schedules as ls')
            ->join('loans as l', 'ls.loan_id', '=', 'l.id')
            ->join('customers as c', 'l.customer_id', '=', 'c.id')
            ->where('l.branch_id', $branchId)
            ->where('l.status', 'active')
            ->whereDate('ls.due_date', $today->copy()->addDay())
            ->select(
                'ls.id',
                'ls.due_date',
                'ls.principal',
                'ls.interest',
                'ls.fee_amount',
                'ls.penalty_amount',
                'l.id as loan_id',
                'l.amount as loan_amount',
                'c.id as customer_id',
                'c.name as customer_name',
                'c.phone1'
            )
            ->get();
            
        foreach ($dueTomorrow as $schedule) {
            $amountDue = $schedule->principal + $schedule->interest + $schedule->fee_amount + $schedule->penalty_amount;
            
            $dueMessages[] = [
                'id' => 'due_' . $schedule->id,
                'type' => 'due',
                'title' => 'Payment Due Tomorrow',
                'message' => "Customer {$schedule->customer_name} has a loan payment due tomorrow. Amount: TZS " . number_format($amountDue, 2),
                'customer' => $schedule->customer_name,
                'customerId' => $schedule->customer_id,
                'phone' => $schedule->phone1,
                'amount' => $amountDue,
                'dueDate' => $schedule->due_date,
                'loanId' => $schedule->loan_id,
                'isRead' => false,
                'priority' => 'medium'
            ];
        }
        
        return $dueMessages;
    }
    
    private function getArrearsMessages($branchId, $today)
    {
        $arrearsMessages = [];
        
        // Get loans in arrears (overdue payments)
        $arrears = DB::table('loan_schedules as ls')
            ->join('loans as l', 'ls.loan_id', '=', 'l.id')
            ->join('customers as c', 'l.customer_id', '=', 'c.id')
            ->leftJoin('repayments as r', function($join) {
                $join->on('r.loan_schedule_id', '=', 'ls.id')
                     ->orOn('r.loan_id', '=', 'ls.loan_id');
            })
            ->where('l.branch_id', $branchId)
            ->where('l.status', 'active')
            ->whereDate('ls.due_date', '<', $today)
            ->select(
                'ls.id',
                'ls.due_date',
                'ls.principal',
                'ls.interest',
                'ls.fee_amount',
                'ls.penalty_amount',
                'l.id as loan_id',
                'l.amount as loan_amount',
                'c.id as customer_id',
                'c.name as customer_name',
                'c.phone1',
                DB::raw('DATEDIFF(CURDATE(), ls.due_date) as days_overdue'),
                DB::raw('COALESCE(SUM(r.principal + r.interest), 0) as total_paid')
            )
            ->groupBy('ls.id', 'ls.due_date', 'ls.principal', 'ls.interest', 'ls.fee_amount', 'ls.penalty_amount', 'l.id', 'l.amount', 'c.id', 'c.name', 'c.phone1')
            ->havingRaw('(ls.principal + ls.interest + ls.fee_amount + ls.penalty_amount) > COALESCE(SUM(r.principal + r.interest), 0)')
            ->orderBy('days_overdue', 'desc')
            ->get();
            
        foreach ($arrears as $arrear) {
            $amountDue = $arrear->principal + $arrear->interest + $arrear->fee_amount + $arrear->penalty_amount;
            $outstandingAmount = $amountDue - $arrear->total_paid;
            $daysOverdue = $arrear->days_overdue;
            
            // Determine priority based on days overdue
            $priority = 'medium';
            if ($daysOverdue > 30) {
                $priority = 'critical';
            } elseif ($daysOverdue > 15) {
                $priority = 'high';
            }
            
            $arrearsMessages[] = [
                'id' => 'arrears_' . $arrear->id,
                'type' => 'arrears',
                'title' => 'Loan in Arrears',
                'message' => "Customer {$arrear->customer_name} is {$daysOverdue} days overdue on loan payment. Outstanding Amount: TZS " . number_format($outstandingAmount, 2),
                'customer' => $arrear->customer_name,
                'customerId' => $arrear->customer_id,
                'phone' => $arrear->phone1,
                'amount' => $outstandingAmount,
                'dueDate' => $arrear->due_date,
                'loanId' => $arrear->loan_id,
                'daysOverdue' => $daysOverdue,
                'isRead' => false,
                'priority' => $priority
            ];
        }
        
        return $arrearsMessages;
    }
    
    private function getApprovalMessages($branchId)
    {
        $approvalMessages = [];
        
        // Get pending loan approvals
        $pendingLoans = DB::table('loans as l')
            ->join('customers as c', 'l.customer_id', '=', 'c.id')
            ->where('l.branch_id', $branchId)
            ->where('l.status', 'pending')
            ->select(
                'l.id as loan_id',
                'l.amount',
                'l.created_at',
                'c.id as customer_id',
                'c.name as customer_name',
                'c.phone1'
            )
            ->orderBy('l.created_at', 'asc')
            ->get();
            
        foreach ($pendingLoans as $loan) {
            $approvalMessages[] = [
                'id' => 'approval_' . $loan->loan_id,
                'type' => 'approval',
                'title' => 'Loan Approval Request',
                'message' => "New loan application from Customer {$loan->customer_name} requires approval. Amount: TZS " . number_format($loan->amount, 2),
                'customer' => $loan->customer_name,
                'customerId' => $loan->customer_id,
                'phone' => $loan->phone1,
                'amount' => $loan->amount,
                'dueDate' => $loan->created_at,
                'loanId' => $loan->loan_id,
                'isRead' => false,
                'priority' => 'medium'
            ];
        }
        
        return $approvalMessages;
    }
    
    private function getMessageStats($messages, $today)
    {
        $dueToday = collect($messages)->where('type', 'due')->where('dueDate', $today->toDateString())->count();
        $inArrears = collect($messages)->where('type', 'arrears')->count();
        $pendingApproval = collect($messages)->where('type', 'approval')->where('isRead', false)->count();
        $total = count($messages);
        $unreadCount = collect($messages)->where('isRead', false)->count();
        
        return [
            'dueToday' => $dueToday,
            'inArrears' => $inArrears,
            'pendingApproval' => $pendingApproval,
            'total' => $total,
            'unreadCount' => $unreadCount
        ];
    }
} 