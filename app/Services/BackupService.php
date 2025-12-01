<?php

namespace App\Services;

use App\Models\Backup;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ZipArchive;

class BackupService
{
    protected $backupPath = 'backups';
    protected $tempPath = 'temp';

    public function __construct()
    {
        // Create backup directories if they don't exist
        $backupDir = storage_path("app/{$this->backupPath}");
        $tempDir = storage_path("app/{$this->tempPath}");
        
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
    }

    /**
     * Create a database backup
     */
    public function createDatabaseBackup($description = null)
    {
        try {
            $filename = 'database_backup_' . date('Y-m-d_H-i-s') . '.sql';
            $filePath = $this->backupPath . '/' . $filename;
            $fullPath = storage_path("app/{$filePath}");
            
            // Get database configuration
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');
            $port = config('database.connections.mysql.port');

            // Create mysqldump command
            $command = "mysqldump -h {$host} -P {$port} -u {$username}";
            if ($password) {
                $command .= " -p{$password}";
            }
            $command .= " {$database} > {$fullPath}";

            // Execute the command
            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                throw new \Exception('Database backup failed');
            }

            // Get file size
            $size = file_exists($fullPath) ? filesize($fullPath) : 0;

            // Create backup record
            $backup = Backup::create([
                'name' => 'Database Backup - ' . date('Y-m-d H:i:s'),
                'filename' => $filename,
                'file_path' => $filePath,
                'type' => 'database',
                'size' => $size,
                'description' => $description,
                'status' => 'completed',
                'created_by' => auth()->id(),
                'company_id' => current_company_id(),
            ]);

            return $backup;

        } catch (\Exception $e) {
            // Create failed backup record
            $backup = Backup::create([
                'name' => 'Database Backup - ' . date('Y-m-d H:i:s'),
                'filename' => $filename ?? 'failed_backup.sql',
                'file_path' => $filePath ?? 'failed_backup.sql',
                'type' => 'database',
                'size' => 0,
                'description' => $description,
                'status' => 'failed',
                'created_by' => auth()->id(),
                'company_id' => current_company_id(),
            ]);

            throw $e;
        }
    }

    /**
     * Create a files backup
     */
    public function createFilesBackup($description = null)
    {
        try {
            $filename = 'files_backup_' . date('Y-m-d_H-i-s') . '.zip';
            $filePath = $this->backupPath . '/' . $filename;
            $fullPath = storage_path("app/{$filePath}");
            $tempZipPath = storage_path("app/{$this->tempPath}/temp_files.zip");

            // Create ZIP archive
            $zip = new ZipArchive();
            if ($zip->open($tempZipPath, ZipArchive::CREATE) !== TRUE) {
                throw new \Exception('Could not create ZIP file');
            }

            // Add files to ZIP
            $this->addDirectoryToZip($zip, storage_path('app/public'), 'storage/app/public');
            $this->addDirectoryToZip($zip, public_path('uploads'), 'public/uploads');

            $zip->close();

            // Move to final location
            if (!Storage::move($this->tempPath . '/temp_files.zip', $filePath)) {
                // Fallback to copy and delete
                Storage::copy($this->tempPath . '/temp_files.zip', $filePath);
                Storage::delete($this->tempPath . '/temp_files.zip');
            }

            // Get file size
            $size = file_exists($fullPath) ? filesize($fullPath) : 0;

            // Create backup record
            $backup = Backup::create([
                'name' => 'Files Backup - ' . date('Y-m-d H:i:s'),
                'filename' => $filename,
                'file_path' => $filePath,
                'type' => 'files',
                'size' => $size,
                'description' => $description,
                'status' => 'completed',
                'created_by' => auth()->id(),
                'company_id' => current_company_id(),
            ]);

            return $backup;

        } catch (\Exception $e) {
            // Create failed backup record
            $backup = Backup::create([
                'name' => 'Files Backup - ' . date('Y-m-d H:i:s'),
                'filename' => $filename ?? 'failed_backup.zip',
                'file_path' => $filePath ?? 'failed_backup.zip',
                'type' => 'files',
                'size' => 0,
                'description' => $description,
                'status' => 'failed',
                'created_by' => auth()->id(),
                'company_id' => current_company_id(),
            ]);

            throw $e;
        }
    }

    /**
     * Create a full backup (database + files)
     */
    public function createFullBackup($description = null)
    {
        try {
            $filename = 'full_backup_' . date('Y-m-d_H-i-s') . '.zip';
            $filePath = $this->backupPath . '/' . $filename;
            $fullPath = storage_path("app/{$filePath}");
            $tempZipPath = storage_path("app/{$this->tempPath}/temp_full.zip");

            // Create database backup first (but don't save to database yet)
            $dbFilename = 'database_backup_' . date('Y-m-d_H-i-s') . '.sql';
            $dbFilePath = storage_path("app/{$this->backupPath}/{$dbFilename}");
            
            // Get database configuration
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');
            $port = config('database.connections.mysql.port');

            // Create mysqldump command
            $command = "mysqldump -h {$host} -P {$port} -u {$username}";
            if ($password) {
                $command .= " -p{$password}";
            }
            $command .= " {$database} > {$dbFilePath}";

            // Execute the command
            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                throw new \Exception('Database backup failed');
            }

            // Create ZIP archive
            $zip = new ZipArchive();
            if ($zip->open($tempZipPath, ZipArchive::CREATE) !== TRUE) {
                throw new \Exception('Could not create ZIP file');
            }

            // Add database backup to ZIP
            if (File::exists($dbFilePath)) {
                $zip->addFile($dbFilePath, 'database/' . $dbFilename);
            }

            // Add files to ZIP
            $this->addDirectoryToZip($zip, storage_path('app/public'), 'storage/app/public');
            $this->addDirectoryToZip($zip, public_path('uploads'), 'public/uploads');

            $zip->close();

            // Move to final location
            if (!Storage::move($this->tempPath . '/temp_full.zip', $filePath)) {
                // Fallback to copy and delete
                Storage::copy($this->tempPath . '/temp_full.zip', $filePath);
                Storage::delete($this->tempPath . '/temp_full.zip');
            }

            // Delete temporary database backup file
            if (file_exists($dbFilePath)) {
                unlink($dbFilePath);
            }

            // Get file size
            $size = file_exists($fullPath) ? filesize($fullPath) : 0;

            // Create backup record
            $backup = Backup::create([
                'name' => 'Full Backup - ' . date('Y-m-d H:i:s'),
                'filename' => $filename,
                'file_path' => $filePath,
                'type' => 'full',
                'size' => $size,
                'description' => $description,
                'status' => 'completed',
                'created_by' => auth()->id(),
                'company_id' => current_company_id(),
            ]);

            return $backup;

        } catch (\Exception $e) {
            // Create failed backup record
            $backup = Backup::create([
                'name' => 'Full Backup - ' . date('Y-m-d H:i:s'),
                'filename' => $filename ?? 'failed_backup.zip',
                'file_path' => $filePath ?? 'failed_backup.zip',
                'type' => 'full',
                'size' => 0,
                'description' => $description,
                'status' => 'failed',
                'created_by' => auth()->id(),
                'company_id' => current_company_id(),
            ]);

            throw $e;
        }
    }

    /**
     * Restore from backup
     */
    public function restoreBackup(Backup $backup)
    {
        try {
            $filePath = storage_path("app/{$backup->file_path}");
            
            if (!file_exists($filePath)) {
                throw new \Exception('Backup file not found');
            }

            if ($backup->type === 'database') {
                return $this->restoreDatabaseBackup($filePath);
            } elseif ($backup->type === 'files') {
                return $this->restoreFilesBackup($filePath);
            } elseif ($backup->type === 'full') {
                return $this->restoreFullBackup($filePath);
            }

            throw new \Exception('Unsupported backup type');

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Restore database from SQL file
     */
    protected function restoreDatabaseBackup($filePath)
    {
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');

        $command = "mysql -h {$host} -P {$port} -u {$username}";
        if ($password) {
            $command .= " -p{$password}";
        }
        $command .= " {$database} < {$filePath}";

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception('Database restore failed');
        }

        return true;
    }

    /**
     * Restore files from ZIP
     */
    protected function restoreFilesBackup($filePath)
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== TRUE) {
            throw new \Exception('Could not open ZIP file');
        }

        $zip->extractTo(storage_path('app/'));
        $zip->close();

        return true;
    }

    /**
     * Restore full backup
     */
    protected function restoreFullBackup($filePath)
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== TRUE) {
            throw new \Exception('Could not open ZIP file');
        }

        // Extract to temp directory
        $tempExtractPath = storage_path("app/{$this->tempPath}/restore");
        if (File::exists($tempExtractPath)) {
            File::deleteDirectory($tempExtractPath);
        }
        File::makeDirectory($tempExtractPath, 0755, true);

        $zip->extractTo($tempExtractPath);
        $zip->close();

        // Restore database
        $dbFile = $tempExtractPath . '/database/' . File::files($tempExtractPath . '/database')[0];
        $this->restoreDatabaseBackup($dbFile);

        // Restore files
        if (File::exists($tempExtractPath . '/storage')) {
            File::copyDirectory($tempExtractPath . '/storage', storage_path('app/'));
        }
        if (File::exists($tempExtractPath . '/public')) {
            File::copyDirectory($tempExtractPath . '/public', public_path());
        }

        // Clean up
        File::deleteDirectory($tempExtractPath);

        return true;
    }

    /**
     * Add directory to ZIP archive
     */
    protected function addDirectoryToZip($zip, $dirPath, $zipPath)
    {
        if (!File::exists($dirPath)) {
            return;
        }

        $files = File::allFiles($dirPath);
        foreach ($files as $file) {
            $relativePath = str_replace($dirPath . '/', '', $file->getPathname());
            $zip->addFile($file->getPathname(), $zipPath . '/' . $relativePath);
        }
    }

    /**
     * Get backup statistics
     */
    public function getBackupStats()
    {
        $companyId = current_company_id();
        
        return [
            'total' => Backup::forCompany()->count(),
            'database' => Backup::forCompany()->byType('database')->count(),
            'files' => Backup::forCompany()->byType('files')->count(),
            'full' => Backup::forCompany()->byType('full')->count(),
            'completed' => Backup::forCompany()->completed()->count(),
            'failed' => Backup::forCompany()->failed()->count(),
            'total_size' => Backup::forCompany()->completed()->sum('size'),
        ];
    }

    /**
     * Clean old backups
     */
    public function cleanOldBackups($days = 30)
    {
        $oldBackups = Backup::forCompany()
            ->where('created_at', '<', now()->subDays($days))
            ->get();

        foreach ($oldBackups as $backup) {
            $backup->deleteFile();
            $backup->delete();
        }

        return $oldBackups->count();
    }
} 