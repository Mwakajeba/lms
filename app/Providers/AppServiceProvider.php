<?php

namespace App\Providers;

use App\Jobs\CollectMatureInterestJob;
use Carbon\Carbon;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Run mature interest collection once per day on first user login
        Event::listen(Login::class, function () {
            Log::info('Login event triggered - checking mature interest job');
            try {
                $cacheKey = 'mature_interest_job_ran_' . Carbon::today()->toDateString();

                // Only run once per day; Cache::add sets the key if it does not exist
                $added = Cache::add($cacheKey, true, Carbon::now()->endOfDay());
                if (!$added) {
                    Log::info('Mature interest job already ran today, skipping');
                    return; // Already ran today
                }

                Log::info('Running CollectMatureInterestJob synchronously from login event (once per day)');
                // Run immediately so it processes on user login without needing a worker
                dispatch_sync(new CollectMatureInterestJob());
            } catch (\Throwable $e) {
                Log::error('Failed dispatching CollectMatureInterestJob on login: ' . $e->getMessage());
            }
        });
    }
}
