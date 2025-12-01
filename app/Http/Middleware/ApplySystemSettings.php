<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\SystemSettingService;

class ApplySystemSettings
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Apply security settings
        $this->applySecuritySettings();
        
        return $next($request);
    }

    /**
     * Apply security settings to the application
     */
    private function applySecuritySettings()
    {
        try {
            $securityConfig = SystemSettingService::getSecurityConfig();
            
            // Apply session lifetime
            if (isset($securityConfig['session_lifetime'])) {
                config(['session.lifetime' => $securityConfig['session_lifetime']]);
            }
            
        } catch (\Exception $e) {
            // Log error but don't break the application
            \Log::warning('Failed to apply system settings: ' . $e->getMessage());
        }
    }
}
