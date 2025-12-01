<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChartAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            // Cash and Bank (Group ID: 1)
            [
                'account_code' => '1001',
                'account_name' => 'CRDB Bank',
                'account_class_group_id' => 1,
                'has_cash_flow' => 1,
                'has_equity' => 0,
                'cash_flow_category_id' => 4,
                'equity_category_id' => null,
            ],
            [
                'account_code' => '1022',
                'account_name' => 'NMB Bank',
                'account_class_group_id' => 1,
                'has_cash_flow' => 1,
                'has_equity' => 0,
                'cash_flow_category_id' => 4,
                'equity_category_id' => null,
            ],
            [
                'account_code' => '1021',
                'account_name' => 'Equity Bank',
                'account_class_group_id' => 1,
                'has_cash_flow' => 1,
                'has_equity' => 0,
                'cash_flow_category_id' => 4,
                'equity_category_id' => null,
            ],
            [
                'account_code' => '1023',
                'account_name' => 'Cash account',
                'account_class_group_id' => 1,
                'has_cash_flow' => 1,
                'has_equity' => 0,
                'cash_flow_category_id' => 4,
                'equity_category_id' => null,
            ],

            // Loan Receivables (Group ID: 2)
            [
                'account_code' => '1500',
                'account_name' => 'Principal Receivable',
                'account_class_group_id' => 2,
                'has_cash_flow' => 1,
                'has_equity' => 0,
                'cash_flow_category_id' => 1,
                'equity_category_id' => null,
            ],

            // Interest Receivables (Group ID: 3)
            [
                'account_code' => '1002',
                'account_name' => 'interest receivable',
                'account_class_group_id' => 3,
                'has_cash_flow' => 1,
                'has_equity' => 0,
                'cash_flow_category_id' => 1,
                'equity_category_id' => null,
            ],

            // Other Receivables (Group ID: 4) - Penalty Receivable
            [
                'account_code' => '1003',
                'account_name' => 'Penalty Receivable',
                'account_class_group_id' => 4,
                'has_cash_flow' => 1,
                'has_equity' => 0,
                'cash_flow_category_id' => 1,
                'equity_category_id' => null,
            ],

            // Business Capital (Group ID: 8)
            [
                'account_code' => '3003',
                'account_name' => 'Share Capital',
                'account_class_group_id' => 8,
                'has_cash_flow' => 1,
                'has_equity' => 0,
                'cash_flow_category_id' => 1,
                'equity_category_id' => null,
            ],

            // Interest Income (Group ID: 9)
            [
                'account_code' => '4570',
                'account_name' => 'Interest income',
                'account_class_group_id' => 9,
                'has_cash_flow' => 1,
                'has_equity' => 0,
                'cash_flow_category_id' => 1,
                'equity_category_id' => null,
            ],

            // Other Income (Group ID: 10) - Penalty Income
            [
                'account_code' => '4002',
                'account_name' => 'Penalty Income',
                'account_class_group_id' => 10,
                'has_cash_flow' => 1,
                'has_equity' => 0,
                'cash_flow_category_id' => 1,
                'equity_category_id' => null,
            ],

            // Cash Deposit (Group ID: 5) - Customer operation account
            [
                'account_code' => '2010',
                'account_name' => 'Customer operation account',
                'account_class_group_id' => 5,
                'has_cash_flow' => 1,
                'has_equity' => 0,
                'cash_flow_category_id' => 1,
                'equity_category_id' => null,
            ],
        ];

        foreach ($accounts as $account) {
            DB::table('chart_accounts')->updateOrInsert(
                ['account_code' => $account['account_code']],
                array_merge($account, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
