<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\OptimizedCacheService;
use App\Services\PerformanceMonitor;
use App\Console\Commands\CacheMaintenanceCommand;

class CacheOptimizationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Registrar OptimizedCacheService como singleton
        $this->app->singleton(OptimizedCacheService::class, function ($app) {
            return new OptimizedCacheService(
                $app->make(PerformanceMonitor::class)
            );
        });

        // Registrar el comando de mantenimiento
        if ($this->app->runningInConsole()) {
            $this->commands([
                CacheMaintenanceCommand::class,
            ]);
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Configurar listeners para eventos de caché
        $this->app['events']->listen('cache.hit', function ($key, $value) {
            $performanceMonitor = $this->app->make(PerformanceMonitor::class);
            $performanceMonitor->recordCacheMetrics(true);
        });

        $this->app['events']->listen('cache.missed', function ($key) {
            $performanceMonitor = $this->app->make(PerformanceMonitor::class);
            $performanceMonitor->recordCacheMetrics(false);
        });

        // Configurar listeners para eventos de consultas lentas
        $this->app['events']->listen('illuminate.query', function ($query, $bindings, $time) {
            $performanceMonitor = $this->app->make(PerformanceMonitor::class);
            $performanceMonitor->monitorQuery($query, $time, $bindings);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            OptimizedCacheService::class,
        ];
    }
}
