<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class LaravelLogsController extends Controller
{
    public function index()
    {
        $logFiles = $this->getLogFiles();
        $logs = [];

        foreach ($logFiles as $file) {
            $logs[$file] = $this->parseLogFile($file);
        }

        return view('logs.laravel-logs', compact('logs', 'logFiles'));
    }

    public function clearLogs()
    {
        $logFiles = $this->getLogFiles();
        $clearedCount = 0;

        foreach ($logFiles as $file) {
            $logPath = storage_path('logs/' . $file);
            if (File::exists($logPath)) {
                // Clear the file content but keep the file
                File::put($logPath, '');
                $clearedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully cleared {$clearedCount} log files."
        ]);
    }


    private function getLogFiles()
    {
        $logPath = storage_path('logs');
        $files = File::files($logPath);

        $logFiles = [];
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'log') {
                $logFiles[] = $file->getFilename();
            }
        }

        return $logFiles;
    }

    private function parseLogFile($fileName)
    {
        $logPath = storage_path('logs/' . $fileName);

        if (!File::exists($logPath)) {
            return [];
        }

        $content = File::get($logPath);
        $lines = explode("\n", $content);

        $logs = [];
        $currentLog = null;

        foreach ($lines as $line) {
            if (empty(trim($line))) {
                continue;
            }

            // Check if this line starts a new log entry (contains timestamp)
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\w+)\.(\w+): (.+)$/', $line, $matches)) {
                // Save previous log if exists
                if ($currentLog) {
                    $logs[] = $currentLog;
                }

                // Start new log entry
                $currentLog = [
                    'timestamp' => $matches[1],
                    'environment' => $matches[2],
                    'level' => $matches[3],
                    'message' => $matches[4],
                    'context' => '',
                    'stack_trace' => '',
                    'is_error' => in_array(strtolower($matches[3]), ['error', 'critical', 'emergency', 'alert'])
                ];
            } else {
                // This is a continuation of the current log entry
                if ($currentLog) {
                    if (strpos($line, 'Stack trace:') !== false || strpos($line, 'at ') !== false) {
                        $currentLog['stack_trace'] .= $line . "\n";
                    } else {
                        $currentLog['context'] .= $line . "\n";
                    }
                }
            }
        }

        // Add the last log entry
        if ($currentLog) {
            $logs[] = $currentLog;
        }

        // Reverse to show newest first
        return array_reverse($logs);
    }
}
