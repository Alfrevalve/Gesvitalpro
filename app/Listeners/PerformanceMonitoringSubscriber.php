<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use App\Models\PerformanceMetric;
use App\Models\SystemAlert;
use App\Models\User;
use App\Notifications\SystemAlertNotification;

class PerformanceMonitoringSubscriber
{
    /**
     * Registrar los listeners para el subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     * @return array
     */
    public function subscribe($events)
    {
        return [
            'App\Events\SlowRequestDetected' => [
                [PerformanceMonitoringSubscriber::class, 'handleSlowRequest'],
            ],
            'App\Events\HighMemoryUsageDetected' => [
                [PerformanceMonitoringSubscriber::class, 'handleHighMemoryUsage'],
            ],
            'App\Events\SlowQueriesDetected' => [
                [PerformanceMonitoringSubscriber::class, 'handleSlowQueries'],
            ],
            'App\Events\LowCacheHitRateDetected' => [
                [PerformanceMonitoringSubscriber::class, 'handleLowCacheHitRate'],
            ],
            'App\Events\HighSystemLoadDetected' => [
                [PerformanceMonitoringSubscriber::class, 'handleHighSystemLoad'],
            ],
            'App\Events\DatabasePerformanceIssueDetected' => [
                [PerformanceMonitoringSubscriber::class, 'handleDatabaseIssue'],
            ],
            'App\Events\QueuePerformanceIssueDetected' => [
                [PerformanceMonitoringSubscriber::class, 'handleQueueIssue'],
            ],
            'App\Events\ApiPerformanceIssueDetected' => [
                [PerformanceMonitoringSubscriber::class, 'handleApiIssue'],
            ],
        ];
    }

    /**
     * Manejar petición lenta detectada
     */
    public function handleSlowRequest($event)
    {
        $this->recordMetric('request_duration', [
            'duration' => $event->duration,
            'route' => $event->route,
            'queries_count' => count($event->queries),
        ]);

        if ($this->isSignificantSlowdown($event->duration)) {
            $this->createAlert(
                'warning',
                'slow_request',
                "Slow request detected on route {$event->route}",
                [
                    'duration' => $event->duration,
                    'queries' => $event->queries,
                ]
            );
        }
    }

    /**
     * Manejar uso alto de memoria detectado
     */
    public function handleHighMemoryUsage($event)
    {
        $this->recordMetric('memory_usage', [
            'usage' => $event->memoryUsage,
            'route' => $event->route,
        ]);

        if ($event->memoryUsage > config('monitoring.performance.thresholds.critical_memory', 256)) {
            $this->handleCriticalMemoryUsage($event);
        }
    }

    /**
     * Manejar consultas lentas detectadas
     */
    public function handleSlowQueries($event)
    {
        $this->recordMetric('slow_queries', [
            'count' => $event->getSlowQueriesCount(),
            'average_time' => $event->getAverageQueryTime(),
            'route' => $event->route,
        ]);

        $slowestQuery = $event->getSlowestQuery();
        if ($slowestQuery) {
            $this->analyzeSlowQuery($slowestQuery);
        }
    }

    /**
     * Manejar tasa baja de aciertos de caché
     */
    public function handleLowCacheHitRate($event)
    {
        $this->recordMetric('cache_hit_rate', [
            'rate' => $event->hitRate,
            'hits' => $event->hits,
            'misses' => $event->misses,
        ]);

        if ($event->hitRate < config('monitoring.performance.thresholds.critical_cache_rate', 50)) {
            $this->analyzeCachePerformance($event);
        }
    }

    /**
     * Manejar carga alta del sistema
     */
    public function handleHighSystemLoad($event)
    {
        $this->recordMetric('system_load', [
            'cpu' => $event->cpuLoad,
            'memory' => $event->memoryUsage,
            'disk' => $event->diskUsage,
        ]);

        if ($event->isCritical()) {
            $this->handleCriticalSystemLoad($event);
        }
    }

    /**
     * Manejar problema de rendimiento de base de datos
     */
    public function handleDatabaseIssue($event)
    {
        $this->recordMetric('database_issue', [
            'type' => $event->type,
            'details' => $event->details,
        ]);

        if ($event->isCritical()) {
            $this->handleCriticalDatabaseIssue($event);
        }
    }

    /**
     * Manejar problema de rendimiento de cola
     */
    public function handleQueueIssue($event)
    {
        $this->recordMetric('queue_performance', [
            'queue' => $event->queueName,
            'jobs_count' => $event->jobsCount,
            'failed_count' => $event->failedCount,
            'wait_time' => $event->averageWaitTime,
        ]);

        if ($event->isCritical()) {
            $this->handleCriticalQueueIssue($event);
        }
    }

