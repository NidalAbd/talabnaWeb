<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SystemHealthController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $systemHealth = [
            'database' => $this->checkDatabaseHealth(),
            'storage' => $this->checkStorageHealth(),
            'cache' => $this->checkCacheHealth(),
            'performance' => $this->checkPerformanceMetrics(),
            'errors' => $this->getRecentErrors(),
        ];

        return view('admin.system.health', compact('systemHealth'));
    }

    private function checkDatabaseHealth()
    {
        try {
            DB::connection()->getPdo();
            $tables = DB::select('SHOW TABLES');
            
            return [
                'status' => 'healthy',
                'connection' => 'connected',
                'tables_count' => count($tables),
                'last_check' => now(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'connection' => 'disconnected',
                'error' => $e->getMessage(),
                'last_check' => now(),
            ];
        }
    }

    private function checkStorageHealth()
    {
        $disk = Storage::disk('public');
        
        try {
            $totalSpace = disk_total_space(storage_path());
            $freeSpace = disk_free_space(storage_path());
            $usedSpace = $totalSpace - $freeSpace;
            $usagePercentage = ($usedSpace / $totalSpace) * 100;

            return [
                'status' => $usagePercentage < 90 ? 'healthy' : 'warning',
                'total_space' => $this->formatBytes($totalSpace),
                'free_space' => $this->formatBytes($freeSpace),
                'used_space' => $this->formatBytes($usedSpace),
                'usage_percentage' => round($usagePercentage, 2),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    private function checkCacheHealth()
    {
        try {
            $testKey = 'health_check_' . time();
            Cache::put($testKey, 'test', 60);
            $retrieved = Cache::get($testKey);
            Cache::forget($testKey);

            return [
                'status' => $retrieved === 'test' ? 'healthy' : 'error',
                'driver' => config('cache.default'),
                'last_check' => now(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
                'last_check' => now(),
            ];
        }
    }

    private function checkPerformanceMetrics()
    {
        return [
            'memory_usage' => $this->formatBytes(memory_get_usage(true)),
            'peak_memory' => $this->formatBytes(memory_get_peak_usage(true)),
            'execution_time' => round(microtime(true) - LARAVEL_START, 4) . 's',
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
        ];
    }

    private function getRecentErrors()
    {
        $logFile = storage_path('logs/laravel.log');
        
        if (!file_exists($logFile)) {
            return [];
        }

        $logContent = file_get_contents($logFile);
        $lines = explode("\n", $logContent);
        $errorLines = array_filter($lines, function($line) {
            return strpos($line, 'ERROR') !== false || strpos($line, 'CRITICAL') !== false;
        });

        return array_slice(array_reverse($errorLines), 0, 10);
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
} 