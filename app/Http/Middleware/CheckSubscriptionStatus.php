<?php

namespace App\Http\Middleware;

use App\Models\Subscription;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip check for guest users
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        $companyId = $user->company_id;

        // Skip check for super admin or if user is already locked
        if ($user->status === 'locked' || !$companyId) {
            if ($user->status === 'locked') {
                Auth::logout();
                return redirect()->route('login')->with('error', 'Your account has been locked due to expired subscription. Please contact your administrator.');
            }
            return $next($request);
        }

        // Check if company has an active subscription
        $activeSubscription = Subscription::where('company_id', $companyId)
            ->where('status', 'active')
            ->where('payment_status', 'paid')
            ->where('end_date', '>=', now())
            ->first();

        // If no active subscription, check if user is trying to access subscription management
        if (!$activeSubscription) {
            $allowedRoutes = [
                'subscriptions.*',
                'logout',
                'login',
                'password.*',
                'verification.*',
            ];

            $currentRoute = $request->route()->getName();

            // Allow access to subscription management routes
            foreach ($allowedRoutes as $allowedRoute) {
                if (str_contains($allowedRoute, '*')) {
                    $pattern = str_replace('*', '.*', $allowedRoute);
                    if (preg_match('/^' . $pattern . '$/', $currentRoute)) {
                        return $next($request);
                    }
                } elseif ($currentRoute === $allowedRoute) {
                    return $next($request);
                }
            }

            // Redirect to subscription page with error message
            return redirect()->route('subscriptions.dashboard')
                ->with('error', 'Your subscription has expired. Please renew your subscription to continue using the system.');
        }

        // Check if subscription is expiring soon (within 5 days)
        if ($activeSubscription->isExpiringSoon()) {
            $daysUntilExpiry = $activeSubscription->daysUntilExpiry();

            // Add warning message to session if not already shown
            if (!$request->session()->has('subscription_warning_shown')) {
                $request->session()->flash('warning', "Your subscription will expire in {$daysUntilExpiry} days. Please renew to avoid service interruption.");
                $request->session()->put('subscription_warning_shown', true);
            }
        }

        return $next($request);
    }
}