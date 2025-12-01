<?php

if (!function_exists('normalize_phone_number')) {
    /**
     * Normalize phone number to standard format
     * Handles +255, 0, and 255 prefixes for Tanzania
     */
    function normalize_phone_number($phone)
    {
        // Remove all non-digit characters except +
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // If it starts with +255, convert to 255
        if (strpos($phone, '+255') === 0) {
            return '255' . substr($phone, 4);
        }
        
        // If it starts with 0, convert to 255
        if (strpos($phone, '0') === 0) {
            return '255' . substr($phone, 1);
        }
        
        // If it starts with 255, return as is
        if (strpos($phone, '255') === 0) {
            return $phone;
        }
        
        // If it's a 9-digit number (Tanzania mobile), add 255 prefix
        if (strlen($phone) === 9) {
            return '255' . $phone;
        }
        
        // Return as is if it doesn't match any pattern
        return $phone;
    }
}

if (!function_exists('format_phone_for_display')) {
    /**
     * Format phone number for display
     */
    function format_phone_for_display($phone)
    {
        $normalized = normalize_phone_number($phone);
        
        // If it's a Tanzania number (starts with 255 and has 12 digits)
        if (strpos($normalized, '255') === 0 && strlen($normalized) === 12) {
            return '+255 ' . substr($normalized, 3, 2) . ' ' . substr($normalized, 5, 3) . ' ' . substr($normalized, 8, 4);
        }
        
        return $phone;
    }
}

if (!function_exists('validate_tanzania_phone')) {
    /**
     * Validate Tanzania phone number
     */
    function validate_tanzania_phone($phone)
    {
        $normalized = normalize_phone_number($phone);
        
        // Tanzania mobile numbers should be 12 digits starting with 255
        if (strlen($normalized) === 12 && strpos($normalized, '255') === 0) {
            return true;
        }
        
        return false;
    }
}

if (!function_exists('find_user_by_phone')) {
    /**
     * Find user by phone number with flexible matching
     */
    function find_user_by_phone($phone)
    {
        $normalized = normalize_phone_number($phone);
        
        // Try to find user with normalized phone number
        $user = \App\Models\User::where('phone', $normalized)->first();
        
        if ($user) {
            return $user;
        }
        
        // If not found, try different variations
        $variations = [];
        
        // If it's a Tanzania number, try different formats
        if (strpos($normalized, '255') === 0 && strlen($normalized) === 12) {
            $number = substr($normalized, 3); // Remove 255 prefix
            
            $variations = [
                $normalized,                    // 255xxxxxxxxx
                '0' . $number,                  // 0xxxxxxxxx
                '+' . $normalized,              // +255xxxxxxxxx
                $number                         // xxxxxxxxx (9 digits)
            ];
        }
        
        // Try each variation
        foreach ($variations as $variation) {
            $user = \App\Models\User::where('phone', $variation)->first();
            if ($user) {
                return $user;
            }
        }
        
        return null;
    }
} 