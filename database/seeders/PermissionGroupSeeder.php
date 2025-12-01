<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PermissionGroup;

class PermissionGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groups = [
            [
                'name' => 'dashboard',
                'display_name' => 'Dashboard',
                'description' => 'Permissions related to dashboard access and main overview',
                'color' => '#198754',
                'icon' => 'bx bx-home',
                'sort_order' => 1,
            ],
            [
                'name' => 'settings',
                'display_name' => 'Settings',
                'description' => 'Permissions related to system settings and configuration',
                'color' => '#6c757d',
                'icon' => 'bx bx-cog',
                'sort_order' => 2,
            ],
            [
                'name' => 'customers',
                'display_name' => 'Customers',
                'description' => 'Permissions related to customer management and profiles',
                'color' => '#28a745',
                'icon' => 'bx bx-group',
                'sort_order' => 3,
            ],
            [
                'name' => 'loan_management',
                'display_name' => 'Loan Management',
                'description' => 'Permissions related to loan applications, approvals, and management',
                'color' => '#ffc107',
                'icon' => 'bx bx-credit-card',
                'sort_order' => 4,
            ],
            [
                'name' => 'cash_collaterals',
                'display_name' => 'Cash Collaterals',
                'description' => 'Permissions related to cash collateral management and transactions',
                'color' => '#6f42c1',
                'icon' => 'bx bx-outline',
                'sort_order' => 5,
            ],
            [
                'name' => 'accounting',
                'display_name' => 'Accounting',
                'description' => 'Permissions related to accounting, journals, and financial management',
                'color' => '#20c997',
                'icon' => 'bx bx-calculator',
                'sort_order' => 6,
            ],
            [
                'name' => 'reports',
                'display_name' => 'Reports',
                'description' => 'Permissions related to reports, analytics, and data analysis',
                'color' => '#fd7e14',
                'icon' => 'bx bx-file',
                'sort_order' => 7,
            ],
            [
                'name' => 'chat',
                'display_name' => 'Chat',
                'description' => 'Permissions related to chat features and communication',
                'color' => '#e83e8c',
                'icon' => 'bx bx-message',
                'sort_order' => 8,
            ],
        ];

        foreach ($groups as $group) {
            PermissionGroup::firstOrCreate(
                ['name' => $group['name']],
                $group
            );
        }
    }
}
