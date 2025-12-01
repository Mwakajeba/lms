<?php

namespace Database\Seeders;

use App\Models\Subscription;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first company
        $company = Company::first();

        if (!$company) {
            $this->command->info('No company found. Please run CompanySeeder first.');
            return;
        }

        // Create a sample subscription
        Subscription::create([
            'company_id' => $company->id,
            'plan_name' => 'Premium Plan',
            'plan_description' => 'Full access to all features with premium support',
            'amount' => 50000.00,
            'currency' => 'TZS',
            'billing_cycle' => 'monthly',
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addMonth(),
            'status' => 'active',
            'payment_status' => 'paid',
            'payment_method' => 'Bank Transfer',
            'transaction_id' => 'TXN-' . strtoupper(uniqid()),
            'payment_notes' => 'Initial subscription payment',
            'payment_date' => Carbon::now(),
            'auto_renew' => true,
            'features' => [
                'unlimited_loans',
                'advanced_reporting',
                'priority_support',
                'custom_branding',
                'api_access'
            ],
        ]);

        $this->command->info('Sample subscription created successfully for company: ' . $company->name);
    }
}