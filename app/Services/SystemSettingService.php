<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

class SystemSettingService
{
    /**
     * Get a setting value
     */
    public static function get($key, $default = null)
    {
        return SystemSetting::getValue($key, $default);
    }

    /**
     * Set a setting value
     */
    public static function set($key, $value, $type = 'string', $group = 'general', $label = null, $description = null)
    {
        return SystemSetting::setValue($key, $value, $type, $group, $label, $description);
    }

    /**
     * Get all settings as array
     */
    public static function all()
    {
        return SystemSetting::getAllAsArray();
    }

    /**
     * Get settings by group
     */
    public static function getByGroup($group)
    {
        return SystemSetting::getByGroup($group);
    }

    /**
     * Clear all settings cache
     */
    public static function clearCache()
    {
        SystemSetting::clearCache();
    }

    /**
     * Get application configuration
     */
    public static function getAppConfig()
    {
        return [
            'name' => self::get('app_name', config('app.name')),
            'url' => self::get('app_url', config('app.url')),
            'timezone' => self::get('timezone', config('app.timezone')),
            'locale' => self::get('locale', config('app.locale')),
            'debug' => self::get('debug_mode', config('app.debug')),
        ];
    }

    /**
     * Get email configuration
     */
    public static function getEmailConfig()
    {
        return [
            'driver' => self::get('mail_driver', config('mail.default')),
            'host' => self::get('mail_host', config('mail.mailers.smtp.host')),
            'port' => self::get('mail_port', config('mail.mailers.smtp.port')),
            'username' => self::get('mail_username', config('mail.mailers.smtp.username')),
            'password' => self::get('mail_password', config('mail.mailers.smtp.password')),
            'encryption' => self::get('mail_encryption', config('mail.mailers.smtp.encryption')),
            'from_address' => self::get('mail_from_address', config('mail.from.address')),
            'from_name' => self::get('mail_from_name', config('mail.from.name')),
        ];
    }

    /**
     * Get security configuration
     */
    public static function getSecurityConfig()
    {
        return [
            'session_lifetime' => self::get('session_lifetime', config('session.lifetime')),
            'password_min_length' => self::get('password_min_length', 8),
            'password_require_special' => self::get('password_require_special', true),
            'password_require_numbers' => self::get('password_require_numbers', true),
            'password_require_uppercase' => self::get('password_require_uppercase', true),
            'login_attempts_limit' => self::get('login_attempts_limit', 5),
            'lockout_duration' => self::get('lockout_duration', 15),
            'two_factor_enabled' => self::get('two_factor_enabled', false),
        ];
    }

    /**
     * Get backup configuration
     */
    public static function getBackupConfig()
    {
        return [
            'enabled' => self::get('backup_enabled', true),
            'frequency' => self::get('backup_frequency', 'daily'),
            'retention_days' => self::get('backup_retention_days', 30),
            'include_files' => self::get('backup_include_files', true),
            'compression' => self::get('backup_compression', true),
        ];
    }

    /**
     * Get microfinance configuration
     */
    public static function getMicrofinanceConfig()
    {
        return [
            'loan_interest_rate_default' => self::get('loan_interest_rate_default', 12.5),
            'loan_processing_fee' => self::get('loan_processing_fee', 2.0),
            'savings_interest_rate' => self::get('savings_interest_rate', 5.0),
            'minimum_savings_balance' => self::get('minimum_savings_balance', 100),
            'late_payment_penalty' => self::get('late_payment_penalty', 5.0),
            'grace_period_days' => self::get('grace_period_days', 7),
        ];
    }

    /**
     * Check if maintenance mode is enabled
     */
    public static function isMaintenanceMode()
    {
        return self::get('maintenance_mode', false);
    }

    /**
     * Get maintenance message
     */
    public static function getMaintenanceMessage()
    {
        return self::get('maintenance_message', 'System is under maintenance. Please try again later.');
    }

    /**
     * Apply settings to Laravel configuration
     */
    public static function applyToConfig()
    {
        $appConfig = self::getAppConfig();
        $emailConfig = self::getEmailConfig();

        // Apply app settings
        config(['app.name' => $appConfig['name']]);
        config(['app.url' => $appConfig['url']]);
        config(['app.timezone' => $appConfig['timezone']]);
        config(['app.locale' => $appConfig['locale']]);
        config(['app.debug' => $appConfig['debug']]);

        // Apply email settings
        config(['mail.default' => $emailConfig['driver']]);
        config(['mail.mailers.smtp.host' => $emailConfig['host']]);
        config(['mail.mailers.smtp.port' => $emailConfig['port']]);
        config(['mail.mailers.smtp.username' => $emailConfig['username']]);
        config(['mail.mailers.smtp.password' => $emailConfig['password']]);
        config(['mail.mailers.smtp.encryption' => $emailConfig['encryption']]);
        config(['mail.from.address' => $emailConfig['from_address']]);
        config(['mail.from.name' => $emailConfig['from_name']]);

        // Apply session settings
        $securityConfig = self::getSecurityConfig();
        config(['session.lifetime' => $securityConfig['session_lifetime']]);
    }

    /**
     * Validate email settings
     */
    public static function validateEmailSettings()
    {
        $emailConfig = self::getEmailConfig();
        
        $required = ['host', 'port', 'username', 'password'];
        $missing = [];
        
        foreach ($required as $field) {
            if (empty($emailConfig[$field])) {
                $missing[] = $field;
            }
        }
        
        return [
            'valid' => empty($missing),
            'missing' => $missing,
            'config' => $emailConfig
        ];
    }

    /**
     * Test email configuration
     */
    public static function testEmailConfig()
    {
        try {
            $emailConfig = self::getEmailConfig();
            
            // Create a test mailer
            $config = [
                'transport' => 'smtp',
                'host' => $emailConfig['host'],
                'port' => $emailConfig['port'],
                'encryption' => $emailConfig['encryption'],
                'username' => $emailConfig['username'],
                'password' => $emailConfig['password'],
                'timeout' => null,
                'local_domain' => env('MAIL_EHLO_DOMAIN'),
            ];

            $mailer = new \Illuminate\Mail\MailManager(app(), $config);
            
            // Try to send a test email
            $mailer->raw('Test email from SmartFinance system settings', function($message) use ($emailConfig) {
                $message->to($emailConfig['from_address'])
                        ->subject('SmartFinance Email Test');
            });

            return ['success' => true, 'message' => 'Email configuration is working correctly'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Email configuration failed: ' . $e->getMessage()];
        }
    }
} 