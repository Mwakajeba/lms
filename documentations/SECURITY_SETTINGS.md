# Security Settings Implementation

## Overview
The SmartFinance system now has functional security settings that can be configured through the System Configuration panel. All security settings are stored in the `system_settings` table and are applied dynamically throughout the application.

## Available Security Settings

### 1. Session Lifetime
- **Setting Key**: `session_lifetime`
- **Type**: Integer (minutes)
- **Default**: 120 minutes
- **Functionality**: Controls how long user sessions remain active before requiring re-authentication
- **Implementation**: Applied through middleware and affects Laravel's session configuration

### 2. Password Requirements

#### Minimum Password Length
- **Setting Key**: `password_min_length`
- **Type**: Integer
- **Default**: 8 characters
- **Functionality**: Sets the minimum number of characters required for passwords

#### Require Special Characters
- **Setting Key**: `password_require_special`
- **Type**: Boolean
- **Default**: true
- **Functionality**: Requires passwords to contain at least one special character (!@#$%^&* etc.)

#### Require Numbers
- **Setting Key**: `password_require_numbers`
- **Type**: Boolean
- **Default**: true
- **Functionality**: Requires passwords to contain at least one number

#### Require Uppercase Letters
- **Setting Key**: `password_require_uppercase`
- **Type**: Boolean
- **Default**: true
- **Functionality**: Requires passwords to contain at least one uppercase letter

### 3. Login Security

#### Login Attempts Limit
- **Setting Key**: `login_attempts_limit`
- **Type**: Integer
- **Default**: 5 attempts
- **Functionality**: Maximum number of failed login attempts before account lockout

#### Lockout Duration
- **Setting Key**: `lockout_duration`
- **Type**: Integer (minutes)
- **Default**: 15 minutes
- **Functionality**: How long accounts remain locked after exceeding login attempts limit

#### Two-Factor Authentication
- **Setting Key**: `two_factor_enabled`
- **Type**: Boolean
- **Default**: false
- **Functionality**: Enables/disables two-factor authentication (future implementation)

## Implementation Details

### Database Structure
- **Table**: `system_settings`
- **Columns**: `key`, `value`, `type`, `group`, `label`, `description`, `is_public`
- **Login Tracking**: `login_attempts` table tracks failed login attempts

### Key Components

#### 1. PasswordValidation Rule
- **File**: `app/Rules/PasswordValidation.php`
- **Purpose**: Custom validation rule that checks passwords against system settings
- **Usage**: Applied in user creation, password changes, and password resets

#### 2. LoginAttempt Model
- **File**: `app/Models/LoginAttempt.php`
- **Purpose**: Tracks login attempts and manages account lockouts
- **Features**: 
  - Records successful/failed login attempts
  - Checks for lockout conditions
  - Calculates remaining lockout time
  - Cleans old attempt records

#### 3. ApplySystemSettings Middleware
- **File**: `app/Http/Middleware/ApplySystemSettings.php`
- **Purpose**: Applies system settings to Laravel configuration on each request
- **Features**: Applies session lifetime and other security settings

#### 4. SystemSettingService
- **File**: `app/Services/SystemSettingService.php`
- **Purpose**: Provides easy access to system settings with caching
- **Features**: 
  - Cached settings retrieval
  - Type casting for different setting types
  - Default value fallbacks

### Usage Examples

#### Checking Password Requirements
```php
use App\Services\SystemSettingService;

$securityConfig = SystemSettingService::getSecurityConfig();
$minLength = $securityConfig['password_min_length']; // 8
$requireSpecial = $securityConfig['password_require_special']; // true
```

#### Validating Password
```php
use App\Rules\PasswordValidation;

$request->validate([
    'password' => ['required', 'confirmed', new PasswordValidation],
]);
```

#### Checking Login Lockout
```php
use App\Models\LoginAttempt;

if (LoginAttempt::isLockedOut($request->ip())) {
    $remainingTime = LoginAttempt::getRemainingLockoutTime($request->ip());
    // Show lockout message
}
```

### Configuration

#### Accessing Settings Panel
1. Navigate to Settings → System Configuration
2. Click on the "Security Settings" tab
3. Modify settings as needed
4. Click "Save Settings"

#### Initializing Default Settings
```bash
php artisan system:init-settings
```

#### Clearing Settings Cache
```php
\App\Models\SystemSetting::clearCache();
```

## Security Features

### 1. Account Lockout Protection
- Tracks failed login attempts by IP address
- Automatically locks accounts after exceeding limit
- Provides countdown timer for lockout duration
- Cleans old attempt records automatically

### 2. Dynamic Password Requirements
- Password requirements are configurable through admin panel
- Real-time password strength indicator
- Validation applied at multiple points (registration, password change, reset)

### 3. Session Management
- Configurable session lifetime
- Settings applied globally through middleware
- Automatic session cleanup

### 4. Audit Trail
- All login attempts are logged with IP and user agent
- Failed attempts tracked for security analysis
- Successful logins recorded for audit purposes

## Best Practices

### 1. Password Security
- Use strong default requirements (8+ chars, uppercase, numbers, special chars)
- Regularly review and update password policies
- Consider implementing password history to prevent reuse

### 2. Account Lockout
- Set reasonable attempt limits (5-10 attempts)
- Use appropriate lockout duration (15-30 minutes)
- Monitor failed login patterns for potential attacks

### 3. Session Management
- Set appropriate session lifetime based on security needs
- Consider shorter sessions for high-security environments
- Implement automatic logout for inactive sessions

## Troubleshooting

### Common Issues

#### 1. Settings Not Applying
- Clear settings cache: `\App\Models\SystemSetting::clearCache()`
- Check middleware registration in `bootstrap/app.php`
- Verify settings exist in database

#### 2. Password Validation Errors
- Ensure PasswordValidation rule is imported
- Check system settings are properly initialized
- Verify password requirements are reasonable

#### 3. Login Lockout Issues
- Check login_attempts table exists and is populated
- Verify IP address tracking is working
- Check lockout duration settings

### Debug Commands
```bash
# Check current security settings
php artisan tinker --execute="print_r(\App\Services\SystemSettingService::getSecurityConfig());"

# Clear settings cache
php artisan tinker --execute="\App\Models\SystemSetting::clearCache();"

# Check login attempts
php artisan tinker --execute="echo \App\Models\LoginAttempt::count();"
```

## Future Enhancements

### Planned Features
1. **Two-Factor Authentication**: SMS/Email OTP integration
2. **Password History**: Prevent password reuse
3. **Account Recovery**: Enhanced password reset process
4. **Security Notifications**: Email alerts for suspicious activity
5. **IP Whitelisting**: Allow trusted IPs to bypass lockouts
6. **Geolocation Tracking**: Track login locations for security
7. **Session Analytics**: Monitor active sessions and force logout

### Integration Points
- **Email System**: For security notifications and 2FA
- **SMS System**: For 2FA and security alerts
- **Audit Logging**: For comprehensive security tracking
- **User Management**: For account recovery and security settings 