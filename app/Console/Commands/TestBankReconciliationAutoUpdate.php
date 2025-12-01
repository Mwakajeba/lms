<?php

namespace App\Console\Commands;

use App\Models\BankReconciliation;
use App\Models\GlTransaction;
use App\Models\BankAccount;
use App\Services\BankReconciliationService;
use Illuminate\Console\Command;

class TestBankReconciliationAutoUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:bank-reconciliation-auto-update {bank_account_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test automatic bank reconciliation updates when new GL transactions are created';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $bankAccountId = $this->argument('bank_account_id');
        
        if ($bankAccountId) {
            $bankAccount = BankAccount::find($bankAccountId);
            if (!$bankAccount) {
                $this->error("Bank account with ID {$bankAccountId} not found.");
                return 1;
            }
            $bankAccounts = collect([$bankAccount]);
        } else {
            $bankAccounts = BankAccount::with('chartAccount')->get();
        }

        $this->info("Testing automatic bank reconciliation updates...");
        $this->newLine();

        foreach ($bankAccounts as $bankAccount) {
            $this->info("Testing for Bank Account: {$bankAccount->name} (ID: {$bankAccount->id})");
            
            // Get active reconciliations
            $activeReconciliations = BankReconciliation::where('bank_account_id', $bankAccount->id)
                ->whereIn('status', ['draft', 'in_progress'])
                ->get();

            if ($activeReconciliations->isEmpty()) {
                $this->warn("  No active reconciliations found for this bank account.");
                continue;
            }

            foreach ($activeReconciliations as $reconciliation) {
                $this->info("  Reconciliation ID: {$reconciliation->id}");
                $this->info("    Period: {$reconciliation->start_date} to {$reconciliation->end_date}");
                $this->info("    Status: {$reconciliation->status}");
                $this->info("    Current Book Balance: " . number_format($reconciliation->book_balance, 2));
                
                // Count existing reconciliation items
                $existingItems = $reconciliation->reconciliationItems()->count();
                $this->info("    Existing reconciliation items: {$existingItems}");
                
                // Get GL transactions in the reconciliation period
                $glTransactions = GlTransaction::where('chart_account_id', $bankAccount->chart_account_id)
                    ->whereBetween('date', [$reconciliation->start_date, $reconciliation->end_date])
                    ->get();
                
                $this->info("    GL transactions in period: {$glTransactions->count()}");
                
                // Test the service
                $service = app(BankReconciliationService::class);
                
                foreach ($glTransactions as $glTransaction) {
                    $this->info("    Testing transaction ID: {$glTransaction->id} ({$glTransaction->description})");
                    
                    // Check if this transaction affects the reconciliation
                    $activeReconciliations = $service->getActiveReconciliationsForTransaction($glTransaction);
                    
                    if ($activeReconciliations->contains($reconciliation->id)) {
                        $this->info("      ✓ Transaction affects this reconciliation");
                        
                        // Update the reconciliation
                        $service->updateReconciliationsForTransaction($glTransaction);
                        
                        // Refresh the reconciliation
                        $reconciliation->refresh();
                        
                        $this->info("      New Book Balance: " . number_format($reconciliation->book_balance, 2));
                        $this->info("      New Difference: " . number_format($reconciliation->difference, 2));
                    } else {
                        $this->warn("      ✗ Transaction does not affect this reconciliation");
                    }
                }
                
                $this->newLine();
            }
        }

        $this->info("Test completed successfully!");
        return 0;
    }
} 