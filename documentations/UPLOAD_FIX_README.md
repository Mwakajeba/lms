# Document Upload Fix

## Problem
Getting "POST data is too large" error when uploading documents to loan details page.

## Root Cause
PHP configuration had very restrictive upload limits:
- `upload_max_filesize` = 2M (only 2MB per file)
- `post_max_size` = 8M (only 8MB total POST data)

## Solutions Implemented

### 1. Updated LoanController
- Modified `loanDocument()` method to handle multiple file uploads
- Added proper validation for file types and sizes
- Added database transaction handling for reliability
- Added comprehensive error handling and logging

### 2. Created Upload Configuration
- Added `config/upload.php` for centralized upload settings
- Configurable file size limits, allowed MIME types, storage settings
- Environment-based configuration support

### 3. Updated Form Structure
- Changed form fields to support multiple file uploads:
  - `filetypes[]` for document types
  - `files[]` for file inputs
- Updated JavaScript to handle multiple file rows
- Added support for more file types (.xls, .xlsx)

### 4. PHP Configuration Files
- Created `.htaccess` with increased limits:
  - `upload_max_filesize` = 50M
  - `post_max_size` = 100M
  - `max_file_uploads` = 20
  - `memory_limit` = 256M
- Created `check_upload_limits.php` script to verify settings

## New Features

### Multiple File Upload
- Users can now upload multiple documents at once
- Each document can have a different type
- Proper validation for each file

### Enhanced File Support
- PDF, JPG, JPEG, PNG, DOC, DOCX, XLS, XLSX
- Configurable file size limits (default: 10MB per file)
- Proper MIME type validation

### Better Error Handling
- Clear error messages for users
- Detailed logging for debugging
- Database transaction rollback on errors

## Configuration

### Environment Variables
Add to `.env` file:
```env
UPLOAD_MAX_FILE_SIZE=10240  # 10MB in KB
UPLOAD_MAX_FILES=10
UPLOAD_STORAGE_DISK=public
UPLOAD_STORAGE_PATH=loan_documents
```

### Server Requirements
- PHP 7.4+ with file uploads enabled
- Sufficient disk space for document storage
- Web server with mod_rewrite (for .htaccess)

## Testing
Run the upload limits checker:
```bash
php check_upload_limits.php
```

## Troubleshooting

### If uploads still fail:
1. Check PHP configuration: `php -i | grep -E "(upload_max_filesize|post_max_size)"`
2. Verify .htaccess is being processed by web server
3. Check file permissions on storage directory
4. Review Laravel logs for detailed error messages

### For production servers:
- Contact hosting provider to increase PHP limits
- Consider using cloud storage (S3, etc.) for large files
- Implement file compression for uploaded documents
