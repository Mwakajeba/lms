<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class TestSystemConfigPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:system-config-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test system configuration permissions for admin users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing System Configuration Permissions...');
        $this->newLine();

        // Check if permissions exist
        $permissions = [
            'view system configurations',
            'edit system configurations', 
            'manage system configurations',
            'view system config',
            'edit system config',
            'manage system config',
            'view backup settings',
            'create backup',
            'restore backup',
            'delete backup'
        ];

        $this->info('Checking permissions exist:');
        foreach ($permissions as $permission) {
            $perm = Permission::where('name', $permission)->first();
            if ($perm) {
                $this->line("✓ {$permission}");
            } else {
                $this->error("✗ {$permission} - NOT FOUND");
            }
        }
        $this->newLine();

        // Check admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $this->info('Admin role found. Checking permissions:');
            $adminPermissions = $adminRole->permissions->pluck('name')->toArray();
            
            foreach ($permissions as $permission) {
                if (in_array($permission, $adminPermissions)) {
                    $this->line("✓ Admin has: {$permission}");
                } else {
                    $this->warn("⚠ Admin missing: {$permission}");
                }
            }
        } else {
            $this->error('Admin role not found!');
        }
        $this->newLine();

        // Check super-admin role
        $superAdminRole = Role::where('name', 'super-admin')->first();
        if ($superAdminRole) {
            $this->info('Super Admin role found. Checking permissions:');
            $superAdminPermissions = $superAdminRole->permissions->pluck('name')->toArray();
            
            foreach ($permissions as $permission) {
                if (in_array($permission, $superAdminPermissions)) {
                    $this->line("✓ Super Admin has: {$permission}");
                } else {
                    $this->warn("⚠ Super Admin missing: {$permission}");
                }
            }
        } else {
            $this->error('Super Admin role not found!');
        }
        $this->newLine();

        // Test with actual admin user
        $adminUser = User::whereHas('roles', function($query) {
            $query->where('name', 'admin');
        })->first();

        if ($adminUser) {
            $this->info("Testing with admin user: {$adminUser->name}");
            
            foreach ($permissions as $permission) {
                if ($adminUser->can($permission)) {
                    $this->line("✓ User can: {$permission}");
                } else {
                    $this->warn("⚠ User cannot: {$permission}");
                }
            }
        } else {
            $this->warn('No admin user found to test with.');
        }

        $this->newLine();
        $this->info('System Configuration Permissions Test Complete!');
        
        return Command::SUCCESS;
    }
}
