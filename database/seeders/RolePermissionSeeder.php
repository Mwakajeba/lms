<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Define permissions based on menu structure from MenuSeeder
        $permissions = [
            // Dashboard
            'view dashboard',
            'view financial reports',
            'view charges',
            'view journals',
            'view payments',
            'view receipts',
            'view loans',
            'view graphs',
            'view recent activities',

            // Settings
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
            'assign branches',

            // Customers
            'view customers',
            'create customer',
            'edit customer',
            'delete customer',
            'view customer profile',
            'manage customer documents',
            'view customer history',
            'approve customer registration',

            // Loan Management
            'view loan products',
            'create loan product',
            'edit loan product',
            'delete loan product',
            'view loan product details',
            'deactivate loan product',
            'manage loan products',
            'view completed loans',

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

            // Cash Collaterals
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
            'print cash collateral transactions',

            // Accounting
            'view account class group',
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
            'change supplier status',
            'manage supplier documents',

            'view journals',
            'create journal',
            'edit journal',
            'delete journal',
            'view journal details',
            'create journal entries',
            'edit journal entries',
            'delete journal entries',
            'approve journal entry',
            'reject journal entry',
            'post journal entry',
            'reverse journal entry',

            'view payment vouchers',
            'create payment voucher',
            'edit payment voucher',
            'delete payment voucher',
            'view payment voucher details',
            'approve payment voucher',
            'reject payment voucher',

            'view receipt vouchers',
            'create receipt voucher',
            'edit receipt voucher',
            'delete receipt voucher',
            'view receipt voucher details',
            'approve receipt voucher',
            'reject receipt voucher',

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

            // General Accounting
            'view accounting',
            'view general ledger',
            'manage financial year',
            'close accounting period',
            'delete transaction',
            'edit transaction',

            // Reports
            'view loan portfolio report',
            'view loan performance report',
            'view loan delinquency report',
            'view loan disbursement report',
            'view loan repayments report',
            'view loan aging report',
            'view loan aging installment report',
            'view loan outstanding report',
            'view loan arrears report',
            'view loan expected vs collected report',
            'view loan portfolio at risk report',
            'view loan internal portfolio analysis report',
            'view loan non performing loan report',
        // Accounting report item-level permissions
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
            'view reports',
            'generate reports',
            'export reports',
            'view accounting reports',
            'view loan reports',
            'view loans report', // permission for loans.blade.php
            'view customer reports',
            'view financial statements',
            'view financial report summary',
            'view client reports',
            'view branch performance',

            // Chat
            'view chat',
            'send chat message',
            'view chat messages',

            // AI Assistant
            'use AI assistant',
            'view AI assistant',

            // Analytics & Dashboard
            'view analytics',
            'view statistics',
            'view kpi reports',

            // Menu Management
            'view menus',
            'manage menus',
            'assign menu permissions',

            // User & Staff Management
            'view users',
            'create user',
            'edit user',
            'delete user',
            'assign roles',
            'view user profile',
            'change user status',
            'manage staff',

            // Company & Branch Management
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

            // Collections & Payments
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

            // Accounting & Financial
            'view accounting',
            'create journal entries',
            'edit journal entries',
            'delete journal entries',
            'view chart of accounts',
            'manage chart of accounts',
            'view bank accounts',
            'manage bank accounts',
            'view bank reconciliation',
            'perform bank reconciliation',
            'view general ledger',
            'manage financial year',
            'close accounting period',

            // Savings & Deposits
            'view savings accounts',
            'create savings account',
            'edit savings account',
            'delete savings account',
            'process deposits',
            'process withdrawals',
            'calculate interest on savings',
            'manage savings fees',
            'view savings history',

            // Reports & Analytics
            'view reports',
            'generate reports',
            'export reports',
            'view loan portfolio report',
            'view collection report',
            'view delinquency report',
            'view financial statements',
            'view FINANCIAL REPORT SUMMARY',
            'view client reports',
            'view branch performance',
            'view staff performance',
            'view audit reports',
            'view compliance reports',

            // Risk Management
            'view risk assessment',
            'create risk assessment',
            'manage loan limits',
            'view credit scores',
            'manage collateral',
            'manage loan guarantees',

            // Settings & Configuration
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
            'manage campany setting',
            'delete role',
            'edit role',
            'view role',
            'create role',
            'create permission',
            'view charges',


            // AI Assistant
            'use AI assistant',
            'view AI assistant',

            // Dashboard & Analytics
            'view dashboard',
            'view analytics',
            'view statistics',
            'view kpi reports',

            // Menu Management
            'view menus',
            'manage menus',
            'assign menu permissions',
            'view logs activity',

            //bank accounts
            'view bank accounts',
            'create bank account',
            'edit bank account',
            'delete bank account',
            'view bank account details',
            'manage bank account transactions',

            ////CASH COLLATERAL PERMISSION////
            'delete transaction',
            'edit transaction',
            'deposit cash collateral',
            'withdraw cash collateral',
            'edit cash collateral',
            'delete cash collateral',
            'print cash collateral transations',
            'view cash collaterals',
            'create cash collateral',

            ///group permission

            'view groups',
            'create group',
            'delete group',
            'edit group',
            'view group details',

            // Subscription Management
            'manage subscription',
            'view subscription',
            'create subscription',
            'edit subscription',
            'cancel subscription',
            'upgrade subscription',
            'downgrade subscription',
            'view billing history',
            'manage billing information',

            // Payment Voucher Approval Process
            'manage payment voucher approval',
            'view payment voucher approval',
            'create payment voucher approval',
            'edit payment voucher approval',
            'delete payment voucher approval',
            'configure approval workflow',
            'set approval thresholds',
            'assign approvers',
            'view approval history',
            'manage approval levels',
        ];

        // Create or update permissions
        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web'
            ], [
                'permission_group_id' => null // Will be set by PermissionGroupsSeeder
            ]);
        }

        // Create system roles with their permissions
        $this->createSystemRoles();

        // Assign admin role to user with ID 1 (if exists)
        $user = User::find(1);
        if ($user && !$user->hasRole('admin')) {
            $user->assignRole('admin');
        }
    }

    private function createSystemRoles()
    {
        // Super Admin Role - All permissions
        $superAdminRole = Role::firstOrCreate([
            'name' => 'super-admin',
            'guard_name' => 'web'
        ]);
        $superAdminRole->description = 'Full system access with all microfinance permissions';
        $superAdminRole->save();
        $superAdminRole->syncPermissions(Permission::all());

        // Admin Role - Company level admin
        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web'
        ]);
        $adminRole->description = 'Microfinance company administrator with full access';
        $adminRole->save();

        $adminRole->syncPermissions(Permission::all());

        // Manager Role - Branch level management
        $managerRole = Role::firstOrCreate([
            'name' => 'manager',
            'guard_name' => 'web'
        ]);
        $managerRole->description = 'Branch manager with operational microfinance access';
        $managerRole->save();

        $managerPermissions = [
            'view dashboard',
            'view users',
            'create user',
            'edit user',
            'view user profile',
            'manage staff',
            'view branches',
            'edit branch',
            'view customers',
            'create customer',
            'edit customer',
            'view customer profile',
            'manage customer documents',
            'view customer history',
            'approve customer registration',
            'view loans',
            'create loan',
            'edit loan',
            'approve loan',
            'reject loan',
            'disburse loan',
            'view loan details',
            'manage loan documents',
            'calculate loan interest',
            'generate loan schedule',
            'process loan payments',
            'manage loan fees',
            'view loan history',
            'view groups',
            'create group',
            'edit group',
            'view group details',
            'view collections',
            'create collection',
            'edit collection',
            'process payments',
            'record cash payments',
            'record bank transfers',
            'manage payment schedules',
            'view payment history',
            'generate receipts',
            'manage late payments',
            'process penalties',
            'view accounting',
            'create journal entries',
            'edit journal entries',
            'view chart accounts',
            'view bank accounts',
            'view bank reconciliation',
            'view general ledger',
            'view reports',
            'generate reports',
            'export reports',
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
            'view loan portfolio report',
            'view collection report',
            'view delinquency report',
            'view financial statements',
            'view financial report summary',
            'view client reports',
            'view branch performance',
            'view staff performance',
            'view settings',
            'view backup settings',
            'create backup',
            'use AI assistant',
            'view AI assistant',
            'view analytics',
            'view statistics',
            'view kpi reports',
            'view menus',
            'view chat',
            'manage subscription',
            'view subscription',
            'manage payment voucher approval',
            'view payment voucher approval',
            'configure approval workflow',
            'set approval thresholds',
            'assign approvers',
            'view approval history'
        ];
        $managerRole->syncPermissions($managerPermissions);

        // User Role - Standard user
        $userRole = Role::firstOrCreate([
            'name' => 'user',
            'guard_name' => 'web'
        ]);
        $userRole->description = 'Standard microfinance user with basic access';
        $userRole->save();

        $userPermissions = [
            'view dashboard',
            'view users',
            'view user profile',
            'view branches',
            'view customers',
            'view customer profile',
            'view customer history',
            'view loans',
            'view loan details',
            'view loan history',
            'view groups',
            'view group details',
            'view collections',
            'view payment history',
            'view accounting',
            'create journal entries',
            'view chart accounts',
            'view bank accounts',
            'view settings',
            'use AI assistant',
            'view AI assistant',
            'view reports',
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
            'view loan portfolio report',
            'view collection report',
            'view customer reports',
            'view statistics',
            'view menus',
            'view chat',
            'view subscription',
            'view payment voucher approval'
        ];
        $userRole->syncPermissions($userPermissions);

        // Viewer Role - Read-only access
        $viewerRole = Role::firstOrCreate([
            'name' => 'viewer',
            'guard_name' => 'web'
        ]);
        $viewerRole->description = 'Read-only access to microfinance data';
        $viewerRole->save();

        $viewerPermissions = [
            'view dashboard',
            'view users',
            'view user profile',
            'view branches',
            'view customers',
            'view customer profile',
            'view customer history',
            'view loans',
            'view loan details',
            'view loan history',
            'view groups',
            'view group details',
            'view collections',
            'view payment history',
            'view accounting',
            'view chart accounts',
            'view bank accounts',
            'view settings',
            'view AI assistant',
            'view reports',
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
            'view loan portfolio report',
            'view collection report',
            'view customer reports',
            'view statistics',
            'view menus',
            'view chat'
        ];
        $viewerRole->syncPermissions($viewerPermissions);
    }
}
