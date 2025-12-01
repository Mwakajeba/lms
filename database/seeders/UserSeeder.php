<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Check if any user already exists
        if (User::exists()) {
            $this->command->warn('Users already exist. Skipping seeding.');
            return;
        }
        // Get all branches
        $branches = Branch::all();

        if ($branches->isEmpty()) {
            $this->command->warn('No branches found. Seed branches first.');
            return;
        }

        // Seed one user per branch - first user as super-admin, others as admin
        foreach ($branches as $index => $branch) {
            // First user is super-admin, others are admin
            $role = $index === 0 ? 'super-admin' : 'admin';
            $name = $index === 0 ? 'Julius Mwakajeba (Super Admin)' : 'Julius Mwakajeba ' . $index;

            $user = User::create([
                'name' => $name,
                'phone' => '255655577803' . $index,
                'email' => 'admin' . $index . '@safco.com',
                'password' => Hash::make('12345'),
                'branch_id' => $branch->id,
                'company_id' => $branch->company_id,
                'role' => $role,
                'is_active' => 'yes',
                'sms_verification_code' => '654321',
                'sms_verified_at' => now(),
            ]);

            // Assign appropriate role using Spatie permissions
            $user->assignRole($role);
        }
    }
}
