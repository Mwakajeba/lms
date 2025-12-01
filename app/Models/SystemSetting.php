<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    use HasFactory,LogsActivity;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
        'is_public'
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * Get a setting value by key
     */
    public static function getValue($key, $default = null)
    {
        $cacheKey = "system_setting_{$key}";
        
        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }

            return self::castValue($setting->value, $setting->type);
        });
    }

    /**
     * Set a setting value
     */
    public static function setValue($key, $value, $type = 'string', $group = 'general', $label = null, $description = null)
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'group' => $group,
                'label' => $label ?? ucwords(str_replace('_', ' ', $key)),
                'description' => $description
            ]
        );

        // Clear cache
        Cache::forget("system_setting_{$key}");
        
        return $setting;
    }

    /**
     * Get all settings by group
     */
    public static function getByGroup($group)
    {
        return self::where('group', $group)->get();
    }

    /**
     * Get all settings as key-value pairs
     */
    public static function getAllAsArray()
    {
        return Cache::remember('all_system_settings', 3600, function () {
            $settings = self::all();
            $result = [];
            
            foreach ($settings as $setting) {
                $result[$setting->key] = self::castValue($setting->value, $setting->type);
            }
            
            return $result;
        });
    }

    /**
     * Cast value based on type
     */
    private static function castValue($value, $type)
    {
        if ($value === null) {
            return null;
        }

        switch ($type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
                return (int) $value;
            case 'json':
                return json_decode($value, true);
            case 'array':
                return explode(',', $value);
            default:
                return $value;
        }
    }

    /**
     * Clear all settings cache
     */
    public static function clearCache()
    {
        Cache::forget('all_system_settings');
        
        $settings = self::all();
        foreach ($settings as $setting) {
            Cache::forget("system_setting_{$setting->key}");
        }
    }

    /**
     * Initialize default settings
     */
    public static function initializeDefaults()
    {
        $defaults = [
            // General Settings
            'app_name' => ['value' => 'SmartFinance', 'type' => 'string', 'group' => 'general', 'label' => 'Application Name'],
            'app_url' => ['value' => config('app.url'), 'type' => 'string', 'group' => 'general', 'label' => 'Application URL'],
            'timezone' => ['value' => 'Africa/Dar_es_Salaam', 'type' => 'string', 'group' => 'general', 'label' => 'Timezone'],
            'locale' => ['value' => config('app.locale'), 'type' => 'string', 'group' => 'general', 'label' => 'Default Language'],
            'date_format' => ['value' => 'Y-m-d', 'type' => 'string', 'group' => 'general', 'label' => 'Date Format'],
            'time_format' => ['value' => 'H:i:s', 'type' => 'string', 'group' => 'general', 'label' => 'Time Format'],
            'currency' => ['value' => 'TZS', 'type' => 'string', 'group' => 'general', 'label' => 'Default Currency'],
            'currency_symbol' => ['value' => 'TSh', 'type' => 'string', 'group' => 'general', 'label' => 'Currency Symbol'],
            'locale' => ['value' => 'sw', 'type' => 'string', 'group' => 'general', 'label' => 'Default Language'],
            
            // Email Settings
            'mail_driver' => ['value' => config('mail.default'), 'type' => 'string', 'group' => 'email', 'label' => 'Mail Driver'],
            'mail_host' => ['value' => config('mail.mailers.smtp.host'), 'type' => 'string', 'group' => 'email', 'label' => 'SMTP Host'],
            'mail_port' => ['value' => config('mail.mailers.smtp.port'), 'type' => 'integer', 'group' => 'email', 'label' => 'SMTP Port'],
            'mail_username' => ['value' => config('mail.mailers.smtp.username'), 'type' => 'string', 'group' => 'email', 'label' => 'SMTP Username'],
            'mail_password' => ['value' => config('mail.mailers.smtp.password'), 'type' => 'string', 'group' => 'email', 'label' => 'SMTP Password'],
            'mail_encryption' => ['value' => config('mail.mailers.smtp.encryption'), 'type' => 'string', 'group' => 'email', 'label' => 'SMTP Encryption'],
            'mail_from_address' => ['value' => config('mail.from.address'), 'type' => 'string', 'group' => 'email', 'label' => 'From Email Address'],
            'mail_from_name' => ['value' => config('mail.from.name'), 'type' => 'string', 'group' => 'email', 'label' => 'From Name'],
            
            // Security Settings
            'session_lifetime' => ['value' => config('session.lifetime'), 'type' => 'integer', 'group' => 'security', 'label' => 'Session Lifetime (minutes)'],
            'password_min_length' => ['value' => 8, 'type' => 'integer', 'group' => 'security', 'label' => 'Minimum Password Length'],
            'password_require_special' => ['value' => true, 'type' => 'boolean', 'group' => 'security', 'label' => 'Require Special Characters'],
            'password_require_numbers' => ['value' => true, 'type' => 'boolean', 'group' => 'security', 'label' => 'Require Numbers'],
            'password_require_uppercase' => ['value' => true, 'type' => 'boolean', 'group' => 'security', 'label' => 'Require Uppercase Letters'],
            'login_attempts_limit' => ['value' => 5, 'type' => 'integer', 'group' => 'security', 'label' => 'Login Attempts Limit'],
            'lockout_duration' => ['value' => 15, 'type' => 'integer', 'group' => 'security', 'label' => 'Lockout Duration (minutes)'],
            'two_factor_enabled' => ['value' => false, 'type' => 'boolean', 'group' => 'security', 'label' => 'Enable Two-Factor Authentication'],
            
            // Backup Settings
            'backup_enabled' => ['value' => true, 'type' => 'boolean', 'group' => 'backup', 'label' => 'Enable Automatic Backups'],
            'backup_frequency' => ['value' => 'daily', 'type' => 'string', 'group' => 'backup', 'label' => 'Backup Frequency'],
            'backup_retention_days' => ['value' => 30, 'type' => 'integer', 'group' => 'backup', 'label' => 'Backup Retention (days)'],
            'backup_include_files' => ['value' => true, 'type' => 'boolean', 'group' => 'backup', 'label' => 'Include Files in Backup'],
            'backup_compression' => ['value' => true, 'type' => 'boolean', 'group' => 'backup', 'label' => 'Compress Backups'],
            
            // Maintenance Settings
            'maintenance_mode' => ['value' => false, 'type' => 'boolean', 'group' => 'maintenance', 'label' => 'Maintenance Mode'],
            'maintenance_message' => ['value' => 'System is under maintenance. Please try again later.', 'type' => 'string', 'group' => 'maintenance', 'label' => 'Maintenance Message'],
            'debug_mode' => ['value' => config('app.debug'), 'type' => 'boolean', 'group' => 'maintenance', 'label' => 'Debug Mode'],
            'log_level' => ['value' => config('logging.default'), 'type' => 'string', 'group' => 'maintenance', 'label' => 'Log Level'],
            
            // Microfinance Specific Settings
            'loan_interest_rate_default' => ['value' => 12.5, 'type' => 'string', 'group' => 'microfinance', 'label' => 'Default Loan Interest Rate (%)'],
            'loan_processing_fee' => ['value' => 2.0, 'type' => 'string', 'group' => 'microfinance', 'label' => 'Loan Processing Fee (%)'],
            'savings_interest_rate' => ['value' => 5.0, 'type' => 'string', 'group' => 'microfinance', 'label' => 'Savings Interest Rate (%)'],
            'minimum_savings_balance' => ['value' => 100, 'type' => 'string', 'group' => 'microfinance', 'label' => 'Minimum Savings Balance'],
            'late_payment_penalty' => ['value' => 5.0, 'type' => 'string', 'group' => 'microfinance', 'label' => 'Late Payment Penalty (%)'],
            'grace_period_days' => ['value' => 7, 'type' => 'integer', 'group' => 'microfinance', 'label' => 'Grace Period (days)'],
        ];

        foreach ($defaults as $key => $config) {
            self::setValue(
                $key,
                $config['value'],
                $config['type'],
                $config['group'],
                $config['label']
            );
        }
    }
}
