<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class MonitoringConfig extends Model
{
    protected $fillable = [
        'metric',
        'enabled',
        'warning_threshold',
        'critical_threshold',
        'check_interval',
        'notification_channels',
        'additional_config',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'warning_threshold' => 'integer',
        'critical_threshold' => 'integer',
        'check_interval' => 'integer',
        'notification_channels' => 'array',
        'additional_config' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope para configuraciones activas
     */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('enabled', true);
    }

    /**
     * Scope para métricas específicas
     */
    public function scopeForMetric(Builder $query, string $metric): Builder
    {
        return $query->where('metric', $metric);
    }

    /**
     * Verificar si una métrica está habilitada
     */
    public static function isMetricEnabled(string $metric): bool
    {
        return static::forMetric($metric)->enabled()->exists();
    }

    /**
     * Obtener la configuración de una métrica
     */
    public static function getMetricConfig(string $metric): ?self
    {
        return static::forMetric($metric)->first();
    }

    /**
     * Verificar si un valor excede los umbrales
     */
    public function checkThresholds($value): ?string
    {
        if ($this->critical_threshold !== null && $value >= $this->critical_threshold) {
            return 'critical';
        }

        if ($this->warning_threshold !== null && $value >= $this->warning_threshold) {
            return 'warning';
        }

        return null;
    }

    /**
     * Obtener los canales de notificación habilitados
     */
    public function getEnabledChannels(): array
    {
        return array_filter($this->notification_channels ?? []);
    }

    /**
     * Verificar si un canal de notificación está habilitado
     */
    public function isChannelEnabled(string $channel): bool
    {
        return in_array($channel, $this->getEnabledChannels());
    }

    /**
     * Obtener el intervalo de verificación en segundos
     */
    public function getCheckInterval(): int
    {
        return max($this->check_interval, 60); // Mínimo 1 minuto
    }

    /**
     * Obtener una configuración adicional específica
     */
    public function getConfig(string $key, $default = null)
    {
        return $this->additional_config[$key] ?? $default;
    }

    /**
     * Establecer una configuración adicional
     */
    public function setConfig(string $key, $value): self
    {
        $config = $this->additional_config ?? [];
        $config[$key] = $value;
        $this->additional_config = $config;
        return $this;
    }

    /**
     * Obtener el tipo de medición
     */
    public function getMeasureType(): string
    {
        return $this->getConfig('measure_type', 'value');
    }

    /**
     * Obtener el tipo de agregación
     */
    public function getAggregationType(): string
    {
        return $this->getConfig('aggregation', 'average');
    }

    /**
     * Obtener el período de muestreo en segundos
     */
    public function getSamplePeriod(): int
    {
        return $this->getConfig('sample_period', 300); // 5 minutos por defecto
    }

    /**
     * Verificar si la métrica requiere notificación inmediata
     */
    public function requiresImmediateNotification(): bool
    {
        return $this->getConfig('immediate_notification', false);
    }

    /**
     * Obtener la descripción amigable de la métrica
     */
    public function getDisplayName(): string
    {
        return $this->getConfig('display_name', ucfirst(str_replace('_', ' ', $this->metric)));
    }

    /**
     * Obtener la unidad de medida
     */
    public function getUnit(): string
    {
        return $this->getConfig('unit', '');
    }

    /**
     * Formatear un valor según la configuración
     */
    public function formatValue($value): string
    {
        $unit = $this->getUnit();
        
        if ($this->getMeasureType() === 'percentage') {
            return number_format($value, 2) . '%';
        }

        if ($this->getMeasureType() === 'bytes') {
            return $this->formatBytes($value);
        }

        return $value . ($unit ? " $unit" : '');
    }

    /**
     * Formatear bytes a unidades legibles
     */
    protected function formatBytes($bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
