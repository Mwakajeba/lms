<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Services\SystemSettingService;

class PasswordValidation implements Rule
{
    protected $message = '';

    /**
     * Create a new rule instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     */
    public function passes($attribute, $value): bool
    {
        $securityConfig = SystemSettingService::getSecurityConfig();
        
        // Check minimum length
        $minLength = $securityConfig['password_min_length'] ?? 8;
        if (strlen($value) < $minLength) {
            $this->message = "Password must be at least {$minLength} characters long.";
            return false;
        }

        // Check for uppercase letters
        if ($securityConfig['password_require_uppercase'] ?? true) {
            if (!preg_match('/[A-Z]/', $value)) {
                $this->message = 'Password must contain at least one uppercase letter.';
                return false;
            }
        }

        // Check for numbers
        if ($securityConfig['password_require_numbers'] ?? true) {
            if (!preg_match('/[0-9]/', $value)) {
                $this->message = 'Password must contain at least one number.';
                return false;
            }
        }

        // Check for special characters
        if ($securityConfig['password_require_special'] ?? true) {
            if (!preg_match('/[^A-Za-z0-9]/', $value)) {
                $this->message = 'Password must contain at least one special character.';
                return false;
            }
        }

        return true;
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return $this->message;
    }
} 