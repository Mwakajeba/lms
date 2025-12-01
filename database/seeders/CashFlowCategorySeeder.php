<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CashFlowCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cashFlowCategories = [
            [
                'name' => 'Operating Activities',
                'description' => 'Cash flows from operating activities of the business',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Investing Activities',
                'description' => 'Cash flows from investing activities of the business',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Financial Activities',
                'description' => 'Cash flows from financing activities of the business',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cash and Cash Equivalent',
                'description' => 'Cash and cash equivalent balances',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($cashFlowCategories as $category) {
            DB::table('cash_flow_categories')->insertOrIgnore($category);
        }
    }
}
