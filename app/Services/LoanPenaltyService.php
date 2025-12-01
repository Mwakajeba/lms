<?php

namespace App\Services;

use App\Models\Penalty;
use App\Models\GlTransaction; 
use Illuminate\Support\Collection;

class LoanPenaltyService
{
    /**
     * Get total penalty balance (debit - credit) from active penalty receivable accounts.
     */
    public static function getTotalPenaltyBalance($branchId = null): float
    {
        // Retrieve the penalty receivables account ID from the Penalty model
        $penaltyAccountId = Penalty::query()
            ->where('status', 'active')
            ->whereNotNull('penalty_receivables_account_id')
            ->value('penalty_receivables_account_id');

            info($penaltyAccountId);
        // If no active penalty account is found, return 0.0
        if (!$penaltyAccountId) {
            return 0.0;
        }

        // Use the GlTransaction model to query for total debit and credit amounts
        $query = GlTransaction::query()
            ->where('chart_account_id', $penaltyAccountId);
            
        // Filter by branch if provided
        if ($branchId) {
            $query->whereHas('journal', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }
        
        $totals = $query->selectRaw('
                SUM(CASE WHEN nature = "debit" THEN amount ELSE 0 END) as total_debit,
                SUM(CASE WHEN nature = "credit" THEN amount ELSE 0 END) as total_credit
            ')
            ->first();


            info($totals);

        // Cast totals to float, defaulting to 0 if null
        $debit = (float) ($totals->total_debit ?? 0);
        $credit = (float) ($totals->total_credit ?? 0);

        // Calculate and return the net balance
        return $debit - $credit;
    }

    /**
     * Get penalty balance per customer, optimized to avoid DB::raw.
     */
    public static function getCustomerPenaltyBalances(): Collection
    {
        // Retrieve the penalty receivables account ID from the Penalty model
        $penaltyAccountId = Penalty::query()
            ->where('status', 'active')
            ->whereNotNull('penalty_receivables_account_id')
            ->value('penalty_receivables_account_id');
            info($penaltyAccountId);

        // If no active penalty account is found, return an empty collection
        if (!$penaltyAccountId) {
            return collect();
        }

        // We select only the necessary columns from gl_transactions.
        $transactions = GlTransaction::query()
            ->with('customer') // Eager load the customer relationship
            ->where('chart_account_id', $penaltyAccountId)
            ->select('customer_id', 'nature', 'amount') // Only select from gl_transactions table
            ->get();

        // Group transactions by customer_id and calculate balances using collection methods
        return $transactions->groupBy('customer_id')->map(function ($customerTransactions, $customerId) {
            $totalDebit = $customerTransactions->where('nature', 'debit')->sum('amount');
            $totalCredit = $customerTransactions->where('nature', 'credit')->sum('amount');

            // Check if customer relationship exists before accessing its properties
            $customerName = $customerTransactions->first()->customer->name ?? 'Unknown Customer';
            $customerPhone = $customerTransactions->first()->customer->phone1 ?? '+25555......';

            return [
                'customer_id' => $customerId,
                'customer_name' => $customerName, 
                'customer_phone' => $customerPhone, 
                'penalty_balance' => round($totalDebit - $totalCredit, 2)
            ];
        })->values(); // Use values() to reset keys if needed, making it a simple array of objects.
    }
}
