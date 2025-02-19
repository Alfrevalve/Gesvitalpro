<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CacheService;
use Illuminate\Support\Facades\Artisan;

class OptimizeSystemCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:optimize
                          {--clear-all : Limpiar todo el caché y archivos temporales}
                          {--refresh-cache : Refrescar el caché del sistema}
                          {--optimize-db : Optimizar índices de base de datos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimiza el sistema limpiando caché y archivos temporales';

    /**
     * @var CacheService
     */
    protected $cacheService;

    /**
     * Create a new command instance.
     */
    public function __construct(CacheService $cacheService)
    {
        parent::__construct();
        $this->cacheService = $cacheService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando optimización del sistema...');

        if ($this->option('clear-all')) {
            $this->clearAll();
        }

        if ($this->option('refresh-cache')) {
            $this->refreshCache();
        }

        if ($this->option('optimize-db')) {
            $this->optimizeDatabase();
        }

        if (!$this->option('clear-all') && !$this->option('refresh-cache') && !$this->option('optimize-db')) {
            $this->runDefaultOptimization();
        }

        $this->info('¡Optimización completada!');
    }

    /**
     * Limpiar todo el caché y archivos temporales
     */
    protected function clearAll()
    {
        $this->info('Limpiando todo el caché y archivos temporales...');

        Artisan::call('cache:clear');
        $this->info('Cache limpiado');

        Artisan::call('config:clear');
        $this->info('Configuración limpiada');

        Artisan::call('view:clear');
        $this->info('Vistas limpiadas');

        Artisan::call('route:clear');
        $this->info('Rutas limpiadas');

        Artisan::call('filament:assets');
        $this->info('Assets de Filament regenerados');

        // Limpiar archivos temporales
        $this->cleanTempFiles();
    }

    /**
     * Refrescar el caché del sistema
     */
    protected function refreshCache()
    {
        $this->info('Refrescando caché del sistema...');

        $this->cacheService->refreshAllCache();

        Artisan::call('config:cache');
        $this->info('Configuración cacheada');

        Artisan::call('route:cache');
        $this->info('Rutas cacheadas');

        Artisan::call('view:cache');
        $this->info('Vistas cacheadas');
    }

    /**
     * Optimizar la base de datos
     */
    protected function optimizeDatabase()
    {
        $this->info('Optimizando base de datos...');

        // Ejecutar migraciones pendientes
        if ($this->confirm('¿Desea ejecutar las migraciones pendientes?')) {
            Artisan::call('migrate', ['--force' => true]);
            $this->info('Migraciones ejecutadas');
        }

        // Optimizar índices
        if ($this->confirm('¿Desea optimizar los índices de la base de datos?')) {
            Artisan::call('migrate', [
                '--path' => 'database/migrations/2024_03_27_000001_optimize_performance_indexes.php',
                '--force' => true
            ]);
            $this->info('Índices optimizados');
        }
    }

    /**
     * Ejecutar optimización por defecto
     */
    protected function runDefaultOptimization()
    {
        $this->info('Ejecutando optimización por defecto...');

        // Limpiar caché obsoleto
        Artisan::call('cache:clear');

        // Regenerar caché crítico
        $this->cacheService->refreshAllCache();
        Artisan::call('config:cache');
        Artisan::call('route:cache');

        // Optimizar carga de clases
        Artisan::call('optimize');

        $this->info('Optimización por defecto completada');
    }

    /**
     * Limpiar archivos temporales
     */
    protected function cleanTempFiles()
    {
        $this->info('Limpiando archivos temporales...');

        $tempPaths = [
            storage_path('framework/cache/*'),
            storage_path('framework/views/*'),
            storage_path('framework/sessions/*'),
            storage_path('logs/*.log'),
        ];

        foreach ($tempPaths as $path) {
            array_map('unlink', glob($path));
        }

        $this->info('Archivos temporales limpiados');
    }
}
