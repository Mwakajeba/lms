<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accountClasses = [
            [
                'name' => 'Assets',
                'range_from' => 1000,
                'range_to' => 1999,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Liabilities',
                'range_from' => 2000,
                'range_to' => 2999,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Revenue',
                'range_from' => 4000,
                'range_to' =>4999,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Expenses',
                'range_from' => 5000,
                'range_to' => 5999,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Equity',
                'range_from' => 3000,
                'range_to' => 3999,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($accountClasses as $accountClass) {
            DB::table('account_class')->insertOrIgnore($accountClass);
        }
    }
}
