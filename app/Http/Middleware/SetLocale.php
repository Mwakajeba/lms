<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\SystemSettingService;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get locale from different sources in order of priority
        $locale = $this->getLocale($request);
        
        // Set the application locale
        app()->setLocale($locale);
        
        // Set Carbon locale for date formatting
        \Carbon\Carbon::setLocale($locale);
        
        return $next($request);
    }

    /**
     * Get the locale from various sources
     */
    private function getLocale(Request $request): string
    {
        // 1. Check if user is authenticated and has a preferred locale
        if (auth()->check() && auth()->user()->locale) {
            return auth()->user()->locale;
        }

        // 2. Check session for stored locale
        if (session()->has('locale')) {
            return session('locale');
        }

        // 3. Check URL parameter for locale
        if ($request->has('lang')) {
            $locale = $request->get('lang');
            if ($this->isValidLocale($locale)) {
                session(['locale' => $locale]);
                return $locale;
            }
        }

        // 4. Check system settings for default locale
        $defaultLocale = SystemSettingService::get('locale', config('app.locale'));
        if ($this->isValidLocale($defaultLocale)) {
            return $defaultLocale;
        }

        // 5. Fallback to application default
        return config('app.locale', 'en');
    }

    /**
     * Check if the locale is valid
     */
    private function isValidLocale(string $locale): bool
    {
        $validLocales = ['en', 'sw', 'fr', 'es', 'ar'];
        return in_array($locale, $validLocales);
    }
}
