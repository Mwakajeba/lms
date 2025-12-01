<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Fee;
use App\Models\Company;
use App\Models\Branch;
use App\Models\User;
use App\Models\ChartAccount;

class FeeSeeder extends Seeder
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

        // Get chart accounts for fees (income accounts)
        $chartAccounts = ChartAccount::where('account_name', 'like', '%fee%')
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

        // Sample fee data
        $fees = [
            [
                'name' => 'Application Fee',
                'chart_account_id' => $chartAccountId,
                'fee_type' => 'fixed',
                'amount' => 5000.00,
                'description' => 'One-time application processing fee for new loan applications',
                'status' => 'active',
                'company_id' => $company->id ?? 1,
                'branch_id' => $branch->id ?? null,
                'created_by' => $user->id ?? 1,
                'updated_by' => $user->id ?? 1,
            ],
            [
                'name' => 'Processing Fee',
                'chart_account_id' => $chartAccountId,
                'fee_type' => 'percentage',
                'amount' => 2.50,
                'description' => 'Processing fee calculated as percentage of loan amount',
                'status' => 'active',
                'company_id' => $company->id ?? 1,
                'branch_id' => $branch->id ?? null,
                'created_by' => $user->id ?? 1,
                'updated_by' => $user->id ?? 1,
            ],
            [
                'name' => 'Late Payment Penalty',
                'chart_account_id' => $chartAccountId,
                'fee_type' => 'percentage',
                'amount' => 5.00,
                'description' => 'Penalty fee for late loan payments',
                'status' => 'active',
                'company_id' => $company->id ?? 1,
                'branch_id' => $branch->id ?? null,
                'created_by' => $user->id ?? 1,
                'updated_by' => $user->id ?? 1,
            ],
            [
                'name' => 'Documentation Fee',
                'chart_account_id' => $chartAccountId,
                'fee_type' => 'fixed',
                'amount' => 3000.00,
                'description' => 'Fee for document preparation and processing',
                'status' => 'active',
                'company_id' => $company->id ?? 1,
                'branch_id' => $branch->id ?? null,
                'created_by' => $user->id ?? 1,
                'updated_by' => $user->id ?? 1,
            ],
            [
                'name' => 'Insurance Fee',
                'chart_account_id' => $chartAccountId,
                'fee_type' => 'percentage',
                'amount' => 1.50,
                'description' => 'Insurance coverage fee for loan protection',
                'status' => 'active',
                'company_id' => $company->id ?? 1,
                'branch_id' => $branch->id ?? null,
                'created_by' => $user->id ?? 1,
                'updated_by' => $user->id ?? 1,
            ],
            [
                'name' => 'Administrative Fee',
                'chart_account_id' => $chartAccountId,
                'fee_type' => 'fixed',
                'amount' => 2000.00,
                'description' => 'Administrative handling fee for loan management',
                'status' => 'active',
                'company_id' => $company->id ?? 1,
                'branch_id' => $branch->id ?? null,
                'created_by' => $user->id ?? 1,
                'updated_by' => $user->id ?? 1,
            ],
            [
                'name' => 'Early Repayment Fee',
                'chart_account_id' => $chartAccountId,
                'fee_type' => 'percentage',
                'amount' => 3.00,
                'description' => 'Fee charged for early loan repayment',
                'status' => 'inactive',
                'company_id' => $company->id ?? 1,
                'branch_id' => $branch->id ?? null,
                'created_by' => $user->id ?? 1,
                'updated_by' => $user->id ?? 1,
            ],
            [
                'name' => 'Consultation Fee',
                'chart_account_id' => $chartAccountId,
                'fee_type' => 'fixed',
                'amount' => 10000.00,
                'description' => 'Financial consultation and advisory services fee',
                'status' => 'active',
                'company_id' => $company->id ?? 1,
                'branch_id' => $branch->id ?? null,
                'created_by' => $user->id ?? 1,
                'updated_by' => $user->id ?? 1,
            ],
        ];

        // Create fees
        foreach ($fees as $feeData) {
            Fee::create($feeData);
        }

        $this->command->info('Fees seeded successfully!');
    }
}
