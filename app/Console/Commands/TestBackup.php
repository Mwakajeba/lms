<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BackupService;
use App\Models\Company;
use App\Models\User;

class TestBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:test {type=database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test backup functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->argument('type');
        
        // Set up a test environment
        $company = Company::first();
        if (!$company) {
            $this->error('No company found. Please run seeders first.');
            return 1;
        }

        // Set current company context
        config(['app.current_company_id' => $company->id]);
        
        // Set authenticated user context
        $user = User::first();
        if ($user) {
            auth()->login($user);
        }

        $this->info("Testing {$type} backup...");
        
        try {
            $backupService = new BackupService();
            
            switch ($type) {
                case 'database':
                    $backup = $backupService->createDatabaseBackup('Test database backup');
                    break;
                case 'files':
                    $backup = $backupService->createFilesBackup('Test files backup');
                    break;
                case 'full':
                    $backup = $backupService->createFullBackup('Test full backup');
                    break;
                default:
                    $this->error('Invalid backup type. Use: database, files, or full');
                    return 1;
            }

            $this->info("✅ Backup created successfully!");
            $this->info("Name: {$backup->name}");
            $this->info("Type: {$backup->type}");
            $this->info("Size: {$backup->formatted_size}");
            $this->info("Status: {$backup->status}");
            $this->info("File: {$backup->file_path}");

            // Test statistics
            $stats = $backupService->getBackupStats();
            $this->info("\n📊 Backup Statistics:");
            $this->info("Total: {$stats['total']}");
            $this->info("Completed: {$stats['completed']}");
            $this->info("Failed: {$stats['failed']}");
            $this->info("Total Size: " . number_format($stats['total_size'] / 1024 / 1024, 2) . " MB");

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Backup failed: " . $e->getMessage());
            return 1;
        }
    }
}
