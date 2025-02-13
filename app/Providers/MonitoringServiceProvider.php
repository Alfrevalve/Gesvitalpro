<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Services\SystemMonitoringService;
use App\Models\PerformanceMetric;

class MonitoringServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(SystemMonitoringService::class);

        $this->mergeConfigFrom(
            __DIR__.'/../../config/monitoring.php', 'monitoring'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (!config('monitoring.enabled')) {
            return;
        }

        $this->registerEventListeners();
        $this->registerQueueListeners();
        $this->registerDatabaseListeners();
        $this->registerCacheListeners();
        $this->registerScheduledTasks();
        $this->publishAssets();
    }

    /**
     * Registrar listeners de eventos
     */
    protected function registerEventListeners(): void
    {
        if (!config('monitoring.performance.tracking.events')) {
            return;
        }

        Event::listen('*', function ($event, $data) {
            // Skip monitoring events to prevent recursion
            if ($this->shouldSkipEvent($event)) {
                return;
            }
            
            $eventName = is_object($event) ? get_class($event) : $event;
            
            try {
                PerformanceMetric::unguarded(function () use ($eventName, $data) {
                    PerformanceMetric::record(
                        'event',
                        'dispatched',
                        1,
                        null,
                        [
                            'event' => $eventName,
                            'data' => $this->serializeEventData($data),
                        ]
                    );
                });
            } catch (\Exception $e) {
                // Log error but don't re-throw to prevent cascading failures
                \Log::error('Failed to record performance metric', [
                    'event' => $eventName,
                    'error' => $e->getMessage()
                ]);
            }
        });
    }

    /**
     * Registrar listeners de cola
     */
    protected function registerQueueListeners(): void
    {
        if (!config('monitoring.performance.tracking.jobs')) {
            return;
        }

        Queue::before(function ($job) {
            $job->monitoringStartTime = microtime(true);
        });

        Queue::after(function ($job) {
            if (isset($job->monitoringStartTime)) {
                $duration = (microtime(true) - $job->monitoringStartTime) * 1000;
                
                PerformanceMetric::record(
                    'job',
                    'processed',
                    $duration,
                    'ms',
                    [
                        'job' => get_class($job),
                        'queue' => $job->queue,
                        'attempt' => $job->attempts(),
                    ]
                );
            }
        });

        Queue::failing(function ($job, $exception) {
            PerformanceMetric::record(
                'job',
                'failed',
                1,
                null,
                [
                    'job' => get_class($job),
                    'queue' => $job->queue,
                    'attempt' => $job->attempts(),
                    'exception' => [
                        'class' => get_class($exception),
                        'message' => $exception->getMessage(),
                    ],
                ]
            );
        });
    }

    /**
     * Registrar listeners de base de datos
     */
    protected function registerDatabaseListeners(): void
    {
        if (!config('monitoring.performance.tracking.queries')) {
            return;
        }

        DB::listen(function ($query) {
            if ($query->time >= config('monitoring.performance.slow_threshold.query', 100)) {
                PerformanceMetric::record(
                    'database',
                    'slow_query',
                    $query->time,
                    'ms',
                    [
                        'sql' => $query->sql,
                        'bindings' => $query->bindings,
                        'connection' => $query->connection->getName(),
                    ]
                );
            }
        });
    }

    /**
     * Registrar listeners de caché
     */
    protected function registerCacheListeners(): void
    {
        if (!config('monitoring.performance.tracking.cache')) {
            return;
        }

        $events = [
            'cache.hit', 'cache.missed', 'cache.written', 'cache.deleted',
        ];

        foreach ($events as $event) {
            Event::listen($event, function ($key) use ($event) {
                PerformanceMetric::record(
                    'cache',
                    str_replace('cache.', '', $event),
                    1,
                    null,
                    ['key' => $key]
                );
            });
        }
    }

    /**
     * Registrar tareas programadas
     */
    protected function registerScheduledTasks(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(\Illuminate\Console\Scheduling\Schedule::class);
            
            // Limpiar métricas antiguas
            $schedule->command('metrics:cleanup')
                    ->daily()
                    ->at('01:00')
                    ->withoutOverlapping();

            // Generar reporte diario
            $schedule->command('system:report --type=daily')
                    ->dailyAt('23:59')
                    ->withoutOverlapping();
        });
    }

    /**
     * Publicar assets
     */
    protected function publishAssets(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/monitoring.php' => config_path('monitoring.php'),
            ], 'monitoring-config');

            $this->publishes([
                __DIR__.'/../../resources/views/monitoring' => resource_path('views/vendor/monitoring'),
            ], 'monitoring-views');
        }
    }

    /**
     * Determine if an event should be skipped for monitoring
     */
    protected function shouldSkipEvent($event): bool
    {
        $skipEvents = [
            'eloquent.*',
            'creating*',
            'created*',
            'updating*',
            'updated*',
            'deleting*',
            'deleted*',
            'saving*',
            'saved*',
            'restoring*',
            'restored*',
            'event.dispatched',
        ];

        $eventName = is_object($event) ? get_class($event) : $event;
        
        foreach ($skipEvents as $pattern) {
            if (fnmatch($pattern, $eventName)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Serializar datos de eventos para almacenamiento
     */
    protected function serializeEventData($data): array
    {
        return collect($data)->map(function ($item) {
            if (is_object($item)) {
                return [
                    'class' => get_class($item),
                    'id' => method_exists($item, 'getKey') ? $item->getKey() : null,
                ];
            }
            return $item;
        })->toArray();
    }
}
