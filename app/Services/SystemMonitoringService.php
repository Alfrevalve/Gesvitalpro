<?php

namespace App\Services;

use App\Models\SystemMetric;
use App\Models\SystemAlert;
use App\Models\MonitoringConfig;
use App\Models\PerformanceMetric;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SystemAlertNotification;
use Carbon\Carbon;

class SystemMonitoringService
{
    protected $configs;
    protected $lastCheck;
    protected $currentMetrics;

    public function __construct()
    {
        if (config('monitoring.enabled', false)) {
            $this->configs = MonitoringConfig::enabled()->get();
        } else {
            $this->configs = collect();
        }
        $this->lastCheck = [];
        $this->currentMetrics = [];
    }

    /**
     * Ejecutar todas las verificaciones de monitoreo
     */
    public function runChecks(): array
    {
        $results = [];
        $startTime = microtime(true);

        try {
            foreach ($this->configs as $config) {
                if ($this->shouldCheck($config)) {
                    $results[$config->metric] = $this->checkMetric($config);
                    $this->lastCheck[$config->metric] = now();
                }
            }

            $this->processResults($results);
            $this->logMetrics();

            return [
                'success' => true,
                'results' => $results,
                'duration' => round(microtime(true) - $startTime, 3),
                'timestamp' => now()->toIso8601String(),
            ];
        } catch (\Exception $e) {
            Log::error('Error running system checks', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'duration' => round(microtime(true) - $startTime, 3),
                'timestamp' => now()->toIso8601String(),
            ];
        }
    }

    /**
     * Verificar si una métrica debe ser comprobada
     */
    protected function shouldCheck(MonitoringConfig $config): bool
    {
        if (!isset($this->lastCheck[$config->metric])) {
            return true;
        }

        $lastCheck = $this->lastCheck[$config->metric];
        return $lastCheck->addSeconds($config->check_interval) <= now();
    }

    /**
     * Verificar una métrica específica
     */
    protected function checkMetric(MonitoringConfig $config): array
    {
        $value = $this->collectMetric($config->metric);
        $this->currentMetrics[$config->metric] = $value;

        $status = $config->checkThresholds($value);
        
        if ($status) {
            $this->handleAlert($config, $value, $status);
        }

        return [
            'value' => $value,
            'status' => $status ?? 'normal',
            'formatted_value' => $config->formatValue($value),
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Recolectar el valor de una métrica
     */
    protected function collectMetric(string $metric): float
    {
        switch ($metric) {
            case 'cpu_usage':
                return $this->getCpuUsage();
            case 'memory_usage':
                return $this->getMemoryUsage();
            case 'disk_usage':
                return $this->getDiskUsage();
            case 'mysql_connections':
                return $this->getMySQLConnections();
            default:
                throw new \InvalidArgumentException("Unsupported metric: {$metric}");
        }
    }

    /**
     * Manejar una alerta del sistema
     */
    protected function handleAlert(MonitoringConfig $config, float $value, string $status): void
    {
        $alert = SystemAlert::createAlert(
            $status,
            $config->metric,
            $status === 'critical' ? $config->critical_threshold : $config->warning_threshold,
            $value,
            $this->generateAlertMessage($config, $value, $status)
        );

        if ($config->requiresImmediateNotification()) {
            $this->sendAlertNotifications($alert, $config);
        }
    }

    /**
     * Generar mensaje de alerta
     */
    protected function generateAlertMessage(MonitoringConfig $config, float $value, string $status): string
    {
        $threshold = $status === 'critical' ? $config->critical_threshold : $config->warning_threshold;
        
        return sprintf(
            '%s ha alcanzado un nivel %s. Valor actual: %s (Umbral: %s)',
            $config->getDisplayName(),
            $status,
            $config->formatValue($value),
            $config->formatValue($threshold)
        );
    }

    /**
     * Enviar notificaciones de alerta
     */
    protected function sendAlertNotifications(SystemAlert $alert, MonitoringConfig $config): void
    {
        $channels = $config->getEnabledChannels();

        foreach ($channels as $channel) {
            try {
                $this->sendNotification($alert, $channel);
            } catch (\Exception $e) {
                Log::error("Error sending alert notification", [
                    'channel' => $channel,
                    'alert_id' => $alert->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Enviar una notificación por un canal específico
     */
    protected function sendNotification(SystemAlert $alert, string $channel): void
    {
        switch ($channel) {
            case 'email':
                $this->sendEmailNotification($alert);
                break;
            case 'slack':
                $this->sendSlackNotification($alert);
                break;
            case 'database':
                // Las alertas ya se guardan en la base de datos
                break;
        }
    }

    /**
     * Procesar y almacenar los resultados
     */
    protected function processResults(array $results): void
    {
        // Guardar métricas del sistema
        SystemMetric::create([
            'metrics' => $this->currentMetrics,
            'created_at' => now(),
        ]);

        // Guardar métricas de rendimiento individuales
        foreach ($this->currentMetrics as $metric => $value) {
            PerformanceMetric::record(
                $this->getMetricType($metric),
                $metric,
                $value,
                $this->getMetricUnit($metric)
            );
        }
    }

    /**
     * Obtener el tipo de una métrica
     */
    protected function getMetricType(string $metric): string
    {
        return match(true) {
            str_contains($metric, 'cpu') => 'cpu',
            str_contains($metric, 'memory') => 'memory',
            str_contains($metric, 'disk') => 'disk',
            str_contains($metric, 'mysql') => 'database',
            default => 'system',
        };
    }

    /**
     * Obtener la unidad de una métrica
     */
    protected function getMetricUnit(string $metric): string
    {
        return match(true) {
            str_contains($metric, 'usage') => '%',
            str_contains($metric, 'memory') => 'MB',
            str_contains($metric, 'disk') => 'GB',
            str_contains($metric, 'connections') => 'conn',
            default => '',
        };
    }

    /**
     * Registrar las métricas en el log
     */
    protected function logMetrics(): void
    {
        Log::channel('system')->info('System metrics collected', [
            'metrics' => $this->currentMetrics,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    // Métodos auxiliares para recolectar métricas específicas...
    // (Implementar según el sistema operativo y necesidades)
}
