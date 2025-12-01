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

        // Get specific chart accounts for each fee
        $administrativeFeeAccount = ChartAccount::where('account_name', 'Administrative Fee')->first();
        $applicationFeeAccount = ChartAccount::where('account_name', 'Application Fee')->first();
        $consultationFeeAccount = ChartAccount::where('account_name', 'Consultation Fee')->first();
        $documentationFeeAccount = ChartAccount::where('account_name', 'Documentation Fee')->first();
        $earlyRepaymentFeeAccount = ChartAccount::where('account_name', 'Early Repayment Fee')->first();
        $insuranceFeeAccount = ChartAccount::where('account_name', 'Insurance Fee')->first();
        $latePaymentPenaltyAccount = ChartAccount::where('account_name', 'Late Payment Penalty')->first();
        $processingFeeAccount = ChartAccount::where('account_name', 'Processing Fee')->first();

        // Sample fee data with specific chart accounts
        $fees = [
            [
                'name' => 'Administrative Fee',
                'chart_account_id' => $administrativeFeeAccount->id ?? null,
                'fee_type' => 'fixed',
                'amount' => 2000.00,
                'deduction_criteria' => 'do_not_include_in_loan_schedule',
                'description' => 'Administrative handling fee for loan management',
                'status' => 'active',
                'company_id' => $company->id ?? 1,
                'branch_id' => $branch->id ?? 1,
                'created_by' => $user->id ?? 1,
                'updated_by' => $user->id ?? 1,
            ],
            [
                'name' => 'Application Fee',
                'chart_account_id' => $applicationFeeAccount->id ?? null,
                'fee_type' => 'fixed',
                'amount' => 5000.00,
                'deduction_criteria' => 'do_not_include_in_loan_schedule',
                'description' => 'One-time application processing fee for new loan applications',
                'status' => 'active',
                'company_id' => $company->id ?? 1,
                'branch_id' => $branch->id ?? 1,
                'created_by' => $user->id ?? 1,
                'updated_by' => $user->id ?? 1,
            ],
            [
                'name' => 'Consultation Fee',
                'chart_account_id' => $consultationFeeAccount->id ?? null,
                'fee_type' => 'fixed',
                'amount' => 10000.00,
                'deduction_criteria' => 'do_not_include_in_loan_schedule',
                'description' => 'Financial consultation and advisory services fee',
                'status' => 'active',
                'company_id' => $company->id ?? 1,
                'branch_id' => $branch->id ?? 1,
                'created_by' => $user->id ?? 1,
                'updated_by' => $user->id ?? 1,
            ],
            [
                'name' => 'Documentation Fee',
                'chart_account_id' => $documentationFeeAccount->id ?? null,
                'fee_type' => 'fixed',
                'amount' => 3000.00,
                'deduction_criteria' => 'do_not_include_in_loan_schedule',
                'description' => 'Fee for document preparation and processing',
                'status' => 'active',
                'company_id' => $company->id ?? 1,
                'branch_id' => $branch->id ?? 1,
                'created_by' => $user->id ?? 1,
                'updated_by' => $user->id ?? 1,
            ],
            [
                'name' => 'Early Repayment Fee',
                'chart_account_id' => $earlyRepaymentFeeAccount->id ?? null,
                'fee_type' => 'percentage',
                'amount' => 3.00,
                'deduction_criteria' => 'do_not_include_in_loan_schedule',
                'description' => 'Fee charged for early loan repayment',
                'status' => 'inactive',
                'company_id' => $company->id ?? 1,
                'branch_id' => $branch->id ?? 1,
                'created_by' => $user->id ?? 1,
                'updated_by' => $user->id ?? 1,
            ],
            [
                'name' => 'Insurance Fee',
                'chart_account_id' => $insuranceFeeAccount->id ?? null,
                'fee_type' => 'percentage',
                'amount' => 1.50,
                'deduction_criteria' => 'do_not_include_in_loan_schedule',
                'description' => 'Insurance coverage fee for loan protection',
                'status' => 'active',
                'company_id' => $company->id ?? 1,
                'branch_id' => $branch->id ?? 1,
                'created_by' => $user->id ?? 1,
                'updated_by' => $user->id ?? 1,
            ],
            [
                'name' => 'Late Payment Penalty',
                'chart_account_id' => $latePaymentPenaltyAccount->id ?? null,
                'fee_type' => 'percentage',
                'amount' => 5.00,
                'deduction_criteria' => 'do_not_include_in_loan_schedule',
                'description' => 'Penalty fee for late loan payments',
                'status' => 'active',
                'company_id' => $company->id ?? 1,
                'branch_id' => $branch->id ?? 1,
                'created_by' => $user->id ?? 1,
                'updated_by' => $user->id ?? 1,
            ],
            [
                'name' => 'Processing Fee',
                'chart_account_id' => $processingFeeAccount->id ?? null,
                'fee_type' => 'percentage',
                'amount' => 2.50,
                'deduction_criteria' => 'do_not_include_in_loan_schedule',
                'description' => 'Processing fee calculated as percentage of loan amount',
                'status' => 'active',
                'company_id' => $company->id ?? 1,
                'branch_id' => $branch->id ?? 1,
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
