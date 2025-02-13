<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MaintenanceService;
use Illuminate\Support\Facades\Cache;

class SystemMaintenance extends Command
{
    protected $signature = 'system:maintenance {--check} {--force}';
    protected $description = 'Realizar mantenimiento del sistema';

    protected $maintenanceService;

    public function __construct(MaintenanceService $maintenanceService)
    {
        parent::__construct();
        $this->maintenanceService = $maintenanceService;
    }

    public function handle()
    {
        if ($this->option('check')) {
            return $this->checkHealth();
        }

        if (!config('system.cleanup.enabled') && !$this->option('force')) {
            $this->warn('El mantenimiento automático está deshabilitado.');
            if (!$this->confirm('¿Desea continuar de todos modos?')) {
                return;
            }
        }

        $this->info('Iniciando mantenimiento del sistema...');

        // Realizar mantenimiento
        $results = $this->maintenanceService->performMaintenance();

        // Mostrar resultados de limpieza de logs
        $this->info('Limpieza de logs:');
        $this->table(
            ['Procesados', 'Eliminados', 'Espacio Liberado'],
            [[
                $results['logs']['processed'],
                $results['logs']['deleted'],
                $this->formatBytes($results['logs']['size_freed'])
            ]]
        );

        // Mostrar resultados de limpieza de sesiones
        $this->info('Sesiones eliminadas: ' . $results['sessions']);

        // Mostrar estado del caché
        $this->info('Limpieza de caché: ' . ($results['cache'] ? 'Completada' : 'Fallida'));

        // Mostrar resultados de optimización de base de datos
        $this->info('Optimización de base de datos:');
        $optimizedTables = array_filter($results['database']);
        if (count($optimizedTables) > 0) {
            $this->info('Tablas optimizadas: ' . count($optimizedTables));
        } else {
            $this->warn('No se optimizaron tablas');
        }

        // Guardar fecha del último mantenimiento
        Cache::put('last_maintenance_date', now()->toDateTimeString(), now()->addDays(30));

        $this->info('Mantenimiento completado exitosamente.');
    }

    protected function checkHealth()
    {
        $this->info('Verificando estado del sistema...');

        $health = $this->maintenanceService->checkSystemHealth();

        // Mostrar uso de disco
        $this->info('Uso de Disco:');
        $this->table(
            ['Total', 'Usado', 'Libre', 'Porcentaje Usado'],
            [[
                $this->formatBytes($health['disk_usage']['total']),
                $this->formatBytes($health['disk_usage']['used']),
                $this->formatBytes($health['disk_usage']['free']),
                round(($health['disk_usage']['used'] / $health['disk_usage']['total']) * 100, 2) . '%'
            ]]
        );

        // Mostrar uso de memoria
        $this->info('Uso de Memoria:');
        $this->table(
            ['Límite', 'Uso Actual'],
            [[
                $health['memory_usage']['limit'],
                $this->formatBytes($health['memory_usage']['usage'])
            ]]
        );

        // Mostrar información del sistema
        $this->info('Información del Sistema:');
        $this->table(
            ['PHP Version', 'Laravel Version', 'Último Mantenimiento'],
            [[
                $health['php_version'],
                $health['laravel_version'],
                $health['last_maintenance'] ?? 'Nunca'
            ]]
        );

        // Mostrar tamaño de la base de datos
        $this->info('Tamaño de la Base de Datos: ' . $this->formatBytes($health['database_size']));
    }

    protected function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
