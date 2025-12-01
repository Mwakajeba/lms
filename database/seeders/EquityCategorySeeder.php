<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EquityCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $equityCategories = [
            [
                'name' => 'Issuance of Shares',
                'description' => 'Equity transactions related to share issuance',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Dividends Paid',
                'description' => 'Equity transactions related to dividend payments',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Retained Earnings',
                'description' => 'Equity transactions related to retained earnings',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Profit and Loss',
                'description' => 'Equity transactions related to profit and loss',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($equityCategories as $category) {
            DB::table('equity_categories')->insertOrIgnore($category);
        }
    }
}
