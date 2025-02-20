<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\QueryOptimizationService;
use App\Services\PerformanceMonitor;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\Events\QueryExecuted;

class QueryOptimizationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(QueryOptimizationService::class, function ($app) {
            return new QueryOptimizationService(
                $app->make(PerformanceMonitor::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Monitorear todas las consultas SQL
        Event::listen(QueryExecuted::class, function (QueryExecuted $query) {
            $queryOptimizer = $this->app->make(QueryOptimizationService::class);

            // Registrar consultas lentas
            $queryOptimizer->logSlowQuery(
                $query->sql,
                $query->bindings,
                $query->time / 1000 // Convertir a segundos
            );

            // Monitorear rendimiento general
            $performanceMonitor = $this->app->make(PerformanceMonitor::class);
            $performanceMonitor->monitorQuery(
                $query->sql,
                $query->time / 1000,
                $query->bindings
            );
        });

        // Registrar métricas periódicamente
        if ($this->app->runningInConsole()) {
            $schedule = $this->app->make(\Illuminate\Console\Scheduling\Schedule::class);

            $schedule->call(function () {
                $queryOptimizer = $this->app->make(QueryOptimizationService::class);
                $recommendations = $queryOptimizer->generateOptimizationRecommendations();

                // Registrar recomendaciones si hay problemas críticos
                $hasCritical = collect($recommendations)->contains('type', 'critical');
                if ($hasCritical) {
                    \Log::warning('Se detectaron problemas críticos en consultas SQL', [
                        'recommendations' => $recommendations
                    ]);
                }
            })->hourly();
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            QueryOptimizationService::class,
        ];
    }
}
