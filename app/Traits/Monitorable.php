<?php

namespace App\Traits;

use App\Models\PerformanceMetric;
use App\Models\SystemAlert;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

trait Monitorable
{
    /**
     * Boot the trait
     */
    protected static function bootMonitorable()
    {
        if (!config('monitoring.enabled')) {
            return;
        }

        static::created(function ($model) {
            $model->recordMetric('created');
        });

        static::updated(function ($model) {
            $model->recordMetric('updated');
        });

        static::deleted(function ($model) {
            $model->recordMetric('deleted');
        });
    }

    /**
     * Registrar una métrica de rendimiento
     */
    public function recordMetric(string $action, array $metadata = []): void
    {
        if (!$this->shouldRecordMetric()) {
            return;
        }

        $metric = [
            'type' => 'model_action',
            'name' => static::class,
            'value' => 1,
            'metadata' => array_merge([
                'action' => $action,
                'model_id' => $this->getKey(),
                'user_id' => auth()->id(),
            ], $metadata),
        ];

        $this->storeMetric($metric);
        $this->checkThresholds($metric);
    }

    /**
     * Registrar una métrica personalizada
     */
    public function recordCustomMetric(string $name, $value, array $metadata = []): void
    {
        if (!$this->shouldRecordMetric()) {
            return;
        }

        $metric = [
            'type' => 'custom',
            'name' => $name,
            'value' => $value,
            'metadata' => array_merge([
                'model' => static::class,
                'model_id' => $this->getKey(),
                'user_id' => auth()->id(),
            ], $metadata),
        ];

        $this->storeMetric($metric);
        $this->checkThresholds($metric);
    }

    /**
     * Almacenar una métrica
     */
    protected function storeMetric(array $metric): void
    {
        PerformanceMetric::create([
            'type' => $metric['type'],
            'name' => $metric['name'],
            'value' => $metric['value'],
            'metadata' => $metric['metadata'],
            'recorded_at' => now(),
        ]);

        $this->incrementMetricCounter($metric);
    }

    /**
     * Incrementar el contador de métricas en caché
     */
    protected function incrementMetricCounter(array $metric): void
    {
        $key = $this->getMetricCacheKey($metric);
        $period = now()->format('Y-m-d-H');

        Cache::increment("metrics:{$key}:{$period}");
    }

    /**
     * Verificar umbrales de métricas
     */
    protected function checkThresholds(array $metric): void
    {
        $threshold = $this->getMetricThreshold($metric);
        if (!$threshold) {
            return;
        }

        $currentValue = $this->getCurrentMetricValue($metric);
        
        if ($currentValue >= $threshold['critical']) {
            $this->createAlert('critical', $metric, $currentValue, $threshold['critical']);
        } elseif ($currentValue >= $threshold['warning']) {
            $this->createAlert('warning', $metric, $currentValue, $threshold['warning']);
        }
    }

    /**
     * Crear una alerta del sistema
     */
    protected function createAlert(string $type, array $metric, $currentValue, $threshold): void
    {
        SystemAlert::createAlert(
            $type,
            $this->getMetricName($metric),
            $threshold,
            $currentValue,
            $this->generateAlertMessage($metric, $currentValue, $threshold)
        );
    }

    /**
     * Generar mensaje de alerta
     */
    protected function generateAlertMessage(array $metric, $currentValue, $threshold): string
    {
        return sprintf(
            'La métrica %s ha alcanzado un valor de %s (umbral: %s)',
            $this->getMetricName($metric),
            $currentValue,
            $threshold
        );
    }

    /**
     * Obtener el nombre de la métrica
     */
    protected function getMetricName(array $metric): string
    {
        return sprintf(
            '%s:%s',
            $metric['type'],
            $metric['name']
        );
    }

    /**
     * Obtener la clave de caché para la métrica
     */
    protected function getMetricCacheKey(array $metric): string
    {
        return sprintf(
            '%s:%s',
            str_replace('\\', '_', static::class),
            $metric['type']
        );
    }

    /**
     * Obtener el valor actual de la métrica
     */
    protected function getCurrentMetricValue(array $metric): int
    {
        $key = $this->getMetricCacheKey($metric);
        $period = now()->format('Y-m-d-H');

        return (int) Cache::get("metrics:{$key}:{$period}", 0);
    }

    /**
     * Obtener los umbrales para una métrica
     */
    protected function getMetricThreshold(array $metric): ?array
    {
        $config = config("monitoring.metrics.{$metric['type']}");

        if (!$config || !isset($config['warning_threshold'], $config['critical_threshold'])) {
            return null;
        }

        return [
            'warning' => $config['warning_threshold'],
            'critical' => $config['critical_threshold'],
        ];
    }

    /**
     * Determinar si se debe registrar la métrica
     */
    protected function shouldRecordMetric(): bool
    {
        if (!config('monitoring.enabled')) {
            return false;
        }

        // Aplicar sample rate para reducir la cantidad de métricas
        $sampleRate = config('monitoring.performance.sample_rate', 1);
        return rand(1, 100) <= ($sampleRate * 100);
    }

    /**
     * Obtener estadísticas de la métrica
     */
    public static function getMetricStats(string $type = 'model_action', int $hours = 24): array
    {
        $metrics = PerformanceMetric::where('type', $type)
            ->where('name', static::class)
            ->where('recorded_at', '>=', now()->subHours($hours))
            ->get();

        return [
            'total' => $metrics->count(),
            'by_action' => $metrics->groupBy('metadata.action')
                                 ->map(fn($group) => $group->count()),
            'by_hour' => $metrics->groupBy(fn($metric) => $metric->recorded_at->format('Y-m-d H:00'))
                                ->map(fn($group) => $group->count()),
            'average_per_hour' => $metrics->count() / $hours,
        ];
    }
}
