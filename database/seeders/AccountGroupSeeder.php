<?php

namespace Database\Seeders;

use App\Models\AccountClass;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class AccountGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('account_class_groups')->insert([
            [
                'class_id' => 1,
                'company_id' => 1,
                'group_code' => '1000',
                'name' => 'Cash and Bank',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'class_id' => 1,
                'company_id' => 1,
                'group_code' => '1100',
                'name' => 'Loan Receivables',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'class_id' => 1,
                'company_id' => 1,
                'group_code' => '1200',
                'name' => 'Interest Receivables',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'class_id' => 1,
                'company_id' => 1,
                'group_code' => '1300',
                'name' => 'Other Receivables',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'class_id' => 2,
                'company_id' => 1,
                'group_code' => '2000',
                'name' => 'Cash Deposit',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'class_id' => 2,
                'company_id' => 1,
                'group_code' => '2100',
                'name' => 'Other payables',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'class_id' => 5,
                'company_id' => 1,
                'group_code' => '3000',
                'name' => 'Retained Earnings',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'class_id' => 5,
                'company_id' => 1,
                'group_code' => '3100',
                'name' => 'Business Capital',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'class_id' => 3,
                'company_id' => 1,
                'group_code' => '4000',
                'name' => 'Interest Income',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'class_id' => 3,
                'company_id' => 1,
                'group_code' => '4100',
                'name' => 'Other Income',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'class_id' => 4,
                'company_id' => 1,
                'group_code' => '5000',
                'name' => 'Loan Loss Provision',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'class_id' => 4,
                'company_id' => 1,
                'group_code' => '5100',
                'name' => 'Operating Expenses',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'class_id' => 4,
                'company_id' => 1,
                'group_code' => '5200',
                'name' => 'Administrative Expenses',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'class_id' => 4,
                'company_id' => 1,
                'group_code' => '5300',
                'name' => 'Interest Expenses',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'class_id' => 4,
                'company_id' => 1,
                'group_code' => '5400',
                'name' => 'Finance Expenses',
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ]);
    }
}
