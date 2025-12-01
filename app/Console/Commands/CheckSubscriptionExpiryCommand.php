<?php

namespace App\Console\Commands;

use App\Jobs\CheckSubscriptionExpiryJob;
use Illuminate\Console\Command;

class CheckSubscriptionExpiryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription:check-expiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for expiring and expired subscriptions, send reminders and lock users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting subscription expiry check...');

        CheckSubscriptionExpiryJob::dispatch();

        $this->info('Subscription expiry check job dispatched successfully.');

        return 0;
    }
}