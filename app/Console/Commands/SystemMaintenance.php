<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\ActivityLog;

class SystemMaintenance extends Command
{
    protected $signature = 'system:maintenance 
        {--force : Force maintenance regardless of schedule}
        {--only= : Run only specific maintenance tasks (cleanup,optimize,cache)}';

    protected $description = 'Ejecuta tareas de mantenimiento del sistema';

    public function handle()
    {
        $this->info('Iniciando mantenimiento del sistema...');
        
        $tasks = $this->option('only') 
            ? explode(',', $this->option('only'))
            : ['cleanup', 'optimize', 'cache'];

        try {
            Log::channel('audit')->info('Iniciando mantenimiento del sistema', [
                'tasks' => $tasks,
                'forced' => $this->option('force'),
            ]);

            foreach ($tasks as $task) {
                $this->runTask($task);
            }

            $this->info('Mantenimiento completado exitosamente.');
            Log::channel('audit')->info('Mantenimiento del sistema completado');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error durante el mantenimiento: ' . $e->getMessage());
            Log::channel('audit')->error('Error durante el mantenimiento', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }

    protected function runTask(string $task)
    {
        $this->info("Ejecutando tarea: {$task}");

        match($task) {
            'cleanup' => $this->cleanupOldData(),
            'optimize' => $this->optimizeDatabase(),
            'cache' => $this->manageCacheData(),
            default => $this->warn("Tarea desconocida: {$task}")
        };
    }

    protected function cleanupOldData()
    {
        $config = config('audit.maintenance.database.auto_cleanup');
        
        if (!$config['enabled'] && !$this->option('force')) {
            $this->info('Limpieza automática deshabilitada');
            return;
        }

        $date = Carbon::now()->subDays($config['older_than_days']);

        // Limpiar logs antiguos
        $deleted = ActivityLog::where('created_at', '<', $date)->delete();
        $this->info("Se eliminaron {$deleted} registros de actividad antiguos");

        // Limpiar archivos de log
        $this->cleanLogFiles();

        // Limpiar datos temporales
        $this->cleanTempData();
    }

    protected function optimizeDatabase()
    {
        $config = config('audit.maintenance.database.auto_optimize');
        
        if (!$config['enabled'] && !$this->option('force')) {
            $this->info('Optimización automática deshabilitada');
            return;
        }

        // Analizar y optimizar tablas
        $tables = $this->getApplicationTables();
        foreach ($tables as $table) {
            $this->info("Optimizando tabla: {$table}");
            DB::statement("ANALYZE TABLE {$table}");
            DB::statement("OPTIMIZE TABLE {$table}");
        }
    }

    protected function manageCacheData()
    {
        $config = config('audit.maintenance.cache');
        
        if (!$config['auto_cleanup']['enabled'] && !$this->option('force')) {
            $this->info('Limpieza de caché deshabilitada');
            return;
        }

        // Limpiar caché del sistema
        $this->info('Limpiando caché del sistema...');
        Cache::flush();

        // Regenerar caché crítica
        $this->info('Regenerando caché crítica...');
        
        // Caché de equipamiento disponible
        Cache::put('available_equipment', 
            \App\Models\Equipment::where('status', 'available')->get()->toArray(),
            now()->addMinutes(60)
        );

        // Caché de próximas cirugías
        Cache::put('upcoming_surgeries',
            \App\Models\Surgery::where('surgery_date', '>', now())
                ->where('status', 'programmed')
                ->take(10)
                ->get()
                ->toArray(),
            now()->addMinutes(30)
        );

        // Caché de estadísticas del dashboard
        Cache::put('dashboard_stats', [
            'total_surgeries' => \App\Models\Surgery::count(),
            'available_equipment' => \App\Models\Equipment::where('status', 'available')->count(),
            'maintenance_needed' => \App\Models\Equipment::where('next_maintenance', '<=', now())->count(),
        ], now()->addHours(1));

        $this->info('Caché regenerada exitosamente');
    }

    protected function cleanLogFiles()
    {
        $logPath = storage_path('logs');
        $retentionDays = config('audit.logging.activity.retention_days', 90);
        $date = Carbon::now()->subDays($retentionDays);

        foreach (glob("{$logPath}/*.log") as $file) {
            if (Carbon::createFromTimestamp(filemtime($file))->lt($date)) {
                unlink($file);
                $this->info("Archivo de log eliminado: " . basename($file));
            }
        }
    }

    protected function cleanTempData()
    {
        // Limpiar archivos temporales
        $tempPath = storage_path('app/temp');
        if (is_dir($tempPath)) {
            foreach (glob("{$tempPath}/*") as $file) {
                if (is_file($file) && time() - filemtime($file) > 86400) {
                    unlink($file);
                }
            }
        }

        // Limpiar sesiones expiradas
        $this->cleanExpiredSessions();
    }

    protected function cleanExpiredSessions()
    {
        $sessionPath = config('session.files');
        $lifetime = config('session.lifetime') * 60;

        if ($sessionPath && is_dir($sessionPath)) {
            foreach (glob("{$sessionPath}/*") as $file) {
                if (is_file($file) && time() - filemtime($file) > $lifetime) {
                    unlink($file);
                }
            }
        }
    }

    protected function getApplicationTables(): array
    {
        return DB::connection()->getDoctrineSchemaManager()->listTableNames();
    }
}
