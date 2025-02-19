<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Services\CacheOptimizationService;
use App\Services\SecurityMonitor;
use App\Services\PerformanceMonitor;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Contracts\Role as RoleContract;
use App\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registrar servicios como singletons
        $this->app->singleton(CacheOptimizationService::class);
        $this->app->singleton(SecurityMonitor::class);
        $this->app->singleton(PerformanceMonitor::class);

        // Bind Role model
        $this->app->bind(RoleContract::class, Role::class);
        $this->app->bind('role', Role::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            // Establecer longitud por defecto para strings en migraciones
            Schema::defaultStringLength(191);

            // Precalentar caché si está configurado
            if (config('cache.warm_cache_on_boot')) {
                $this->app->make(CacheOptimizationService::class)->warmupSystemCache();
            }

            // Iniciar monitoreo de rendimiento en producción
            if (config('app.env') === 'production') {
                $performanceMonitor = $this->app->make(PerformanceMonitor::class);
                try {
                    $performanceMonitor->startMonitoring();
                } catch (\Exception $e) {
                    Log::error('Error al iniciar monitoreo de rendimiento: ' . $e->getMessage());
                }
            }

        } catch (\Exception $e) {
            Log::error('Error en AppServiceProvider::boot - ' . $e->getMessage());
        }
    }
}