    /**
     * Manejar problema de rendimiento de API
     */
    public function handleApiIssue($event)
    {
        $this->recordMetric('api_performance', [
            'endpoint' => $event->endpoint,
            'method' => $event->method,
            'response_time' => $event->responseTime,
            'error_rate' => $event->errorRate,
        ]);

        if ($event->isCritical()) {
            $this->handleCriticalApiIssue($event);
        }
    }

    /**
     * Registrar una métrica
     */
    protected function recordMetric(string $name, array $data): void
    {
        PerformanceMetric::create([
            'name' => $name,
            'value' => $data,
            'recorded_at' => now(),
        ]);
    }

    /**
     * Crear una alerta
     */
    protected function createAlert(
        string $type,
        string $category,
        string $message,
        array $metadata = []
    ): void {
        SystemAlert::create([
            'type' => $type,
            'category' => $category,
            'message' => $message,
            'metadata' => $metadata,
        ]);

        $this->notifyAdministrators($type, $message, $metadata);
    }

    /**
     * Notificar a los administradores
     */
    protected function notifyAdministrators(
        string $type,
        string $message,
        array $metadata = []
    ): void {
        $admins = User::role('admin')->get();

        $urgencyLevel = $this->determineUrgencyLevel($type, $metadata);
        
        Notification::send($admins, new SystemAlertNotification(
            [
                'message' => $message,
                'context' => $metadata,
            ],
            $this->getNotificationChannels($urgencyLevel),
            $urgencyLevel
        ));
    }

    /**
     * Determinar nivel de urgencia
     */
    protected function determineUrgencyLevel(string $type, array $metadata): string
    {
        if ($type === 'critical') {
            return 'critical';
        }

        if (isset($metadata['threshold_exceeded'])) {
            return 'high';
        }

        return 'medium';
    }

    /**
     * Obtener canales de notificación
     */
    protected function getNotificationChannels(string $urgencyLevel): array
    {
        $channels = ['database'];

        if ($urgencyLevel === 'critical') {
            $channels[] = 'mail';
            $channels[] = 'slack';
        } elseif ($urgencyLevel === 'high') {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Verificar si es una desaceleración significativa
     */
    protected function isSignificantSlowdown(float $duration): bool
    {
        $threshold = config('monitoring.performance.thresholds.request_duration', 1000);
        return $duration > $threshold;
    }

    /**
     * Manejar uso crítico de memoria
     */
    protected function handleCriticalMemoryUsage($event): void
    {
        $this->createAlert(
            'critical',
            'memory_usage',
            "Critical memory usage detected: {$event->memoryUsage}MB",
            [
                'route' => $event->route,
                'threshold_exceeded' => true,
            ]
        );

        // Ejecutar acciones de mitigación
        $this->executeMemoryMitigationActions();
    }

    /**
     * Analizar consulta lenta
     */
    protected function analyzeSlowQuery(array $query): void
    {
        // Implementar análisis de consulta SQL
        // Por ejemplo, verificar índices faltantes, joins ineficientes, etc.
    }

    /**
     * Analizar rendimiento de caché
     */
    protected function analyzeCachePerformance($event): void
    {
        // Implementar análisis de rendimiento de caché
        // Por ejemplo, identificar patrones de acceso, sugerir optimizaciones, etc.
    }

    /**
     * Manejar carga crítica del sistema
     */
    protected function handleCriticalSystemLoad($event): void
    {
        $overloadedResources = $event->getOverloadedResources();
        
        $this->createAlert(
            'critical',
            'system_load',
            'Critical system load detected on: ' . implode(', ', $overloadedResources),
            [
                'cpu_load' => $event->cpuLoad,
                'memory_usage' => $event->memoryUsage,
                'disk_usage' => $event->diskUsage,
            ]
        );

        // Ejecutar acciones de mitigación según los recursos sobrecargados
        foreach ($overloadedResources as $resource) {
            $this->executeMitigationActions($resource);
        }
    }

    /**
     * Ejecutar acciones de mitigación de memoria
     */
    protected function executeMemoryMitigationActions(): void
    {
        // Implementar acciones de mitigación
        Cache::tags(['non-critical'])->flush();
        \Artisan::call('view:clear');
    }

    /**
     * Ejecutar acciones de mitigación según el recurso
     */
    protected function executeMitigationActions(string $resource): void
    {
        switch ($resource) {
            case 'CPU':
                \Artisan::call('queue:restart');
                break;
            case 'Memory':
                $this->executeMemoryMitigationActions();
                break;
            case 'Disk':
                \Artisan::call('log:clean');
                break;
        }
    }
}
