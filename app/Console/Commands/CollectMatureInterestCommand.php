<?php

namespace App\Console\Commands;

use App\Jobs\CollectMatureInterestJob;
use Illuminate\Console\Command;

class CollectMatureInterestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loans:collect-mature-interest {--force : Force run even if already processed today}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Collect mature interest from active loans and post to GL';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting mature interest collection...');

        try {
            // Dispatch the job
            CollectMatureInterestJob::dispatch();

            $this->info('Mature interest collection job has been dispatched successfully.');
            $this->info('Check the logs for detailed information about the process.');

        } catch (\Exception $e) {
            $this->error('Error dispatching mature interest collection job: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}