<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Penalty;
use App\Models\Company;
use App\Models\Branch;
use App\Models\User;
use App\Models\ChartAccount;

class PenaltySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing records
        $company = Company::first();
        $branch = Branch::first();
        $user = User::first();

        // Get chart accounts for penalties (income accounts)
        $chartAccounts = ChartAccount::where('account_name', 'like', '%penalty%')
            ->orWhere('account_name', 'like', '%late%')
            ->orWhere('account_name', 'like', '%fee%')
            ->orWhere('account_name', 'like', '%service%')
            ->orWhere('account_name', 'like', '%charge%')
            ->orWhere('account_name', 'like', '%income%')
            ->orWhere('account_name', 'like', '%revenue%')
            ->get();

        // If no suitable chart accounts found, get any available
        if ($chartAccounts->isEmpty()) {
            $chartAccounts = ChartAccount::take(5)->get();
        }

        // If still no chart accounts, we'll use null (remove the foreign key constraint temporarily)
        $chartAccountId = $chartAccounts->first()->id ?? null;

        // Sample penalty data
        $penalties = [
            [
                'name' => 'Late Payment Penalty',
                'chart_account_id' => $chartAccountId,
                'penalty_type' => 'percentage',
                'amount' => 5.00,
                'deduction_type' => 'outstanding_amount',
                'description' => 'Penalty applied for late loan payments based on outstanding amount',
                'status' => 'active',
                'company_id' => $company->id ?? 1,
                'branch_id' => $branch->id ?? null,
                'created_by' => $user->id ?? 1,
                'updated_by' => $user->id ?? 1,
            ],
            [
                'name' => 'Default Penalty',
                'chart_account_id' => $chartAccountId,
                'penalty_type' => 'percentage',
                'amount' => 10.00,
                'deduction_type' => 'principal',
                'description' => 'Penalty applied for loan defaults based on original principal amount',
                'status' => 'active',
                'company_id' => $company->id ?? 1,
                'branch_id' => $branch->id ?? null,
                'created_by' => $user->id ?? 1,
                'updated_by' => $user->id ?? 1,
            ],
            [
                'name' => 'Processing Delay Penalty',
                'chart_account_id' => $chartAccountId,
                'penalty_type' => 'fixed',
                'amount' => 5000.00,
                'deduction_type' => 'outstanding_amount',
                'description' => 'Fixed penalty for processing delays in loan applications',
                'status' => 'active',
                'company_id' => $company->id ?? 1,
                'branch_id' => $branch->id ?? null,
                'created_by' => $user->id ?? 1,
                'updated_by' => $user->id ?? 1,
            ],
            [
                'name' => 'Early Repayment Penalty',
                'chart_account_id' => $chartAccountId,
                'penalty_type' => 'percentage',
                'amount' => 3.00,
                'deduction_type' => 'principal',
                'description' => 'Penalty for early loan repayment based on principal amount',
                'status' => 'inactive',
                'company_id' => $company->id ?? 1,
                'branch_id' => $branch->id ?? null,
                'created_by' => $user->id ?? 1,
                'updated_by' => $user->id ?? 1,
            ],
            [
                'name' => 'Documentation Penalty',
                'chart_account_id' => $chartAccountId,
                'penalty_type' => 'fixed',
                'amount' => 3000.00,
                'deduction_type' => 'outstanding_amount',
                'description' => 'Fixed penalty for incomplete or late documentation submission',
                'status' => 'active',
                'company_id' => $company->id ?? 1,
                'branch_id' => $branch->id ?? null,
                'created_by' => $user->id ?? 1,
                'updated_by' => $user->id ?? 1,
            ],
            [
                'name' => 'Insurance Lapse Penalty',
                'chart_account_id' => $chartAccountId,
                'penalty_type' => 'percentage',
                'amount' => 7.50,
                'deduction_type' => 'outstanding_amount',
                'description' => 'Penalty for insurance policy lapses during loan term',
                'status' => 'active',
                'company_id' => $company->id ?? 1,
                'branch_id' => $branch->id ?? null,
                'created_by' => $user->id ?? 1,
                'updated_by' => $user->id ?? 1,
            ],
            [
                'name' => 'Collateral Deficiency Penalty',
                'chart_account_id' => $chartAccountId,
                'penalty_type' => 'fixed',
                'amount' => 10000.00,
                'deduction_type' => 'principal',
                'description' => 'Fixed penalty for insufficient collateral coverage',
                'status' => 'active',
                'company_id' => $company->id ?? 1,
                'branch_id' => $branch->id ?? null,
                'created_by' => $user->id ?? 1,
                'updated_by' => $user->id ?? 1,
            ],
            [
                'name' => 'Administrative Penalty',
                'chart_account_id' => $chartAccountId,
                'penalty_type' => 'fixed',
                'amount' => 2000.00,
                'deduction_type' => 'outstanding_amount',
                'description' => 'Administrative penalty for non-compliance with loan terms',
                'status' => 'active',
                'company_id' => $company->id ?? 1,
                'branch_id' => $branch->id ?? null,
                'created_by' => $user->id ?? 1,
                'updated_by' => $user->id ?? 1,
            ],
        ];

        // Create penalties
        foreach ($penalties as $penaltyData) {
            Penalty::create($penaltyData);
        }

        $this->command->info('Penalties seeded successfully!');
    }
}
