<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\Company;

class BranchSeeder extends Seeder
{
    public function run()
    {
        $company = Company::where('name', 'SAFCO FINTECH LTD')->first();

        if ($company) {
            Branch::create([
                'company_id' => $company->id,
                'name' => 'Main Branch',
                'email' => 'main@safco.com',
                'phone' => '255754111111',
                'address' => 'City Center, Dar es Salaam',
            ]);

            Branch::create([
                'company_id' => $company->id,
                'name' => 'Mwanza Branch',
                'email' => 'mwanza@safco.com',
                'phone' => '255754222222',
                'address' => 'Rock City Mall, Mwanza',
            ]);
        }
    }
}
