<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\OptimizedCacheService;
use App\Services\PerformanceMonitor;

class CacheMaintenanceCommand extends Command
{
    protected $signature = 'cache:maintenance
                          {action=status : Action to perform (status|cleanup|preload|stats)}';

    protected $description = 'Gestiona el mantenimiento del caché del sistema';

    protected $cacheService;
    protected $performanceMonitor;

    public function __construct(
        OptimizedCacheService $cacheService,
        PerformanceMonitor $performanceMonitor
    ) {
        parent::__construct();
        $this->cacheService = $cacheService;
        $this->performanceMonitor = $performanceMonitor;
    }

    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'cleanup':
                $this->cleanup();
                break;
            case 'preload':
                $this->preload();
                break;
            case 'stats':
                $this->showStats();
                break;
            case 'status':
            default:
                $this->showStatus();
                break;
        }
    }

    protected function cleanup()
    {
        $this->info('Iniciando limpieza programada del caché...');

        try {
            $this->cacheService->runScheduledCleanup();
            $this->info('Limpieza de caché completada exitosamente.');
        } catch (\Exception $e) {
            $this->error("Error durante la limpieza: {$e->getMessage()}");
        }
    }

    protected function preload()
    {
        $this->info('Iniciando precarga de datos frecuentes...');

        try {
            $this->cacheService->preloadFrequentData();
            $this->info('Precarga completada exitosamente.');
        } catch (\Exception $e) {
            $this->error("Error durante la precarga: {$e->getMessage()}");
        }
    }

    protected function showStats()
    {
        $stats = $this->cacheService->getCacheStats();

        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Hit Ratio', number_format($stats['hit_ratio'] * 100, 2) . '%'],
                ['Duración Promedio', number_format($stats['average_duration'], 4) . ' seg'],
                ['Uso de Memoria', $stats['memory_usage']],
                ['Total de Claves', $stats['total_keys']],
            ]
        );

        // Mostrar métricas de rendimiento
        $performanceReport = $this->performanceMonitor->generatePerformanceReport();

        $this->info("\nMétricas de Rendimiento:");
        $this->table(
            ['Categoría', 'Métrica', 'Valor'],
            collect($performanceReport)->flatMap(function ($metrics, $category) {
                return collect($metrics)->map(function ($value, $metric) use ($category) {
                    return [$category, $metric, is_numeric($value) ? number_format($value, 2) : $value];
                });
            })->toArray()
        );
    }

    protected function showStatus()
    {
        $stats = $this->cacheService->getCacheStats();

        $this->info('Estado del Sistema de Caché:');
        $this->table(
            ['Componente', 'Estado'],
            [
                ['Redis', $stats['memory_usage'] !== 'N/A' ? '✅ Conectado' : '❌ No Conectado'],
                ['Rendimiento', $stats['hit_ratio'] > 0.7 ? '✅ Óptimo' : '⚠️ Necesita Revisión'],
                ['Memoria', $this->getMemoryStatus($stats['memory_usage'])],
            ]
        );

        if ($this->performanceMonitor->shouldShowPerformanceAlert()) {
            $this->warn($this->performanceMonitor->getPerformanceAlertMessage());
        }
    }

    protected function getMemoryStatus($memoryUsage)
    {
        if ($memoryUsage === 'N/A') {
            return '❌ No Disponible';
        }

        $usage = (int) $memoryUsage;
        if ($usage < 50) {
            return '✅ Bajo Uso';
        } elseif ($usage < 80) {
            return '⚠️ Uso Moderado';
        } else {
            return '❌ Uso Alto';
        }
    }
}
