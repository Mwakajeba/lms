<?php

namespace App\Providers;

use App\Jobs\CollectMatureInterestJob;
use App\Jobs\RepaymentReminderJob;
use App\Jobs\CheckSubscriptionExpiryJob;
use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class ScheduleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            // Schedule mature interest collection to run daily at midnight
            $schedule->job(new CollectMatureInterestJob())
                // ->dailyAt('08:00')
                // ->everyMinute()
                ->everySecond()
                ->withoutOverlapping()
                ->onOneServer()
                ->appendOutputTo(storage_path('logs/mature-interest-collection.log'));

            // Schedule repayment reminders to run daily at 08:00 AM
            $schedule->job(new RepaymentReminderJob())
                ->dailyAt('08:00')
                // ->everyMinute()
                ->withoutOverlapping()
                ->onOneServer()
                ->appendOutputTo(storage_path('logs/repayment-reminder.log'));

            // Schedule subscription expiry check to run every minute
            $schedule->job(new CheckSubscriptionExpiryJob())
                ->dailyAt('00:00')
                ->withoutOverlapping()
                ->onOneServer()
                ->appendOutputTo(storage_path('logs/subscription-expiry-check.log'));
        });
    }
}
