<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SystemSetting;

class InitializeSystemSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:init-settings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize system settings with default values';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Initializing system settings...');

        try {
            // Initialize default settings
            SystemSetting::initializeDefaults();
            
            $this->info('System settings initialized successfully!');
            $this->info('Default security settings applied:');
            $this->info('- Session Lifetime: 120 minutes');
            $this->info('- Minimum Password Length: 8 characters');
            $this->info('- Require Special Characters: Yes');
            $this->info('- Require Numbers: Yes');
            $this->info('- Require Uppercase Letters: Yes');
            $this->info('- Login Attempts Limit: 5');
            $this->info('- Lockout Duration: 15 minutes');
            
        } catch (\Exception $e) {
            $this->error('Failed to initialize system settings: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
