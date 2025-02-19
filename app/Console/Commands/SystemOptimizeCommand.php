<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CacheOptimizationService;
use App\Services\SecurityMonitor;
use App\Services\PerformanceMonitor;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class SystemOptimizeCommand extends Command
{
    protected $signature = 'system:optimize
        {--clear-cache : Limpiar todo el caché del sistema}
        {--warm-cache : Precalentar el caché del sistema}
        {--security-check : Ejecutar verificación de seguridad}
        {--performance-check : Ejecutar verificación de rendimiento}';

    protected $description = 'Optimiza el sistema ejecutando varias tareas de mantenimiento';

    protected $cacheService;
    protected $securityMonitor;
    protected $performanceMonitor;

    public function __construct(
        CacheOptimizationService $cacheService,
        SecurityMonitor $securityMonitor,
        PerformanceMonitor $performanceMonitor
    ) {
        parent::__construct();
        $this->cacheService = $cacheService;
        $this->securityMonitor = $securityMonitor;
        $this->performanceMonitor = $performanceMonitor;
    }

    public function handle()
    {
        $this->info('🚀 Iniciando optimización del sistema...');

        try {
            // Limpiar caché si se solicita
            if ($this->option('clear-cache')) {
                $this->clearSystemCache();
            }

            // Precalentar caché si se solicita
            if ($this->option('warm-cache')) {
                $this->warmCache();
            }

            // Verificar seguridad si se solicita
            if ($this->option('security-check')) {
                $this->checkSecurity();
            }

            // Verificar rendimiento si se solicita
            if ($this->option('performance-check')) {
                $this->checkPerformance();
            }

            // Ejecutar optimizaciones generales
            $this->optimizeSystem();

            $this->info('✅ Optimización completada exitosamente!');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error durante la optimización: ' . $e->getMessage());
            Log::error('Error en system:optimize - ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    protected function clearSystemCache()
    {
        $this->info('🧹 Limpiando caché del sistema...');

        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');
        Artisan::call('optimize:clear');

        $this->info('✅ Caché del sistema limpiado');
    }

    protected function warmCache()
    {
        $this->info('🔥 Precalentando caché...');

        $this->cacheService->warmupSystemCache();

        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:cache');

        $this->info('✅ Caché precalentado');
    }

    protected function checkSecurity()
    {
        $this->info('🔒 Verificando seguridad...');

        $results = $this->securityMonitor->runSecurityCheck();

        foreach ($results as $check => $status) {
            $icon = $status ? '✅' : '❌';
            $this->line("{$icon} {$check}");
        }

        if (in_array(false, $results)) {
            $this->warn('⚠️ Se encontraron problemas de seguridad');
        } else {
            $this->info('✅ Verificación de seguridad completada');
        }
    }

    protected function checkPerformance()
    {
        $this->info('📊 Verificando rendimiento...');

        $metrics = $this->performanceMonitor->getPerformanceMetrics();

        $this->table(
            ['Métrica', 'Valor', 'Estado'],
            collect($metrics)->map(function ($metric) {
                return [
                    $metric['name'],
                    $metric['value'],
                    $metric['status'] ? '✅' : '❌'
                ];
            })->toArray()
        );

        $this->info('✅ Verificación de rendimiento completada');
    }

    protected function optimizeSystem()
    {
        $this->info('⚡ Ejecutando optimizaciones generales...');

        // Optimizar composer
        $this->info('🎼 Optimizando composer...');
        shell_exec('composer dump-autoload --optimize');

        // Optimizar npm
        if (file_exists(base_path('package.json'))) {
            $this->info('📦 Optimizando assets...');
            shell_exec('npm run build');
        }

        // Verificar y corregir permisos
        $this->info('🔑 Verificando permisos...');
        $this->fixPermissions();

        $this->info('✅ Optimizaciones generales completadas');
    }

    protected function fixPermissions()
    {
        $paths = [
            storage_path(),
            public_path('storage'),
            base_path('bootstrap/cache')
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                chmod($path, 0775);
                $this->line("✅ Permisos corregidos para: {$path}");
            }
        }
    }
}
