# Upload Configuration Guide

## Problem
Getting "POST data is too large" error when uploading documents larger than 2MB.

## Root Cause
PHP default settings are too restrictive:
- `upload_max_filesize` = 2M
- `post_max_size` = 8M

## Solution

### Method 1: Update php.ini (Recommended)
1. Find your php.ini file:
   ```bash
   php --ini
   ```

2. Edit the php.ini file and update these values:
   ```ini
   upload_max_filesize = 50M
   post_max_size = 100M
   max_file_uploads = 20
   memory_limit = 512M
   max_execution_time = 600
   max_input_time = 600
   max_input_vars = 3000
   file_uploads = On
   ```

3. Restart your web server:
   ```bash
   # For Apache
   sudo systemctl restart apache2
   
   # For Nginx + PHP-FPM
   sudo systemctl restart nginx
   sudo systemctl restart php8.4-fpm
   ```

### Method 2: Using .htaccess (If supported)
The project includes .htaccess files with the correct settings:
- `/home/efron/smartfinance/.htaccess`
- `/home/efron/smartfinance/public/.htaccess`

### Method 3: Environment Variables
Add to your `.env` file:
```env
UPLOAD_MAX_FILE_SIZE=51200
UPLOAD_MAX_FILES=10
UPLOAD_STORAGE_DISK=public
UPLOAD_STORAGE_PATH=loan_documents
```

## Verification
Run the test script to verify settings:
```bash
php test_upload_limits.php
```

## Current Configuration
- **Max file size**: 50MB per file
- **Max POST size**: 100MB total
- **Max files**: 20 files per upload
- **Memory limit**: 512MB
- **Execution time**: 10 minutes

## Troubleshooting

### If .htaccess doesn't work:
1. Check if mod_rewrite is enabled
2. Check if AllowOverride is set to All in Apache config
3. Use Method 1 (php.ini) instead

### If still getting errors:
1. Check web server error logs
2. Verify PHP configuration is loaded
3. Contact hosting provider for shared hosting

### For Production:
- Consider using cloud storage (AWS S3, Google Cloud)
- Implement file chunking for very large files
- Add virus scanning for uploaded files
