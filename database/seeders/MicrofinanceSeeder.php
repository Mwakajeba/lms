<?php

namespace Database\Seeders;

use App\Models\Microfinance;
use Illuminate\Database\Seeder;

class MicrofinanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $microfinances = [
            [
                'name' => 'Tanzania Microfinance Bank',
                'email' => 'info@tmb.co.tz',
            ],
            [
                'name' => 'Akiba Commercial Bank',
                'email' => 'contact@akibabank.co.tz',
            ],
            [
                'name' => 'CRDB Bank Microfinance',
                'email' => 'microfinance@crdb.co.tz',
            ],
            [
                'name' => 'NMB Bank Microfinance',
                'email' => 'microfinance@nmb.co.tz',
            ],
            [
                'name' => 'Equity Bank Tanzania',
                'email' => 'info@equitybank.co.tz',
            ],
            [
                'name' => 'Exim Bank Tanzania',
                'email' => 'microfinance@eximbank.co.tz',
            ],
            [
                'name' => 'Stanbic Bank Tanzania',
                'email' => 'microfinance@stanbic.co.tz',
            ],
            [
                'name' => 'KCB Bank Tanzania',
                'email' => 'microfinance@kcb.co.tz',
            ],
            [
                'name' => 'Absa Bank Tanzania',
                'email' => 'microfinance@absa.co.tz',
            ],
            [
                'name' => 'Bank of Africa Tanzania',
                'email' => 'microfinance@boatanzania.co.tz',
            ],
        ];

        foreach ($microfinances as $microfinance) {
            Microfinance::updateOrCreate(
                ['email' => $microfinance['email']],
                $microfinance
            );
        }
    }
}
