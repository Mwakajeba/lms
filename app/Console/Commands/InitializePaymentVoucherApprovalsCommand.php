<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payment;
use App\Models\PaymentVoucherApprovalSetting;

class InitializePaymentVoucherApprovalsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment-vouchers:initialize-approvals {--company-id= : Specific company ID to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize approval workflow for existing payment vouchers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $companyId = $this->option('company-id');
        
        if ($companyId) {
            $this->info("Processing company ID: {$companyId}");
            $this->processCompany($companyId);
        } else {
            $this->info("Processing all companies...");
            $companies = \App\Models\Company::pluck('id');
            
            foreach ($companies as $companyId) {
                $this->info("Processing company ID: {$companyId}");
                $this->processCompany($companyId);
            }
        }
        
        $this->info('Payment voucher approval initialization completed!');
    }
    
    private function processCompany($companyId)
    {
        $settings = PaymentVoucherApprovalSetting::where('company_id', $companyId)->first();
        
        if (!$settings) {
            $this->warn("No approval settings found for company ID: {$companyId}");
            return;
        }
        
        $this->info("Found approval settings for company ID: {$companyId}");
        $this->info("Approval levels: {$settings->approval_levels}");
        $this->info("Auto approval limit: {$settings->auto_approval_limit}");
        
        // Get all manual payment vouchers for this company that don't have approval records
        $payments = Payment::whereHas('user', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })->where('reference_type', 'manual')
          ->whereDoesntHave('approvals')->get();
        
        $this->info("Found {$payments->count()} payment vouchers without approval records");
        
        $processed = 0;
        $skipped = 0;
        
        foreach ($payments as $payment) {
            try {
                // Check if payment requires approval
                $requiredLevel = $settings->getRequiredApprovalLevel($payment->amount);
                
                if ($requiredLevel === 0) {
                    // Auto-approve
                    $payment->update([
                        'approved' => true,
                        'approved_by' => $payment->user_id,
                        'approved_at' => now(),
                    ]);
                    $this->line("Auto-approved payment #{$payment->reference} (amount: {$payment->amount})");
                    $processed++;
                } else {
                    // Initialize approval workflow
                    $payment->initializeApprovalWorkflow();
                    $this->line("Initialized approval workflow for payment #{$payment->reference} (amount: {$payment->amount}, level: {$requiredLevel})");
                    $processed++;
                }
            } catch (\Exception $e) {
                $this->error("Failed to process payment #{$payment->reference}: " . $e->getMessage());
                $skipped++;
            }
        }

        // Also handle non-manual payments by auto-approving them
        $nonManualPayments = Payment::whereHas('user', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })->where('reference_type', '!=', 'manual')
          ->where('approved', false)
          ->get();

        $this->info("Found {$nonManualPayments->count()} non-manual payments to auto-approve");

        foreach ($nonManualPayments as $payment) {
            try {
                $payment->update([
                    'approved' => true,
                    'approved_by' => $payment->user_id,
                    'approved_at' => now(),
                ]);
                $this->line("Auto-approved non-manual payment #{$payment->reference} (amount: {$payment->amount})");
                $processed++;
            } catch (\Exception $e) {
                $this->error("Failed to auto-approve non-manual payment #{$payment->reference}: " . $e->getMessage());
                $skipped++;
            }
        }
        
        $this->info("Processed: {$processed}, Skipped: {$skipped}");
    }
} 