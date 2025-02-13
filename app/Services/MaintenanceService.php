<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class MaintenanceService
{
    /**
     * Limpiar logs antiguos del sistema
     */
    public function cleanOldLogs(int $days = 30): array
    {
        $stats = ['processed' => 0, 'deleted' => 0, 'size_freed' => 0];
        $cutoffDate = Carbon::now()->subDays($days);
        
        try {
            $logPath = storage_path('logs');
            if (!File::exists($logPath)) {
                return $stats;
            }

            $files = File::glob("{$logPath}/*.log");
            foreach ($files as $file) {
                $stats['processed']++;
                $lastModified = Carbon::createFromTimestamp(File::lastModified($file));
                
                if ($lastModified->lt($cutoffDate)) {
                    $stats['size_freed'] += File::size($file);
                    File::delete($file);
                    $stats['deleted']++;
                }
            }
        } catch (\Exception $e) {
            Log::error('Error limpiando logs antiguos', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return $stats;
    }

    /**
     * Limpiar sesiones expiradas
     */
    public function cleanExpiredSessions(int $days = 7): int
    {
        try {
            return DB::table('sessions')
                ->where('last_activity', '<', Carbon::now()->subDays($days)->timestamp)
                ->delete();
        } catch (\Exception $e) {
            Log::error('Error limpiando sesiones', [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Limpiar caché antiguo
     */
    public function cleanOldCache(): bool
    {
        try {
            $cachePath = storage_path('framework/cache/data');
            if (File::exists($cachePath)) {
                File::cleanDirectory($cachePath);
                return true;
            }
        } catch (\Exception $e) {
            Log::error('Error limpiando caché', [
                'error' => $e->getMessage()
            ]);
        }
        return false;
    }

    /**
     * Optimizar tablas de la base de datos
     */
    public function optimizeTables(): array
    {
        $results = [];
        try {
            $tables = DB::select('SHOW TABLES');
            foreach ($tables as $table) {
                $tableName = array_values((array)$table)[0];
                DB::statement("OPTIMIZE TABLE {$tableName}");
                $results[$tableName] = true;
            }
        } catch (\Exception $e) {
            Log::error('Error optimizando tablas', [
                'error' => $e->getMessage()
            ]);
        }
        return $results;
    }

    /**
     * Realizar mantenimiento completo del sistema
     */
    public function performMaintenance(): array
    {
        $results = [
            'logs' => $this->cleanOldLogs(),
            'sessions' => $this->cleanExpiredSessions(),
            'cache' => $this->cleanOldCache(),
            'database' => $this->optimizeTables()
        ];

        Log::info('Mantenimiento del sistema completado', $results);

        return $results;
    }

    /**
     * Verificar el estado del sistema
     */
    public function checkSystemHealth(): array
    {
        $health = [
            'disk_usage' => $this->getDiskUsage(),
            'database_size' => $this->getDatabaseSize(),
            'memory_usage' => $this->getMemoryUsage(),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'last_maintenance' => $this->getLastMaintenanceDate()
        ];

        return $health;
    }

    /**
     * Obtener uso del disco
     */
    protected function getDiskUsage(): array
    {
        $path = base_path();
        return [
            'total' => disk_total_space($path),
            'free' => disk_free_space($path),
            'used' => disk_total_space($path) - disk_free_space($path)
        ];
    }

    /**
     * Obtener tamaño de la base de datos
     */
    protected function getDatabaseSize(): int
    {
        try {
            $result = DB::select('SELECT SUM(data_length + index_length) AS size FROM information_schema.TABLES');
            return $result[0]->size ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Obtener uso de memoria
     */
    protected function getMemoryUsage(): array
    {
        return [
            'limit' => ini_get('memory_limit'),
            'usage' => memory_get_usage(true)
        ];
    }

    /**
     * Obtener fecha del último mantenimiento
     */
    protected function getLastMaintenanceDate(): ?string
    {
        try {
            return Cache::get('last_maintenance_date');
        } catch (\Exception $e) {
            return null;
        }
    }
}
