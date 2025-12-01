<?php
// This controller will provide group loans data for DataTables AJAX
namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupLoanAjaxController extends Controller
{
    public function index(Request $request, $groupId)
    {
        $group = Group::findOrFail($groupId);

        // Get loans through the group's loans relationship (which now uses hasManyThrough)
        // If the relationship doesn't work, fall back to the helper method
        try {
            $loans = $group->loans()->with(['customer', 'repayments', 'schedule'])->get();
        } catch (\Exception $e) {
            // Fallback to helper method
            $loans = $group->getGroupLoans()->with(['customer', 'repayments', 'schedule'])->get();
        }

        $data = $loans->map(function ($loan) {
            // Calculate total paid from repayments
            $totalPaid = $loan->repayments->sum(function ($r) {
                return $r->principal + $r->interest + $r->penalt_amount + $r->fee_amount;
            });

            // Calculate amount with interest from loan amount and interest amount
            $amountWithInterest = $loan->amount_total ?? ($loan->amount + ($loan->interest_amount ?? 0));

            // Calculate outstanding balance
            $outstanding = $amountWithInterest - $totalPaid;

            // Generate show URL
            $showUrl = route('loans.show', [\Vinkla\Hashids\Facades\Hashids::encode($loan->id)]);

            return [
                'loan_no' => $loan->loanNo ?? $loan->id,
                'customer_no' => $loan->customer->customerNo ?? '',
                'customer' => $loan->customer->name ?? '',
                'amount_with_interest' => number_format($amountWithInterest, 2),
                'total_paid' => number_format($totalPaid, 2),
                'outstanding' => number_format($outstanding, 2),
                'disbursed_on' => $loan->disbursed_on ? \Carbon\Carbon::parse($loan->disbursed_on)->format('M d, Y') : ($loan->created_at ? $loan->created_at->format('M d, Y') : ''),
                'last_repayment_date' => $loan->last_repayment_date ? \Carbon\Carbon::parse($loan->last_repayment_date)->format('M d, Y') : '',
                'show_url' => $showUrl,
            ];
        });

        return response()->json(['data' => $data]);
    }
}
