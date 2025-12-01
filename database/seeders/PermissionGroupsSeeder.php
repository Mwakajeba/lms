<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\PermissionGroup;

class PermissionGroupsSeeder extends Seeder
{
    public function run()
    {
        // Create permission groups based on menu structure
        $groups = [
            [
                'name' => 'dashboard',
                'display_name' => 'Dashboard',
                'description' => 'Dashboard and overview permissions',
                'color' => '#007bff',
                'icon' => 'bx bx-home',
                'sort_order' => 1,
                'is_active' => true
            ],
            [
                'name' => 'settings',
                'display_name' => 'Settings',
                'description' => 'System settings and configuration permissions',
                'color' => '#6c757d',
                'icon' => 'bx bx-cog',
                'sort_order' => 2,
                'is_active' => true
            ],
            [
                'name' => 'customers',
                'display_name' => 'Customers',
                'description' => 'Customer management permissions',
                'color' => '#28a745',
                'icon' => 'bx bx-group',
                'sort_order' => 3,
                'is_active' => true
            ],
            [
                'name' => 'loan_management',
                'display_name' => 'Loan Management',
                'description' => 'Loan products, groups, and loan processing permissions',
                'color' => '#ffc107',
                'icon' => 'bx bx-money',
                'sort_order' => 4,
                'is_active' => true
            ],
            [
                'name' => 'cash_collaterals',
                'display_name' => 'Cash Collaterals',
                'description' => 'Cash collateral management permissions',
                'color' => '#17a2b8',
                'icon' => 'bx bx-wallet',
                'sort_order' => 5,
                'is_active' => true
            ],
            [
                'name' => 'accounting',
                'display_name' => 'Accounting',
                'description' => 'Accounting and financial management permissions',
                'color' => '#dc3545',
                'icon' => 'bx bx-calculator',
                'sort_order' => 6,
                'is_active' => true
            ],
            [
                'name' => 'reports',
                'display_name' => 'Reports',
                'description' => 'Reporting and analytics permissions',
                'color' => '#6610f2',
                'icon' => 'bx bx-bar-chart-alt-2',
                'sort_order' => 7,
                'is_active' => true
            ],
            [
                'name' => 'chat',
                'display_name' => 'Chat',
                'description' => 'Chat and communication permissions',
                'color' => '#20c997',
                'icon' => 'bx bx-message-square-dots',
                'sort_order' => 8,
                'is_active' => true
            ]
        ];

        // Create permission groups
        foreach ($groups as $groupData) {
            PermissionGroup::firstOrCreate(
                ['name' => $groupData['name']],
                $groupData
            );
        }

        // Define permission groups mapping based on menu structure
        $groupMapping = [
            'dashboard' => [
                'view dashboard',
                'view financial report',
                'view charges',
                'view journals',
                'view payments',
                'view receipts',
                'view loans',
                'view graphs',
                'view recent activities'
            ],

            'settings' => [
                'view settings',
                'edit settings',
                'manage system settings',
                'view system configurations',
                'edit system configurations',
                'manage system configurations',
                'view system config',
                'edit system config',
                'manage system config',
                'manage interest rates',
                'manage fee setting',
                'manage role & permission',
                'manage penalty setting',
                'manage payment terms',
                'view backup settings',
                'manage filetype setting',
                'create backup',
                'restore backup',
                'delete backup',
                'manage user setting',
                'manage branch setting',
                'manage company setting',
                'delete role',
                'edit role',
                'view role',
                'create role',
                'create permission',
                'view permission groups',
                'create permission group',
                'edit permission group',
                'delete permission group',
                'view logs activity',
                'view general ledger',
                'manage financial year',
                'close accounting period',
                'delete transaction',
                'edit transaction',
                'view transaction reports',
                'view loan portfolio report',
                'view collection report',
                'view delinquency report',
                'view financial statements',
                'view financial report summary',
                'view client reports',
                'view branch performance',
                'view staff performance',
                'view audit reports',
                'view compliance reports',
                'send chat message',
                'view chat messages',
                'use AI assistant',
                'view AI assistant',
                'view analytics',
                'view statistics',
                'view kpi reports',
                'view menus',
                'manage menus',
                'assign menu permissions',
                'view users',
                'create user',
                'edit user',
                'delete user',
                'assign roles',
                'view user profile',
                'change user status',
                'manage staff',
                'view companies',
                'create company',
                'edit company',
                'delete company',
                'manage company settings',
                'view branches',
                'create branch',
                'edit branch',
                'delete branch',
                'assign users to branches',
                'view collections',
                'create collection',
                'edit collection',
                'delete collection',
                'process payments',
                'record cash payments',
                'record bank transfers',
                'manage payment schedules',
                'view payment history',
                'generate receipts',
                'manage late payments',
                'process penalties',
                'view chart of accounts',
                'view savings accounts',
                'create savings account',
                'edit savings account',
                'delete savings account',
                'process deposits',
                'process withdrawals',
                'calculate interest on savings',
                'manage savings fees',
                'view savings history',
                'view risk assessment',
                'create risk assessment',
                'edit risk assessment',
                'manage loan limits',
                'view credit scores',
                'manage collateral',
                'view insurance policies',
                'manage loan guarantees',
                'manage campany setting',
                'view charges',
                'print cash collateral transations'
            ],

            'customers' => [
                'view customers',
                'create customer',
                'edit customer',
                'delete customer',
                'view customer profile',
                'manage customer documents',
                'view customer history',
                'approve customer registration',
                'view borrowers',
                'create borrower',
                'edit borrower',
                'delete borrower',
                'view borrower profile',
                'view guarantors',
                'create guarantor',
                'edit guarantor',
                'delete guarantor',
                'view guarantor profile'
            ],

            'loan_management' => [
                'view loan products',
                'create loan product',
                'edit loan product',
                'delete loan product',
                'view loan product details',
                'deactivate loan product',
                'manage loan products',
                'view groups',
                'create group',
                'edit group',
                'delete group',
                'view group details',
                'manage group payments',
                'view loans',
                'create loan',
                'edit loan',
                'delete loan',
                'view loan details',
                'manage loan documents',
                'view loan documents',
                'calculate loan interest',
                'generate loan schedule',
                'process loan payments',
                'manage loan fees',
                'view loan history',
                'view checked loans',
                'view applied loans',
                'view approved loans',
                'view authorized loans',
                'view defaulted loans',
                'view rejected loans',
                'remove guarantor',
                'add guarantor',
                'default loan',
                'approve loan',
                'reject loan',
                'disburse loan',
                'view completed loans'
            ],

            'cash_collaterals' => [
                'view cash collateral types',
                'create cash collateral type',
                'edit cash collateral type',
                'delete cash collateral type',
                'view cash collateral type details',
                'view cash collaterals',
                'create cash collateral',
                'edit cash collateral',
                'delete cash collateral',
                'view cash collateral details',
                'deposit cash collateral',
                'withdraw cash collateral',
                'print cash collateral transactions'
            ],

            'accounting' => [
                'view account class groups',
                'create account class group',
                'edit account class group',
                'delete account class group',
                'view account class group details',
                'view chart accounts',
                'create chart account',
                'edit chart account',
                'delete chart account',
                'view chart account details',
                'manage chart of accounts',
                'view suppliers',
                'create supplier',
                'edit supplier',
                'delete supplier',
                'view supplier details',
                'view journals',
                'create journal',
                'edit journal',
                'delete journal',
                'view journal details',
                'create journal entries',
                'edit journal entries',
                'delete journal entries',
                'view payment vouchers',
                'create payment voucher',
                'edit payment voucher',
                'delete payment voucher',
                'view payment voucher details',
                'view receipt vouchers',
                'create receipt voucher',
                'edit receipt voucher',
                'delete receipt voucher',
                'view receipt voucher details',
                'view bank accounts',
                'create bank account',
                'edit bank account',
                'delete bank account',
                'view bank account details',
                'manage bank accounts',
                'manage bank account transactions',
                'view bank reconciliation',
                'create bank reconciliation',
                'edit bank reconciliation',
                'delete bank reconciliation',
                'view bank reconciliation details',
                'perform bank reconciliation',
                'view bill purchases',
                'create bill purchase',
                'edit bill purchase',
                'delete bill purchase',
                'view bill purchase details',
                'view budgets',
                'create budget',
                'edit budget',
                'delete budget',
                'view budget details',
                'view fees',
                'create fee',
                'edit fee',
                'delete fee',
                'view fee details',
                'view penalties',
                'create penalty',
                'edit penalty',
                'delete penalty',
                'view penalty details',
                'view accounting'
            ],

            'reports' => [
                'view reports',
                'generate reports',
                'export reports',
                'view financial reports',
                'view customer reports',
                'view loan reports',
                'view collection reports',
                'view accounting reports',
                'view loan portfolio report',
                'view loan performance report',
                'view loan delinquency report',
                'view loan disbursement report',
                'view loan repayments report',
                'view loan aging report',
                'view loan aging installment report',
                'view loan outstanding report',
                'view arrears',
                'view expected vs collected',
                'view portfolio at risk',
                'view non perfoming loans',        // Accounting report item-level permissions
                'view balance sheet report',
                'view trial balance report',
                'view income statement report',
                'view cash book report',
                'view cash flow report',
                'view general ledger report',
                'view expenses summary report',
                'view accounting notes report',
                'view changes in equity report',
                'view fees report',
                'view penalties report',
                'view other income report',
                'view budget report',
                'view bank reconciliation report',
            ],

            'chat' => [
                'view chat',
                'send messages',
                'view chat history',
                'manage chat settings'
            ]
        ];

        // Update permissions with their groups
        $updatedCount = 0;
        foreach ($groupMapping as $group => $permissionNames) {
            $permissionGroup = PermissionGroup::where('name', $group)->first();

            foreach ($permissionNames as $permissionName) {
                $permission = Permission::where('name', $permissionName)->first();
                if ($permission && $permissionGroup) {
                    $permission->update(['permission_group_id' => $permissionGroup->id]);
                    $updatedCount++;
                }
            }
        }

        // Set remaining permissions to 'settings' group
        $settingsGroup = PermissionGroup::where('name', 'settings')->first();
        if ($settingsGroup) {
            $remainingPermissions = Permission::whereNull('permission_group_id')->get();
            foreach ($remainingPermissions as $permission) {
                $permission->update(['permission_group_id' => $settingsGroup->id]);
                $updatedCount++;
            }
        }

        $this->command->info("Permission groups created and {$updatedCount} permissions assigned to groups.");
    }
}
