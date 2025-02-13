<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class CleanupOldRecords extends Command
{
    protected $signature = 'app:cleanup-records {--force} {--dry-run}';
    protected $description = 'Limpiar registros antiguos según la configuración';

    public function handle()
    {
        if (!config('monitoring.cleanup.enabled') && !$this->option('force')) {
            $this->warn('La limpieza automática está deshabilitada en la configuración.');
            return;
        }

        $this->info('Iniciando proceso de limpieza...');
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('Ejecutando en modo simulación (dry-run)...');
        }

        foreach (config('monitoring.cleanup.types') as $type => $config) {
            $this->cleanupType($type, $config, $isDryRun);
        }

        $this->cleanupLogFiles();

        $this->info('Proceso de limpieza completado.');
    }

    protected function cleanupType($type, $config, $isDryRun)
    {
        $this->info("Procesando limpieza de: {$type}");
        
        try {
            $date = Carbon::now()->subDays($config['older_than_days']);
            $batchSize = $config['batch_size'];

            switch ($type) {
                case 'audit_logs':
                    $this->cleanupAuditLogs($date, $batchSize, $isDryRun);
                    break;
                
                case 'system_logs':
                    $this->cleanupSystemLogs($date, $isDryRun);
                    break;
                
                case 'failed_jobs':
                    $this->cleanupFailedJobs($date, $batchSize, $isDryRun);
                    break;
                
                case 'sessions':
                    $this->cleanupSessions($date, $batchSize, $isDryRun);
                    break;
            }
        } catch (\Exception $e) {
            $this->error("Error al limpiar {$type}: " . $e->getMessage());
            Log::error("Error en limpieza de {$type}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    protected function cleanupAuditLogs($date, $batchSize, $isDryRun)
    {
        $query = DB::table('audit_logs')->where('created_at', '<', $date);
        $count = $query->count();

        if ($isDryRun) {
            $this->info("Se eliminarían {$count} registros de audit_logs");
            return;
        }

        $deleted = 0;
        while ($deleted < $count) {
            $affected = $query->limit($batchSize)->delete();
            $deleted += $affected;
            $this->info("Eliminados {$deleted} de {$count} registros de audit_logs");
            
            if ($affected < $batchSize) break;
            sleep(1); // Prevenir sobrecarga
        }
    }

    protected function cleanupSystemLogs($date, $isDryRun)
    {
        $logPath = storage_path('logs');
        $files = File::glob("{$logPath}/*.log");
        $deleted = 0;

        foreach ($files as $file) {
            $lastModified = Carbon::createFromTimestamp(File::lastModified($file));
            
            if ($lastModified->lt($date)) {
                if ($isDryRun) {
                    $this->info("Se eliminaría: " . basename($file));
                    continue;
                }

                File::delete($file);
                $deleted++;
            }
        }

        $this->info("Procesados {$deleted} archivos de log");
    }

    protected function cleanupFailedJobs($date, $batchSize, $isDryRun)
    {
        $query = DB::table('failed_jobs')->where('failed_at', '<', $date);
        $count = $query->count();

        if ($isDryRun) {
            $this->info("Se eliminarían {$count} trabajos fallidos");
            return;
        }

        $deleted = 0;
        while ($deleted < $count) {
            $affected = $query->limit($batchSize)->delete();
            $deleted += $affected;
            $this->info("Eliminados {$deleted} de {$count} trabajos fallidos");
            
            if ($affected < $batchSize) break;
            sleep(1);
        }
    }

    protected function cleanupSessions($date, $batchSize, $isDryRun)
    {
        $query = DB::table('sessions')->where('last_activity', '<', $date->timestamp);
        $count = $query->count();

        if ($isDryRun) {
            $this->info("Se eliminarían {$count} sesiones antiguas");
            return;
        }

        $deleted = 0;
        while ($deleted < $count) {
            $affected = $query->limit($batchSize)->delete();
            $deleted += $affected;
            $this->info("Eliminadas {$deleted} de {$count} sesiones");
            
            if ($affected < $batchSize) break;
            sleep(1);
        }
    }

    protected function cleanupLogFiles()
    {
        $logPath = storage_path('logs');
        $pattern = '/\.log(\.[0-9-]+)?$/';
        $files = File::glob("{$logPath}/*");
        $totalSize = 0;
        $deletedSize = 0;

        foreach ($files as $file) {
            if (!preg_match($pattern, $file)) continue;
            
            $totalSize += File::size($file);
            
            // Comprimir archivos antiguos
            if (Carbon::createFromTimestamp(File::lastModified($file))->lt(now()->subDays(7))) {
                $this->compressLogFile($file);
            }
        }

        $this->info(sprintf(
            "Tamaño total de logs: %s, Liberado: %s",
            $this->formatBytes($totalSize),
            $this->formatBytes($deletedSize)
        ));
    }

    protected function compressLogFile($file)
    {
        if (File::exists($file) && !File::exists($file . '.gz')) {
            $this->info("Comprimiendo: " . basename($file));
            system("gzip -9 " . escapeshellarg($file));
        }
    }

    protected function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
