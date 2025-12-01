<?php

namespace App\Services;

use App\Models\BankReconciliation;
use App\Models\BankReconciliationItem;
use App\Models\GlTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BankReconciliationService
{
    /**
     * Update bank reconciliations when a new GL transaction is created
     */
    public function updateReconciliationsForTransaction(GlTransaction $glTransaction)
    {
        try {
            DB::beginTransaction();

            // Get the chart account for this transaction
            $chartAccount = $glTransaction->chartAccount;
            if (!$chartAccount) {
                return;
            }

            // Find bank accounts that use this chart account
            $bankAccounts = $glTransaction->chartAccount->bankAccounts;

            foreach ($bankAccounts as $bankAccount) {
                // Find active reconciliations for this bank account
                $activeReconciliations = BankReconciliation::where('bank_account_id', $bankAccount->id)
                    ->whereIn('status', ['draft', 'in_progress'])
                    ->where('start_date', '<=', $glTransaction->date)
                    ->where('end_date', '>=', $glTransaction->date)
                    ->get();

                foreach ($activeReconciliations as $reconciliation) {
                    $this->updateReconciliation($reconciliation, $glTransaction);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update bank reconciliations for transaction: ' . $e->getMessage(), [
                'transaction_id' => $glTransaction->id,
                'chart_account_id' => $glTransaction->chart_account_id,
            ]);
        }
    }

    /**
     * Update a specific reconciliation with a new transaction
     */
    private function updateReconciliation(BankReconciliation $reconciliation, GlTransaction $glTransaction)
    {
        // Store old book balance for notification
        $oldBookBalance = $reconciliation->book_balance;
        
        // Update book balance
        $reconciliation->calculateBookBalance();
        
        // Recalculate adjusted balances
        $reconciliation->recalculateAdjustedBalances();
        
        // Add new transaction as reconciliation item if it doesn't exist
        $this->addTransactionToReconciliation($reconciliation, $glTransaction);
        
        // Send notification if book balance changed
        if ($oldBookBalance != $reconciliation->book_balance) {
            $this->sendUpdateNotification($reconciliation, $glTransaction, $oldBookBalance, $reconciliation->book_balance);
        }
    }

    /**
     * Send notification about reconciliation update
     */
    private function sendUpdateNotification(BankReconciliation $reconciliation, GlTransaction $glTransaction, $oldBookBalance, $newBookBalance)
    {
        try {
            $reconciliation->user->notify(new \App\Notifications\BankReconciliationUpdated(
                $reconciliation,
                $glTransaction,
                $oldBookBalance,
                $newBookBalance
            ));
        } catch (\Exception $e) {
            Log::error('Failed to send bank reconciliation update notification: ' . $e->getMessage());
        }
    }

    /**
     * Add a GL transaction to reconciliation items if it doesn't exist
     */
    private function addTransactionToReconciliation(BankReconciliation $reconciliation, GlTransaction $glTransaction)
    {
        // Check if this transaction is already in the reconciliation items
        $existingItem = BankReconciliationItem::where('bank_reconciliation_id', $reconciliation->id)
            ->where('gl_transaction_id', $glTransaction->id)
            ->first();

        if (!$existingItem) {
            BankReconciliationItem::create([
                'bank_reconciliation_id' => $reconciliation->id,
                'gl_transaction_id' => $glTransaction->id,
                'transaction_type' => 'book_entry',
                'reference' => $glTransaction->transaction_id,
                'description' => $glTransaction->description,
                'transaction_date' => $glTransaction->date,
                'amount' => $glTransaction->amount,
                'nature' => $glTransaction->nature,
                'is_bank_statement_item' => false,
                'is_book_entry' => true,
                'is_reconciled' => false, // Always start as unreconciled
                'notes' => 'Auto-imported from GL transaction',
            ]);
        }
    }

    /**
     * Import all missing GL transactions to reconciliation items
     */
    public function importMissingTransactions(BankReconciliation $reconciliation)
    {
        // Get all GL transactions for this bank account in the reconciliation period
        $glTransactions = GlTransaction::where('chart_account_id', $reconciliation->bankAccount->chart_account_id)
            ->whereBetween('date', [$reconciliation->start_date, $reconciliation->end_date])
            ->get();

        // Get existing reconciliation items
        $existingGlTransactionIds = $reconciliation->reconciliationItems()
            ->whereNotNull('gl_transaction_id')
            ->pluck('gl_transaction_id')
            ->toArray();

        // Add only missing transactions
        foreach ($glTransactions as $glTransaction) {
            if (!in_array($glTransaction->id, $existingGlTransactionIds)) {
                $this->addTransactionToReconciliation($reconciliation, $glTransaction);
            }
        }
    }

    /**
     * Get all active reconciliations that might be affected by a transaction
     */
    public function getActiveReconciliationsForTransaction(GlTransaction $glTransaction)
    {
        $chartAccount = $glTransaction->chartAccount;
        if (!$chartAccount) {
            return collect();
        }

        return BankReconciliation::whereHas('bankAccount', function ($query) use ($chartAccount) {
                $query->where('chart_account_id', $chartAccount->id);
            })
            ->whereIn('status', ['draft', 'in_progress'])
            ->where('start_date', '<=', $glTransaction->date)
            ->where('end_date', '>=', $glTransaction->date)
            ->get();
    }

    /**
     * Check if a reconciliation needs to be updated
     */
    public function reconciliationNeedsUpdate(BankReconciliation $reconciliation)
    {
        // Check if there are any new GL transactions that haven't been imported
        $latestImportedTransaction = $reconciliation->reconciliationItems()
            ->whereNotNull('gl_transaction_id')
            ->orderBy('created_at', 'desc')
            ->first();

        $latestGlTransaction = GlTransaction::where('chart_account_id', $reconciliation->bankAccount->chart_account_id)
            ->whereBetween('date', [$reconciliation->start_date, $reconciliation->end_date])
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$latestImportedTransaction || !$latestGlTransaction) {
            return true;
        }

        return $latestGlTransaction->created_at->gt($latestImportedTransaction->created_at);
    }
} 