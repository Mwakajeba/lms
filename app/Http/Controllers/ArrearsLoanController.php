<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Response;
use PDF;

class ArrearsLoanController extends Controller
{
    // Show all loans in arrears for 30+ days (AJAX DataTable)
    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                $arrearsData = $this->getArrearsData();
                
                // Filter for 1-30 days in arrears and format for DataTable
                $filteredData = collect($arrearsData)
                    ->filter(function($item) {
                        return $item['days_in_arrears'] >= 1 && $item['days_in_arrears'] <= 30;
                    })
                    ->map(function($item) {
                        return [
                            'loan_id' => $item['loan_id'] ?? null,
                            'customer_name' => $item['customer'],
                            'customer_no' => $item['customer_no'],
                            'loan_no' => $item['loan_no'],
                            'total_outstanding' => $item['loan_amount'],
                            'amount_in_arrears' => $item['arrears_amount'],
                            'days_in_arrears' => $item['days_in_arrears']
                        ];
                    })
                    ->values()
                    ->toArray();
                
                return response()->json(['data' => $filteredData]);
            } catch (\Exception $e) {
                \Log::error('ArrearsLoanController AJAX error: ' . $e->getMessage());
                return response()->json(['data' => [], 'error' => $e->getMessage()], 500);
            }
        }
        return view('arrears_loans.list');
    }

    private function getArrearsData($branchId = null, $groupId = null, $loanOfficerId = null)
    {
        $today = \Carbon\Carbon::now();
        
        $loansQuery = \App\Models\Loan::with(['customer', 'branch', 'group', 'loanOfficer', 'schedule.repayments'])
                          ->where('status', 'active');

        if ($branchId) {
            $loansQuery->where('branch_id', $branchId);
        }

        if ($groupId) {
            $loansQuery->where('group_id', $groupId);
        }

        if ($loanOfficerId) {
            $loansQuery->where('loan_officer_id', $loanOfficerId);
        }

        $loans = $loansQuery->get();
        $arrearsData = [];

        foreach ($loans as $loan) {
            $totalArrears = 0;
            $daysInArrears = 0;
            $firstOverdueDate = null;
            $overdueSchedules = [];

            // Check each schedule item for overdue amounts
            foreach ($loan->schedule->sortBy('due_date') as $schedule) {
                $dueDate = \Carbon\Carbon::parse($schedule->due_date);
                
                if ($dueDate->lt($today) && $schedule->remaining_amount > 0) {
                    $totalArrears += $schedule->remaining_amount;
                    $overdueSchedules[] = $schedule;
                    
                    if (!$firstOverdueDate) {
                        $firstOverdueDate = $dueDate;
                        $daysInArrears = round($firstOverdueDate->diffInDays($today));
                    }
                }
            }

            // Only include loans that have arrears
            if ($totalArrears > 0) {
                $arrearsData[] = [
                    'loan_id' => $loan->id,
                    'customer' => $loan->customer->name ?? 'N/A',
                    'customer_no' => $loan->customer->customerNo ?? 'N/A',
                    'phone' => $loan->customer->phone1 ?? 'N/A',
                    'loan_no' => $loan->loanNo ?? 'N/A',
                    'loan_amount' => $loan->amount,
                    'disbursed_date' => $loan->disbursed_on ? \Carbon\Carbon::parse($loan->disbursed_on)->format('d-m-Y') : 'N/A',
                    'branch' => $loan->branch->name ?? 'N/A',
                    'group' => $loan->group->name ?? 'N/A',
                    'loan_officer' => $loan->loanOfficer->name ?? 'N/A',
                    'arrears_amount' => $totalArrears,
                    'days_in_arrears' => $daysInArrears,
                    'first_overdue_date' => $firstOverdueDate ? $firstOverdueDate->format('d-m-Y') : 'N/A',
                    'overdue_schedules_count' => count($overdueSchedules),
                ];
            }
        }

        // Sort by days in arrears (highest first)
        usort($arrearsData, function($a, $b) {
            return $b['days_in_arrears'] - $a['days_in_arrears'];
        });

        return $arrearsData;
    }

    // Export all loans in arrears for 30+ days to PDF
    public function exportPdf(Request $request)
    {
        $loans = DB::table('loan_schedules')
            ->join('customers', 'loan_schedules.customer_id', '=', 'customers.id')
            ->where('loan_schedules.days_in_arrears', '>=', 30)
            ->select(
                'customers.name as customer_name',
                DB::raw('(loan_schedules.principal + loan_schedules.interest) as amount_in_arrears'),
                'loan_schedules.days_in_arrears'
            )
            ->orderByDesc('loan_schedules.days_in_arrears')
            ->get();
        $pdf = PDF::loadView('arrears_loans.pdf', compact('loans'));
        return $pdf->download('arrears_loans_30plus_days.pdf');
    }
}
