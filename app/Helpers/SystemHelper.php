<?php

if (!function_exists('setting')) {
    /**
     * Get a system setting value
     */
    function setting($key, $default = null)
    {
        return \App\Services\SystemSettingService::get($key, $default);
    }
}

if (!function_exists('app_setting')) {
    /**
     * Get application setting
     */
    function app_setting($key, $default = null)
    {
        return \App\Services\SystemSettingService::get($key, $default);
    }
}

if (!function_exists('microfinance_setting')) {
    /**
     * Get microfinance specific setting
     */
    function microfinance_setting($key, $default = null)
    {
        return \App\Services\SystemSettingService::get($key, $default);
    }
}

if (!function_exists('is_maintenance_mode')) {
    /**
     * Check if maintenance mode is enabled
     */
    function is_maintenance_mode()
    {
        return \App\Services\SystemSettingService::isMaintenanceMode();
    }
}

if (!function_exists('get_maintenance_message')) {
    /**
     * Get maintenance message
     */
    function get_maintenance_message()
    {
        return \App\Services\SystemSettingService::getMaintenanceMessage();
    }
}

if (!function_exists('format_currency')) {
    /**
     * Format currency based on system settings
     */
    function format_currency($amount, $currency = null)
    {
        $currency = $currency ?: setting('currency', 'TZS');
        $symbol = setting('currency_symbol', 'TSh');
        
        return $symbol . number_format($amount, 2);
    }
}

if (!function_exists('format_date')) {
    /**
     * Format date based on system settings
     */
    function format_date($date, $format = null)
    {
        $format = $format ?: setting('date_format', 'Y-m-d');
        
        if ($date instanceof \Carbon\Carbon) {
            return $date->format($format);
        }
        
        return \Carbon\Carbon::parse($date)->format($format);
    }
}

if (!function_exists('format_datetime')) {
    /**
     * Format datetime based on system settings
     */
    function format_datetime($datetime, $dateFormat = null, $timeFormat = null)
    {
        $dateFormat = $dateFormat ?: setting('date_format', 'Y-m-d');
        $timeFormat = $timeFormat ?: setting('time_format', 'H:i:s');
        $format = $dateFormat . ' ' . $timeFormat;
        
        if ($datetime instanceof \Carbon\Carbon) {
            return $datetime->format($format);
        }
        
        return \Carbon\Carbon::parse($datetime)->format($format);
    }
} 