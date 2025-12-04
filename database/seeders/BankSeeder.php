<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ChartAccount;
use App\Models\BankAccount;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bankAccounts = [
            [
                'account_code' => '1001',
                'name' => 'CRDB Bank',
                'account_number' => 'CRDB-001',
            ],
            [
                'account_code' => '1022',
                'name' => 'NMB Bank',
                'account_number' => 'NMB-001',
            ],
            [
                'account_code' => '1023',
                'name' => 'Cash Account',
                'account_number' => 'CASH-001',
            ],
        ];

        foreach ($bankAccounts as $bankAccountData) {
            $chartAccount = ChartAccount::where('account_code', $bankAccountData['account_code'])->first();

            if ($chartAccount) {
                BankAccount::updateOrCreate(
                    ['chart_account_id' => $chartAccount->id],
                    [
                        'name' => $bankAccountData['name'],
                        'account_number' => $bankAccountData['account_number'],
                    ]
                );
            }
        }

        $this->command->info('Bank accounts seeded successfully!');
    }
}
