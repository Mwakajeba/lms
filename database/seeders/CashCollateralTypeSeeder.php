<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CashCollateralTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cashCollateralTypes = [
            [
                'name' => 'Customer Operation Account',
                'chart_account_id' => 8, // Customer operation account (2010)
                'description' => 'Customer Operation Account performing various customer operations and transaction management',
                'is_active' => true,
            ],
            [
                'name' => 'Savings Account',
                'chart_account_id' => 8, // Customer operation account (2010)
                'description' => 'Customer savings account for long-term deposits and savings management',
                'is_active' => true,
            ],
            [
                'name' => 'Fixed Deposit Account',
                'chart_account_id' => 8, // Customer operation account (2010)
                'description' => 'Fixed deposit account with predetermined interest rates and maturity periods',
                'is_active' => true,
            ],
            [
                'name' => 'Current Account',
                'chart_account_id' => 8, // Customer operation account (2010)
                'description' => 'Current account for day-to-day banking operations and transactions',
                'is_active' => true,
            ],
            [
                'name' => 'Security Deposit',
                'chart_account_id' => 8, // Customer operation account (2010)
                'description' => 'Security deposit account for loan collateral and guarantee purposes',
                'is_active' => true,
            ],
        ];

        foreach ($cashCollateralTypes as $type) {
            DB::table('cash_collateral_types')->updateOrInsert(
                ['name' => $type['name']],
                array_merge($type, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
