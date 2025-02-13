<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redis;
use Exception;

class HealthCheckService
{
    /**
     * Realizar una verificación completa del sistema
     */
    public function checkSystem(): array
    {
        return [
            'status' => $this->getOverallStatus(),
            'checks' => [
                'database' => $this->checkDatabase(),
                'cache' => $this->checkCache(),
                'storage' => $this->checkStorage(),
                'queue' => $this->checkQueue(),
                'memory' => $this->checkMemoryUsage(),
                'disk' => $this->checkDiskSpace(),
                'services' => $this->checkServices(),
                'security' => $this->checkSecurity(),
            ],
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Obtener el estado general del sistema
     */
    private function getOverallStatus(): string
    {
        try {
            $checks = [
                $this->checkDatabase()['status'],
                $this->checkCache()['status'],
                $this->checkStorage()['status'],
                $this->checkQueue()['status'],
                $this->checkMemoryUsage()['status'],
                $this->checkDiskSpace()['status'],
            ];

            return !in_array('error', $checks) ? 'healthy' : 'unhealthy';
        } catch (Exception $e) {
            return 'error';
        }
    }

    /**
     * Verificar la conexión a la base de datos
     */
    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            $dbStatus = [
                'status' => 'healthy',
                'message' => 'Database connection successful',
                'latency' => $this->measureDatabaseLatency(),
            ];
        } catch (Exception $e) {
            $dbStatus = [
                'status' => 'error',
                'message' => 'Database connection failed: ' . $e->getMessage(),
            ];
        }

        return $dbStatus;
    }

    /**
     * Verificar el sistema de caché
     */
    private function checkCache(): array
    {
        try {
            $key = 'health_check_test';
            Cache::put($key, 'test', 1);
            $value = Cache::get($key);
            Cache::forget($key);

            return [
                'status' => $value === 'test' ? 'healthy' : 'error',
                'message' => $value === 'test' ? 'Cache is working properly' : 'Cache test failed',
                'driver' => config('cache.default'),
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Cache check failed: ' . $e->getMessage(),
                'driver' => config('cache.default'),
            ];
        }
    }

    /**
     * Verificar el sistema de almacenamiento
     */
    private function checkStorage(): array
    {
        try {
            $testFile = 'health_check_test.txt';
            Storage::put($testFile, 'test');
            $content = Storage::get($testFile);
            Storage::delete($testFile);

            return [
                'status' => $content === 'test' ? 'healthy' : 'error',
                'message' => $content === 'test' ? 'Storage is working properly' : 'Storage test failed',
                'disk' => config('filesystems.default'),
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Storage check failed: ' . $e->getMessage(),
                'disk' => config('filesystems.default'),
            ];
        }
    }

    /**
     * Verificar el sistema de colas
     */
    private function checkQueue(): array
    {
        try {
            $connection = config('queue.default');
            $isRedis = $connection === 'redis';

            if ($isRedis) {
                Redis::ping();
            }

            return [
                'status' => 'healthy',
                'message' => 'Queue connection successful',
                'driver' => $connection,
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Queue check failed: ' . $e->getMessage(),
                'driver' => config('queue.default'),
            ];
        }
    }

    /**
     * Verificar el uso de memoria
     */
    private function checkMemoryUsage(): array
    {
        $memoryLimit = ini_get('memory_limit');
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);

        return [
            'status' => 'healthy',
            'memory_limit' => $memoryLimit,
            'current_usage' => $this->formatBytes($memoryUsage),
            'peak_usage' => $this->formatBytes($memoryPeak),
        ];
    }

    /**
     * Verificar el espacio en disco
     */
    private function checkDiskSpace(): array
    {
        $disk = storage_path();
        $totalSpace = disk_total_space($disk);
        $freeSpace = disk_free_space($disk);
        $usedSpace = $totalSpace - $freeSpace;
        $usedPercentage = ($usedSpace / $totalSpace) * 100;

        return [
            'status' => $usedPercentage < 90 ? 'healthy' : 'warning',
            'total_space' => $this->formatBytes($totalSpace),
            'free_space' => $this->formatBytes($freeSpace),
            'used_space' => $this->formatBytes($usedSpace),
            'used_percentage' => round($usedPercentage, 2) . '%',
        ];
    }

    /**
     * Verificar servicios críticos
     */
    private function checkServices(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'web_server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'ssl_enabled' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'environment' => config('app.env'),
            'debug_mode' => config('app.debug'),
        ];
    }

    /**
     * Verificar configuraciones de seguridad
     */
    private function checkSecurity(): array
    {
        return [
            'session_secure' => config('session.secure'),
            'session_http_only' => config('session.http_only'),
            'csrf_protection' => config('session.csrf_protection', true),
            'xss_protection' => true,
            'content_security_policy' => config('security.content_security_policy', false),
        ];
    }

    /**
     * Medir la latencia de la base de datos
     */
    private function measureDatabaseLatency(): float
    {
        $start = microtime(true);
        DB::select('SELECT 1');
        $end = microtime(true);
        return round(($end - $start) * 1000, 2); // Convertir a milisegundos
    }

    /**
     * Formatear bytes a unidades legibles
     */
    private function formatBytes($bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
