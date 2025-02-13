<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class PerformanceMetric extends Model
{
    protected $fillable = [
        'type',
        'name',
        'value',
        'unit',
        'metadata',
        'recorded_at',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'metadata' => 'array',
        'recorded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope para métricas recientes
     */
    public function scopeRecent(Builder $query, int $minutes = 60): Builder
    {
        return $query->where('recorded_at', '>=', now()->subMinutes($minutes));
    }

    /**
     * Scope para un tipo específico de métrica
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope para una métrica específica
     */
    public function scopeNamed(Builder $query, string $name): Builder
    {
        return $query->where('name', $name);
    }

    /**
     * Obtener estadísticas para una métrica específica
     */
    public static function getStats(string $type, string $name, int $minutes = 60): array
    {
        $metrics = static::ofType($type)
            ->named($name)
            ->recent($minutes)
            ->get();

        if ($metrics->isEmpty()) {
            return [
                'current' => 0,
                'average' => 0,
                'min' => 0,
                'max' => 0,
                'trend' => 'stable',
                'unit' => null,
            ];
        }

        $values = $metrics->pluck('value')->toArray();
        $latest = $metrics->last();

        return [
            'current' => $latest->value,
            'average' => round(array_sum($values) / count($values), 2),
            'min' => min($values),
            'max' => max($values),
            'trend' => static::calculateTrend($metrics),
            'unit' => $latest->unit,
        ];
    }

    /**
     * Calcular la tendencia de una serie de métricas
     */
    protected static function calculateTrend($metrics): string
    {
        if ($metrics->count() < 2) {
            return 'stable';
        }

        $values = $metrics->pluck('value')->toArray();
        $firstHalf = array_slice($values, 0, floor(count($values) / 2));
        $secondHalf = array_slice($values, floor(count($values) / 2));

        $firstAvg = array_sum($firstHalf) / count($firstHalf);
        $secondAvg = array_sum($secondHalf) / count($secondHalf);

        $difference = $secondAvg - $firstAvg;
        $threshold = max($firstAvg, $secondAvg) * 0.05; // 5% de cambio

        if (abs($difference) < $threshold) {
            return 'stable';
        }

        return $difference > 0 ? 'increasing' : 'decreasing';
    }

    /**
     * Registrar una nueva métrica
     */
    public static function record(
        string $type,
        string $name,
        float $value,
        ?string $unit = null,
        ?array $metadata = null
    ): self {
        return static::create([
            'type' => $type,
            'name' => $name,
            'value' => $value,
            'unit' => $unit,
            'metadata' => $metadata,
            'recorded_at' => now(),
        ]);
    }

    /**
     * Obtener datos para gráficos
     */
    public static function getChartData(
        string $type,
        string $name,
        int $hours = 24,
        string $interval = '1 hour'
    ): array {
        $startDate = now()->subHours($hours);
        
        $metrics = static::ofType($type)
            ->named($name)
            ->where('recorded_at', '>=', $startDate)
            ->orderBy('recorded_at')
            ->get()
            ->groupBy(function ($metric) use ($interval) {
                return $metric->recorded_at->startOfHour()->format('Y-m-d H:i:s');
            });

        $labels = [];
        $data = [];

        $current = $startDate->copy()->startOfHour();
        $end = now()->startOfHour();

        while ($current <= $end) {
            $timeKey = $current->format('Y-m-d H:i:s');
            $labels[] = $current->format('H:i');
            
            if (isset($metrics[$timeKey])) {
                $data[] = round($metrics[$timeKey]->avg('value'), 2);
            } else {
                $data[] = null;
            }

            $current->addHour();
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Limpiar métricas antiguas
     */
    public static function cleanup(int $days = 30): int
    {
        return static::where('recorded_at', '<', now()->subDays($days))->delete();
    }

    /**
     * Obtener el color recomendado para gráficos
     */
    public static function getMetricColor(string $type): string
    {
        return match($type) {
            'cpu' => '#4F46E5',
            'memory' => '#10B981',
            'disk' => '#F59E0B',
            'network' => '#6366F1',
            'database' => '#EC4899',
            'cache' => '#8B5CF6',
            'queue' => '#14B8A6',
            default => '#6B7280',
        };
    }
}
