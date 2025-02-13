<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class SystemMetric extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'metrics',
        'created_at',
    ];

    protected $casts = [
        'metrics' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Scope para obtener métricas dentro de un rango de tiempo
     */
    public function scopeInTimeRange(Builder $query, string $start, string $end): Builder
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }

    /**
     * Scope para obtener las métricas más recientes
     */
    public function scopeRecent(Builder $query, int $minutes = 60): Builder
    {
        return $query->where('created_at', '>=', now()->subMinutes($minutes));
    }

    /**
     * Obtener el valor de una métrica específica
     */
    public function getMetricValue(string $key, string $subKey = null)
    {
        $metrics = $this->metrics;

        if ($subKey) {
            return $metrics[$key][$subKey] ?? null;
        }

        return $metrics[$key] ?? null;
    }

    /**
     * Calcular el promedio de una métrica en un período
     */
    public static function calculateAverage(string $metricKey, string $subKey = null, int $minutes = 60)
    {
        $metrics = static::recent($minutes)->get();
        $values = [];

        foreach ($metrics as $metric) {
            $value = $metric->getMetricValue($metricKey, $subKey);
            if (is_numeric($value)) {
                $values[] = $value;
            }
        }

        return empty($values) ? 0 : array_sum($values) / count($values);
    }

    /**
     * Obtener el valor máximo de una métrica en un período
     */
    public static function getMaxValue(string $metricKey, string $subKey = null, int $minutes = 60)
    {
        $metrics = static::recent($minutes)->get();
        $values = [];

        foreach ($metrics as $metric) {
            $value = $metric->getMetricValue($metricKey, $subKey);
            if (is_numeric($value)) {
                $values[] = $value;
            }
        }

        return empty($values) ? 0 : max($values);
    }

    /**
     * Obtener el valor mínimo de una métrica en un período
     */
    public static function getMinValue(string $metricKey, string $subKey = null, int $minutes = 60)
    {
        $metrics = static::recent($minutes)->get();
        $values = [];

        foreach ($metrics as $metric) {
            $value = $metric->getMetricValue($metricKey, $subKey);
            if (is_numeric($value)) {
                $values[] = $value;
            }
        }

        return empty($values) ? 0 : min($values);
    }

    /**
     * Obtener estadísticas de una métrica
     */
    public static function getMetricStats(string $metricKey, string $subKey = null, int $minutes = 60): array
    {
        return [
            'current' => static::latest('created_at')->first()?->getMetricValue($metricKey, $subKey),
            'average' => static::calculateAverage($metricKey, $subKey, $minutes),
            'max' => static::getMaxValue($metricKey, $subKey, $minutes),
            'min' => static::getMinValue($metricKey, $subKey, $minutes),
            'period_minutes' => $minutes,
        ];
    }

    /**
     * Limpiar métricas antiguas
     */
    public static function cleanup(int $days = 30): int
    {
        return static::where('created_at', '<', now()->subDays($days))->delete();
    }
}
