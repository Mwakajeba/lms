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

        // Define groups to create
        $groups = [
            [
                'name' => 'Individual',
                'minimum_members' => 1000000,
                'maximum_members' => 1000000,
                'meeting_day' => null,
                'meeting_time' => null,
                'description' => 'For individual loan customers'
            ],
            [
                'name' => 'Group Loans',
                'minimum_members' => 2,
                'maximum_members' => 50,
                'meeting_day' => 'Monday',
                'meeting_time' => '10:00',
                'description' => 'For group loan customers'
            ]
        ];

        foreach ($groups as $groupData) {
            // Check if group already exists
            if (Group::where('name', $groupData['name'])->exists()) {
                $this->command->info("{$groupData['name']} group already exists. Skipping...");
                continue;
            }

            // Create group
            Group::create([
                'name' => $groupData['name'],
                'loan_officer' => $adminUser->id,
                'branch_id' => $branch->id,
                'minimum_members' => $groupData['minimum_members'],
                'maximum_members' => $groupData['maximum_members'],
                'group_leader' => null,
                'meeting_day' => $groupData['meeting_day'],
                'meeting_time' => $groupData['meeting_time'],
            ]);

            $this->command->info("{$groupData['name']} group created successfully!");
        }
    }
} 