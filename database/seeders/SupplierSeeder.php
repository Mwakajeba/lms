<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Supplier;
use App\Models\Company;
use App\Models\Branch;
use App\Models\User;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first company and branch for seeding
        $company = Company::first();
        $branch = Branch::first();
        $user = User::first();

        if (!$company) {
            $this->command->warn('No company found. Please create a company first.');
            return;
        }

        $suppliers = [
            [
                'name' => 'ABC Electronics Ltd',
                'email' => 'info@abcelectronics.com',
                'phone' => '255-123-456-789',
                'address' => '123 Electronics Street, Dar es Salaam',
                'region' => 'Dar es Salaam',
                'company_registration_name' => 'ABC Electronics Limited',
                'tin_number' => '123-456-789',
                'vat_number' => 'VAT-123-456',
                'bank_name' => 'CRDB Bank',
                'bank_account_number' => '1234567890',
                'account_name' => 'ABC Electronics Ltd',
                'products_or_services' => 'Electronic components, computers, laptops, mobile phones, and accessories',
                'status' => 'active',
                'company_id' => $company->id,
                'branch_id' => $branch ? $branch->id : null,
                'created_by' => $user ? $user->id : null,
            ],
            [
                'name' => 'Tanzania Office Supplies',
                'email' => 'sales@tanzaniaofficesupplies.co.tz',
                'phone' => '255-987-654-321',
                'address' => '456 Business Avenue, Arusha',
                'region' => 'Arusha',
                'company_registration_name' => 'Tanzania Office Supplies Company',
                'tin_number' => '987-654-321',
                'vat_number' => 'VAT-987-654',
                'bank_name' => 'NMB Bank',
                'bank_account_number' => '0987654321',
                'account_name' => 'Tanzania Office Supplies',
                'products_or_services' => 'Office furniture, stationery, printing supplies, and office equipment',
                'status' => 'active',
                'company_id' => $company->id,
                'branch_id' => $branch ? $branch->id : null,
                'created_by' => $user ? $user->id : null,
            ],
            [
                'name' => 'Mwanza Construction Materials',
                'email' => 'info@mwanza-construction.co.tz',
                'phone' => '255-555-123-456',
                'address' => '789 Industrial Road, Mwanza',
                'region' => 'Mwanza',
                'company_registration_name' => 'Mwanza Construction Materials Ltd',
                'tin_number' => '555-123-456',
                'vat_number' => 'VAT-555-123',
                'bank_name' => 'NBC Bank',
                'bank_account_number' => '5551234567',
                'account_name' => 'Mwanza Construction Materials',
                'products_or_services' => 'Cement, steel, bricks, sand, gravel, and construction tools',
                'status' => 'active',
                'company_id' => $company->id,
                'branch_id' => $branch ? $branch->id : null,
                'created_by' => $user ? $user->id : null,
            ],
            [
                'name' => 'Dodoma Agricultural Supplies',
                'email' => 'contact@dodoma-agri.co.tz',
                'phone' => '255-777-888-999',
                'address' => '321 Farm Road, Dodoma',
                'region' => 'Dodoma',
                'company_registration_name' => 'Dodoma Agricultural Supplies Company',
                'tin_number' => '777-888-999',
                'vat_number' => 'VAT-777-888',
                'bank_name' => 'CRDB Bank',
                'bank_account_number' => '7778889990',
                'account_name' => 'Dodoma Agricultural Supplies',
                'products_or_services' => 'Fertilizers, seeds, pesticides, farming equipment, and irrigation systems',
                'status' => 'inactive',
                'company_id' => $company->id,
                'branch_id' => $branch ? $branch->id : null,
                'created_by' => $user ? $user->id : null,
            ],
            [
                'name' => 'Zanzibar Tourism Services',
                'email' => 'info@zanzibar-tourism.com',
                'phone' => '255-111-222-333',
                'address' => '654 Beach Road, Stone Town, Zanzibar',
                'region' => 'Zanzibar',
                'company_registration_name' => 'Zanzibar Tourism Services Ltd',
                'tin_number' => '111-222-333',
                'vat_number' => 'VAT-111-222',
                'bank_name' => 'Amana Bank',
                'bank_account_number' => '1112223334',
                'account_name' => 'Zanzibar Tourism Services',
                'products_or_services' => 'Hotel supplies, restaurant equipment, tour guide services, and transportation',
                'status' => 'active',
                'company_id' => $company->id,
                'branch_id' => $branch ? $branch->id : null,
                'created_by' => $user ? $user->id : null,
            ],
            [
                'name' => 'Mbeya Medical Supplies',
                'email' => 'sales@mbeya-medical.co.tz',
                'phone' => '255-444-555-666',
                'address' => '987 Health Street, Mbeya',
                'region' => 'Mbeya',
                'company_registration_name' => 'Mbeya Medical Supplies Company',
                'tin_number' => '444-555-666',
                'vat_number' => 'VAT-444-555',
                'bank_name' => 'CRDB Bank',
                'bank_account_number' => '4445556667',
                'account_name' => 'Mbeya Medical Supplies',
                'products_or_services' => 'Medical equipment, pharmaceuticals, hospital supplies, and laboratory equipment',
                'status' => 'blacklisted',
                'company_id' => $company->id,
                'branch_id' => $branch ? $branch->id : null,
                'created_by' => $user ? $user->id : null,
            ],
        ];

        foreach ($suppliers as $supplierData) {
            Supplier::create($supplierData);
        }

        $this->command->info('Suppliers seeded successfully!');
    }
}
