<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PerformanceMetric;
use Symfony\Component\HttpFoundation\Response;

class MonitorPerformance
{
    protected $startTime;
    protected $startMemory;
    protected $queryCount = 0;
    protected $queries = [];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->shouldMonitor($request)) {
            return $next($request);
        }

        // Iniciar monitoreo
        $this->startMonitoring();

        // Configurar listener de consultas SQL
        $this->setupQueryListener();

        // Procesar la petición
        $response = $next($request);

        // Registrar métricas
        $this->recordMetrics($request, $response);

        return $response;
    }

    /**
     * Determinar si se debe monitorear la petición
     */
    protected function shouldMonitor(Request $request): bool
    {
        if (!config('monitoring.performance.enabled')) {
            return false;
        }

        // No monitorear peticiones de assets
        if ($this->isAssetRequest($request)) {
            return false;
        }

        // Aplicar sample rate
        $sampleRate = config('monitoring.performance.sample_rate', 1);
        return rand(1, 100) <= ($sampleRate * 100);
    }

    /**
     * Iniciar monitoreo
     */
    protected function startMonitoring(): void
    {
        $this->startTime = microtime(true);
        $this->startMemory = memory_get_usage(true);
    }

    /**
     * Configurar listener de consultas SQL
     */
    protected function setupQueryListener(): void
    {
        if (!config('monitoring.performance.tracking.queries')) {
            return;
        }

        DB::listen(function ($query) {
            $this->queryCount++;
            
            $time = $query->time;
            $slowThreshold = config('monitoring.performance.slow_threshold.query', 100);

            if ($time >= $slowThreshold) {
                $this->queries[] = [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $time,
                    'connection' => $query->connection->getName(),
                ];
            }
        });
    }

    /**
     * Registrar métricas de rendimiento
     */
    protected function recordMetrics(Request $request, Response $response): void
    {
        $duration = $this->calculateDuration();
        $memoryUsage = $this->calculateMemoryUsage();
        
        // Registrar métrica de duración de la petición
        $this->recordRequestDuration($request, $response, $duration);

        // Registrar métrica de uso de memoria
        $this->recordMemoryUsage($memoryUsage);

        // Registrar métricas de base de datos
        $this->recordDatabaseMetrics();

        // Verificar umbrales
        $this->checkThresholds($duration, $memoryUsage);
    }

    /**
     * Registrar duración de la petición
     */
    protected function recordRequestDuration(Request $request, Response $response, float $duration): void
    {
        PerformanceMetric::record(
            'request',
            'duration',
            $duration,
            'ms',
            [
                'route' => $request->route()?->getName(),
                'method' => $request->method(),
                'status' => $response->getStatusCode(),
                'ip' => $request->ip(),
                'user_id' => $request->user()?->id,
            ]
        );
    }

    /**
     * Registrar uso de memoria
     */
    protected function recordMemoryUsage(float $memoryUsage): void
    {
        PerformanceMetric::record(
            'system',
            'memory_usage',
            $memoryUsage,
            'MB'
        );
    }

    /**
     * Registrar métricas de base de datos
     */
    protected function recordDatabaseMetrics(): void
    {
        if (!config('monitoring.performance.tracking.queries')) {
            return;
        }

        PerformanceMetric::record(
            'database',
            'query_count',
            $this->queryCount
        );

        if (!empty($this->queries)) {
            PerformanceMetric::record(
                'database',
                'slow_queries',
                count($this->queries),
                null,
                ['queries' => $this->queries]
            );
        }
    }

    /**
     * Verificar umbrales de rendimiento
     */
    protected function checkThresholds(float $duration, float $memoryUsage): void
    {
        $slowThreshold = config('monitoring.performance.slow_threshold.request', 1000);
        if ($duration >= $slowThreshold) {
            $this->reportSlowRequest($duration);
        }

        $memoryThreshold = config('monitoring.performance.memory_threshold', 128);
        if ($memoryUsage >= $memoryThreshold) {
            $this->reportHighMemoryUsage($memoryUsage);
        }
    }

    /**
     * Reportar petición lenta
     */
    protected function reportSlowRequest(float $duration): void
    {
        \Log::warning('Slow request detected', [
            'duration' => $duration,
            'route' => request()->route()?->getName(),
            'method' => request()->method(),
            'queries' => $this->queries,
        ]);
    }

    /**
     * Reportar uso alto de memoria
     */
    protected function reportHighMemoryUsage(float $memoryUsage): void
    {
        \Log::warning('High memory usage detected', [
            'memory_usage' => $memoryUsage,
            'route' => request()->route()?->getName(),
            'method' => request()->method(),
        ]);
    }

    /**
     * Calcular duración de la petición
     */
    protected function calculateDuration(): float
    {
        return round((microtime(true) - $this->startTime) * 1000, 2);
    }

    /**
     * Calcular uso de memoria
     */
    protected function calculateMemoryUsage(): float
    {
        $bytes = memory_get_usage(true) - $this->startMemory;
        return round($bytes / 1024 / 1024, 2);
    }

    /**
     * Verificar si es una petición de asset
     */
    protected function isAssetRequest(Request $request): bool
    {
        $path = $request->path();
        return preg_match('/\.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot)$/i', $path);
    }
}
