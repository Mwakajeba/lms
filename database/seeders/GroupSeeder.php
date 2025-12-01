<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first branch and admin user
        $branch = Branch::first();
        $adminUser = User::where('role', 'admin')->first();

        if (!$branch) {
            $this->command->warn('No branch found. Please seed branches first.');
            return;
        }

        if (!$adminUser) {
            $this->command->warn('No admin user found. Please seed users first.');
            return;
        }

        // Check if Individual group already exists
        if (Group::where('name', 'Individual')->exists()) {
            $this->command->info('Individual group already exists. Skipping...');
            return;
        }

        // Create Individual group
        Group::create([
            'name' => 'Individual',
            'loan_officer' => $adminUser->id,
            'branch_id' => $branch->id,
            'minimum_members' => 1000000,
            'maximum_members' => 1000000,
            'group_leader' => null, // No specific leader for individual group
            'meeting_day' => null, // No meetings for individual customers
            'meeting_time' => null,
        ]);

        $this->command->info('Individual group created successfully!');
    }
} 