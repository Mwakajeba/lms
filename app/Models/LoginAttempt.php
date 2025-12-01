<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LoginAttempt extends Model
{
    use HasFactory,LogsActivity;

    protected $fillable = [
        'phone',
        'ip_address',
        'user_agent',
        'success',
        'attempted_at'
    ];

    protected $casts = [
        'success' => 'boolean',
        'attempted_at' => 'datetime'
    ];

    /**
     * Record a login attempt
     */
    public static function record($phone, $ipAddress, $userAgent, $success = false)
    {
        return self::create([
            'phone' => $phone,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'success' => $success,
            'attempted_at' => now()
        ]);
    }

    /**
     * Check if an IP address is locked out
     */
    public static function isLockedOut($ipAddress)
    {
        $securityConfig = \App\Services\SystemSettingService::getSecurityConfig();
        $limit = $securityConfig['login_attempts_limit'] ?? 5;
        $duration = $securityConfig['lockout_duration'] ?? 15;

        $failedAttempts = self::where('ip_address', $ipAddress)
            ->where('success', false)
            ->where('attempted_at', '>=', now()->subMinutes($duration))
            ->count();

        return $failedAttempts >= $limit;
    }

    /**
     * Get remaining lockout time in minutes
     */
    public static function getRemainingLockoutTime($ipAddress)
    {
        $securityConfig = \App\Services\SystemSettingService::getSecurityConfig();
        $duration = $securityConfig['lockout_duration'] ?? 15;

        $lastFailedAttempt = self::where('ip_address', $ipAddress)
            ->where('success', false)
            ->orderBy('attempted_at', 'desc')
            ->first();

        if (!$lastFailedAttempt) {
            return 0;
        }

        $lockoutEnd = $lastFailedAttempt->attempted_at->addMinutes($duration);
        $remaining = now()->diffInMinutes($lockoutEnd, false);

        return max(0, $remaining);
    }

    /**
     * Clear old login attempts
     */
    public static function clearOldAttempts()
    {
        $securityConfig = \App\Services\SystemSettingService::getSecurityConfig();
        $duration = $securityConfig['lockout_duration'] ?? 15;

        return self::where('attempted_at', '<', now()->subMinutes($duration * 2))
            ->delete();
    }

    /**
     * Get failed attempts count for an IP
     */
    public static function getFailedAttemptsCount($ipAddress)
    {
        $securityConfig = \App\Services\SystemSettingService::getSecurityConfig();
        $duration = $securityConfig['lockout_duration'] ?? 15;

        return self::where('ip_address', $ipAddress)
            ->where('success', false)
            ->where('attempted_at', '>=', now()->subMinutes($duration))
            ->count();
    }
} 