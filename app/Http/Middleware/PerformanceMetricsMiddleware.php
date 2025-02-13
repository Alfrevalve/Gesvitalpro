<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\PerformanceMetric;
use Symfony\Component\HttpFoundation\Response;

class PerformanceMetricsMiddleware
{
    protected $startTime;
    protected $startMemory;
    protected $queryCount = 0;
    protected $queries = [];
    protected $cacheHits = 0;
    protected $cacheMisses = 0;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // No monitorear peticiones de assets
        if ($this->shouldSkip($request)) {
            return $next($request);
        }

        // Iniciar monitoreo
        $this->startMonitoring();

        // Configurar listeners
        $this->setupListeners();

        // Procesar la petición
        $response = $next($request);

        // Registrar métricas
        $this->recordMetrics($request, $response);

        return $response;
    }

    /**
     * Determinar si se debe omitir el monitoreo
     */
    protected function shouldSkip(Request $request): bool
    {
        // Omitir archivos estáticos
        if ($this->isStaticFile($request)) {
            return true;
        }

        // Aplicar sample rate
        $sampleRate = config('monitoring.performance.sample_rate', 1);
        return rand(1, 100) > ($sampleRate * 100);
    }

    /**
     * Verificar si es un archivo estático
     */
    protected function isStaticFile(Request $request): bool
    {
        return preg_match('/\.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot)$/i', $request->path());
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
     * Configurar listeners
     */
    protected function setupListeners(): void
    {
        // Monitorear consultas SQL
        DB::listen(function ($query) {
            $this->queryCount++;
            
            $time = $query->time;
            $threshold = config('monitoring.performance.slow_threshold.query', 100);

            if ($time >= $threshold) {
                $this->queries[] = [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $time,
                    'connection' => $query->connection->getName(),
                ];
            }
        });

        // Monitorear caché
        Cache::macro('recordHit', function () {
            $this->cacheHits++;
        });

        Cache::macro('recordMiss', function () {
            $this->cacheMisses++;
        });
    }

    /**
     * Registrar métricas
     */
    protected function recordMetrics(Request $request, Response $response): void
    {
        $duration = $this->calculateDuration();
        $memoryUsage = $this->calculateMemoryUsage();
        
        // Registrar métricas de la petición
        $this->recordRequestMetrics($request, $response, $duration);

        // Registrar métricas de base de datos
        $this->recordDatabaseMetrics();

        // Registrar métricas de caché
        $this->recordCacheMetrics();

        // Registrar métricas de memoria
        $this->recordMemoryMetrics($memoryUsage);

        // Verificar umbrales
        $this->checkThresholds($duration, $memoryUsage);
    }

    /**
     * Registrar métricas de la petición
     */
    protected function recordRequestMetrics(Request $request, Response $response, float $duration): void
    {
        PerformanceMetric::record(
            'request',
            'duration',
            $duration,
            'ms',
            [
                'route' => $request->route()?->getName(),
                'method' => $request->method(),
                'path' => $request->path(),
                'status' => $response->getStatusCode(),
                'user_id' => $request->user()?->id,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );
    }

    /**
     * Registrar métricas de base de datos
     */
    protected function recordDatabaseMetrics(): void
    {
        PerformanceMetric::record(
            'database',
            'queries',
            $this->queryCount,
            null,
            [
                'slow_queries' => count($this->queries),
                'slow_queries_details' => $this->queries,
            ]
        );
    }

    /**
     * Registrar métricas de caché
     */
    protected function recordCacheMetrics(): void
    {
        $total = $this->cacheHits + $this->cacheMisses;
        $hitRate = $total > 0 ? ($this->cacheHits / $total) * 100 : 0;

        PerformanceMetric::record(
            'cache',
            'hit_rate',
            $hitRate,
            '%',
            [
                'hits' => $this->cacheHits,
                'misses' => $this->cacheMisses,
                'total' => $total,
            ]
        );
    }

    /**
     * Registrar métricas de memoria
     */
    protected function recordMemoryMetrics(float $memoryUsage): void
    {
        PerformanceMetric::record(
            'memory',
            'usage',
            $memoryUsage,
            'MB'
        );
    }

    /**
     * Verificar umbrales
     */
    protected function checkThresholds(float $duration, float $memoryUsage): void
    {
        $thresholds = config('monitoring.performance.thresholds', []);

        // Verificar duración de la petición
        if (isset($thresholds['request_duration']) && $duration > $thresholds['request_duration']) {
            $this->reportSlowRequest($duration);
        }

        // Verificar uso de memoria
        if (isset($thresholds['memory_usage']) && $memoryUsage > $thresholds['memory_usage']) {
            $this->reportHighMemoryUsage($memoryUsage);
        }

        // Verificar consultas lentas
        if (isset($thresholds['slow_queries']) && count($this->queries) > $thresholds['slow_queries']) {
            $this->reportSlowQueries();
        }

        // Verificar tasa de aciertos de caché
        if (isset($thresholds['cache_hit_rate'])) {
            $total = $this->cacheHits + $this->cacheMisses;
            if ($total > 0) {
                $hitRate = ($this->cacheHits / $total) * 100;
                if ($hitRate < $thresholds['cache_hit_rate']) {
                    $this->reportLowCacheHitRate($hitRate);
                }
            }
        }
    }

    /**
     * Reportar petición lenta
     */
    protected function reportSlowRequest(float $duration): void
    {
        event(new \App\Events\SlowRequestDetected(
            $duration,
            request()->route()?->getName(),
            $this->queries
        ));
    }

    /**
     * Reportar uso alto de memoria
     */
    protected function reportHighMemoryUsage(float $memoryUsage): void
    {
        event(new \App\Events\HighMemoryUsageDetected(
            $memoryUsage,
            request()->route()?->getName()
        ));
    }

    /**
     * Reportar consultas lentas
     */
    protected function reportSlowQueries(): void
    {
        event(new \App\Events\SlowQueriesDetected(
            $this->queries,
            request()->route()?->getName()
        ));
    }

    /**
     * Reportar tasa baja de aciertos de caché
     */
    protected function reportLowCacheHitRate(float $hitRate): void
    {
        event(new \App\Events\LowCacheHitRateDetected(
            $hitRate,
            $this->cacheHits,
            $this->cacheMisses
        ));
    }

    /**
     * Calcular duración
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
}
