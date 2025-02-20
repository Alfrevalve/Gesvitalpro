<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use App\Services\PerformanceMonitor;
use Carbon\Carbon;

class OptimizedCacheService
{
    protected $performanceMonitor;
    protected $prefix = 'gesbio_';
    protected $defaultTtl;

    public function __construct(PerformanceMonitor $performanceMonitor)
    {
        $this->performanceMonitor = $performanceMonitor;
        $this->defaultTtl = config('cache.ttl.surgery_cache', 3600);
    }

    /**
     * Obtener datos con caché optimizado y monitoreo
     */
    public function remember(string $key, $ttl, callable $callback, array $tags = [])
    {
        $startTime = microtime(true);
        $cacheKey = $this->prefix . $key;

        try {
            // Intentar obtener del caché
            if (Cache::tags($tags)->has($cacheKey)) {
                $value = Cache::tags($tags)->get($cacheKey);
                $this->recordMetrics($key, true, microtime(true) - $startTime);
                return $value;
            }

            // Si no está en caché, ejecutar callback
            $value = $callback();
            Cache::tags($tags)->put($cacheKey, $value, $ttl);

            $this->recordMetrics($key, false, microtime(true) - $startTime);
            return $value;
        } catch (\Exception $e) {
            // Log error y retornar resultado sin caché
            \Log::error("Error en caché: {$e->getMessage()}", [
                'key' => $key,
                'tags' => $tags,
                'trace' => $e->getTraceAsString()
            ]);

            return $callback();
        }
    }

    /**
     * Registrar métricas de rendimiento del caché
     */
    protected function recordMetrics(string $key, bool $hit, float $duration)
    {
        $this->performanceMonitor->recordMetric("cache_access.{$key}.duration", $duration);
        $this->performanceMonitor->recordMetric("cache_access.{$key}.hit", $hit ? 1 : 0);

        // Actualizar métricas globales
        $this->performanceMonitor->recordCacheMetrics($hit);
    }

    /**
     * Limpiar caché por tags
     */
    public function clearByTags(array $tags)
    {
        try {
            Cache::tags($tags)->flush();
            \Log::info("Caché limpiado para tags: " . implode(', ', $tags));
        } catch (\Exception $e) {
            \Log::error("Error al limpiar caché: {$e->getMessage()}", [
                'tags' => $tags
            ]);
        }
    }

    /**
     * Obtener estadísticas del caché
     */
    public function getCacheStats(): array
    {
        return [
            'hit_ratio' => $this->performanceMonitor->getMetric('cache_hit_ratio'),
            'average_duration' => $this->performanceMonitor->getMetric('cache_average_duration'),
            'memory_usage' => Redis::info()['used_memory_human'] ?? 'N/A',
            'total_keys' => Redis::dbsize(),
        ];
    }

    /**
     * Caché específico para cirugías con tags
     */
    public function cacheSurgeryData(string $key, $data, array $additionalTags = [])
    {
        $tags = array_merge(['surgeries'], $additionalTags);
        return $this->remember(
            "surgery.{$key}",
            config('cache.ttl.surgery_cache'),
            fn() => $data,
            $tags
        );
    }

    /**
     * Caché específico para equipamiento con tags
     */
    public function cacheEquipmentData(string $key, $data, array $additionalTags = [])
    {
        $tags = array_merge(['equipment'], $additionalTags);
        return $this->remember(
            "equipment.{$key}",
            config('cache.ttl.equipment_cache'),
            fn() => $data,
            $tags
        );
    }

    /**
     * Caché para dashboard con TTL corto
     */
    public function cacheDashboardData(string $key, $data)
    {
        return $this->remember(
            "dashboard.{$key}",
            config('cache.ttl.dashboard_cache'),
            fn() => $data,
            ['dashboard']
        );
    }

    /**
     * Precarga de datos frecuentemente accedidos
     */
    public function preloadFrequentData()
    {
        try {
            // Lista de datos a precargar
            $preloadTasks = [
                'upcoming_surgeries' => fn() => \App\Models\Surgery::upcoming()->get(),
                'available_equipment' => fn() => \App\Models\Equipment::available()->get(),
                'active_doctors' => fn() => \App\Models\Medico::active()->get(),
            ];

            foreach ($preloadTasks as $key => $task) {
                if (!Cache::tags(['preload'])->has($this->prefix . $key)) {
                    Cache::tags(['preload'])->put(
                        $this->prefix . $key,
                        $task(),
                        Carbon::now()->addHour()
                    );
                }
            }

            \Log::info('Precarga de caché completada exitosamente');
        } catch (\Exception $e) {
            \Log::error("Error en precarga de caché: {$e->getMessage()}");
        }
    }

    /**
     * Limpieza programada de caché
     */
    public function runScheduledCleanup()
    {
        try {
            // Limpiar datos antiguos
            $oldKeys = Redis::keys($this->prefix . 'temp:*');
            if (!empty($oldKeys)) {
                Redis::del($oldKeys);
            }

            // Refrescar datos precargados
            $this->preloadFrequentData();

            \Log::info('Limpieza programada de caché completada');
        } catch (\Exception $e) {
            \Log::error("Error en limpieza de caché: {$e->getMessage()}");
        }
    }
}
