<?php

return [
    /*
    |--------------------------------------------------------------------------
    | File Upload Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for file uploads in the application.
    | You can adjust these values based on your server capabilities and requirements.
    |
    */

    'max_file_size' => env('UPLOAD_MAX_FILE_SIZE', 51200), // 50MB in KB
    'max_files' => env('UPLOAD_MAX_FILES', 10),
    'allowed_mimes' => [
        'pdf',
        'jpg',
        'jpeg',
        'png',
        'doc',
        'docx',
        'xls',
        'xlsx',
        'txt'
    ],
    'storage_disk' => env('UPLOAD_STORAGE_DISK', 'public'),
    'storage_path' => env('UPLOAD_STORAGE_PATH', 'loan_documents'),
];

