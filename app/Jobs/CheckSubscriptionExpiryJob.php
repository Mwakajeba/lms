<?php

namespace App\Jobs;

use App\Models\Subscription;
use App\Models\User;
use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class CheckSubscriptionExpiryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting subscription expiry check job');

        // Debug: Check total subscriptions
        $totalSubscriptions = Subscription::count();
        Log::info("Total subscriptions in database: {$totalSubscriptions}");

        // Debug: Check active subscriptions
        $activeSubscriptions = Subscription::where('status', 'active')->count();
        Log::info("Active subscriptions: {$activeSubscriptions}");

        // Debug: Check paid subscriptions
        $paidSubscriptions = Subscription::where('payment_status', 'paid')->count();
        Log::info("Paid subscriptions: {$paidSubscriptions}");

        // Check for subscriptions expiring in 5 days
        $this->checkExpiringSubscriptions();

        // Check for expired subscriptions
        $this->checkExpiredSubscriptions();

        Log::info('Subscription expiry check job completed');
    }

    /**
     * Check for subscriptions expiring in 5 days and send reminders
     */
    private function checkExpiringSubscriptions(): void
    {
        // Check ALL subscriptions expiring in 5 days (regardless of current status)
        $expiringSubscriptions = Subscription::where('end_date', '<=', Carbon::now()->addDays(5))
            ->where('end_date', '>', Carbon::now()) // Not yet expired
            ->where('payment_status', 'paid')
            ->get();

        Log::info("Found {$expiringSubscriptions->count()} subscriptions expiring in 5 days");

        foreach ($expiringSubscriptions as $subscription) {
            // Check if reminder was already sent today
            if (
                $subscription->last_reminder_sent &&
                $subscription->last_reminder_sent->isToday()
            ) {
                continue;
            }

            // Send reminder to company admins
            $this->sendExpiryReminder($subscription);

            // Update reminder count and last sent date
            $subscription->update([
                'reminder_count' => $subscription->reminder_count + 1,
                'last_reminder_sent' => Carbon::now(),
            ]);

            Log::info("Expiry reminder sent for subscription ID: {$subscription->id}");
        }
    }

    /**
     * Check for expired subscriptions and lock users
     */
    private function checkExpiredSubscriptions(): void
    {
        // Check ALL subscriptions where end_date has passed (regardless of current status)
        $expiredSubscriptions = Subscription::where('end_date', '<', Carbon::now())
            ->where('payment_status', 'paid') // Only check paid subscriptions
            ->get();

        Log::info("Found {$expiredSubscriptions->count()} subscriptions with expired end dates");

        foreach ($expiredSubscriptions as $subscription) {
            // Mark subscription as expired if it's not already
            if ($subscription->status !== 'expired') {
                $subscription->markAsExpired();
                Log::info("Marked subscription ID {$subscription->id} as expired");
            }

            // Check if users are currently active and suspend them
            $activeUsers = User::where('company_id', $subscription->company_id)
                ->where('status', 'active')
                ->whereDoesntHave('roles', function ($query) {
                    $query->where('name', 'super-admin');
                })
                ->get();

            if ($activeUsers->count() > 0) {
                foreach ($activeUsers as $user) {
                    $user->update([
                        'status' => 'suspended',
                        'is_active' => 'no',
                    ]);
                }

                Log::info("Suspended {$activeUsers->count()} active users for company ID: {$subscription->company_id}");
            }

            // Send expiry notification
            $this->sendExpiryNotification($subscription);

            Log::info("Processed expired subscription for company ID: {$subscription->company_id}");
        }
    }

    /**
     * Suspend all users for a company when subscription expires (except super-admin)
     */
    private function lockCompanyUsers(int $companyId): void
    {
        $users = User::where('company_id', $companyId)
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', 'super-admin');
            })
            ->get();

        foreach ($users as $user) {
            $user->update([
                'status' => 'suspended',
                'is_active' => 'no',
            ]);
        }

        Log::info("Suspended {$users->count()} users for company ID: {$companyId} (super-admin users excluded)");
    }

    /**
     * Unlock all users for a company (except super-admin)
     */
    public function unlockCompanyUsers(int $companyId): void
    {
        $users = User::where('company_id', $companyId)
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', 'super-admin');
            })
            ->get();

        foreach ($users as $user) {
            $user->update([
                'status' => 'active',
                'is_active' => 'yes',
            ]);
        }

        Log::info("Unlocked {$users->count()} users for company ID: {$companyId} (super-admin users excluded)");
    }

    /**
     * Send expiry reminder email
     */
    private function sendExpiryReminder(Subscription $subscription): void
    {
        $company = $subscription->company;
        $adminUsers = User::where('company_id', $subscription->company_id)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'admin');
            })
            ->get();

        $daysUntilExpiry = $subscription->daysUntilExpiry();

        foreach ($adminUsers as $admin) {
            try {
                Mail::send('emails.subscription-expiry-reminder', [
                    'subscription' => $subscription,
                    'company' => $company,
                    'admin' => $admin,
                    'daysUntilExpiry' => $daysUntilExpiry,
                ], function ($message) use ($admin, $company) {
                    $message->to($admin->email, $admin->name)
                        ->subject("Subscription Expiring Soon - {$company->name}");
                });

                Log::info("Expiry reminder email sent to: {$admin->email}");
            } catch (\Exception $e) {
                Log::error("Failed to send expiry reminder email to {$admin->email}: " . $e->getMessage());
            }
        }
    }

    /**
     * Send expiry notification email
     */
    private function sendExpiryNotification(Subscription $subscription): void
    {
        $company = $subscription->company;
        $adminUsers = User::where('company_id', $subscription->company_id)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'admin');
            })
            ->get();

        foreach ($adminUsers as $admin) {
            try {
                Mail::send('emails.subscription-expired', [
                    'subscription' => $subscription,
                    'company' => $company,
                    'admin' => $admin,
                ], function ($message) use ($admin, $company) {
                    $message->to($admin->email, $admin->name)
                        ->subject("Subscription Expired - {$company->name}");
                });

                Log::info("Expiry notification email sent to: {$admin->email}");
            } catch (\Exception $e) {
                Log::error("Failed to send expiry notification email to {$admin->email}: " . $e->getMessage());
            }
        }
    }

    /**
     * Send subscription activated notification
     */
    public function sendActivationNotification(Subscription $subscription): void
    {
        $company = $subscription->company;
        $adminUsers = User::where('company_id', $subscription->company_id)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'admin');
            })
            ->get();

        foreach ($adminUsers as $admin) {
            try {
                Mail::send('emails.subscription-activated', [
                    'subscription' => $subscription,
                    'company' => $company,
                    'admin' => $admin,
                ], function ($message) use ($admin, $company) {
                    $message->to($admin->email, $admin->name)
                        ->subject("Subscription Activated - {$company->name}");
                });

                Log::info("Activation notification email sent to: {$admin->email}");
            } catch (\Exception $e) {
                Log::error("Failed to send activation notification email to {$admin->email}: " . $e->getMessage());
            }
        }
    }
}