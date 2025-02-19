<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use App\Models\Surgery;
use App\Models\Equipment;
use App\Models\User;
use Exception;

class CacheOptimizationService
{
    protected $cacheConfig;
    protected $redisConnection;

    public function __construct()
    {
        $this->cacheConfig = config('cache.strategies');
        $this->redisConnection = Redis::connection('cache');
    }

    /**
     * Precalentar el caché del sistema
     */
    public function warmupSystemCache(): void
    {
        try {
            foreach (config('cache.warm_cache_on_boot') as $cacheKey) {
                $this->warmupSpecificCache($cacheKey);
            }
            Log::info('Cache del sistema precalentado exitosamente');
        } catch (Exception $e) {
            Log::error('Error al precalentar el cache: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Precalentar un caché específico
     */
    protected function warmupSpecificCache(string $cacheKey): void
    {
        switch ($cacheKey) {
            case 'dashboard_stats':
                $this->warmupDashboardCache();
                break;
            case 'equipment_status':
                $this->warmupEquipmentCache();
                break;
            case 'surgery_data':
                $this->warmupSurgeryCache();
                break;
        }
    }

    /**
     * Precalentar caché del dashboard
     */
    public function warmupDashboardCache(): void
    {
        $stats = [
            'surgeries' => [
                'total' => Surgery::count(),
                'pending' => Surgery::where('status', 'pending')->count(),
                'completed' => Surgery::where('status', 'completed')->count(),
            ],
            'equipment' => [
                'total' => Equipment::count(),
                'available' => Equipment::where('status', 'available')->count(),
                'maintenance' => Equipment::where('status', 'maintenance')->count(),
            ],
            'users' => [
                'total' => User::count(),
                'active' => User::where('status', 'active')->count(),
            ],
        ];

        $this->setCacheWithStrategy('dashboard_stats', $stats);
    }

    /**
     * Precalentar caché de equipamiento
     */
    protected function warmupEquipmentCache(): void
    {
        $equipment = Equipment::with(['maintenanceRecords', 'currentLocation'])
            ->get()
            ->keyBy('id');

        $this->setCacheWithStrategy('equipment_status', $equipment);
    }

    /**
     * Precalentar caché de cirugías
     */
    protected function warmupSurgeryCache(): void
    {
        $surgeries = Surgery::with([
            'patient',
            'doctor',
            'equipment',
            'institution'
        ])->get()->keyBy('id');

        $this->setCacheWithStrategy('surgery_data', $surgeries);
    }

    /**
     * Establecer caché usando estrategia configurada
     */
    protected function setCacheWithStrategy(string $key, $data): void
    {
        if (!isset($this->cacheConfig[$key])) {
            throw new Exception("Estrategia de caché no encontrada para: {$key}");
        }

        $strategy = $this->cacheConfig[$key];

        Cache::tags($strategy['tags'])->put(
            $key,
            $data,
            $strategy['ttl']
        );
    }

    /**
     * Limpiar caché selectivamente
     */
    public function clearSelectiveCache(array $tags): void
    {
        try {
            Cache::tags($tags)->flush();
            Log::info('Caché limpiado selectivamente para tags: ' . implode(', ', $tags));
        } catch (Exception $e) {
            Log::error('Error al limpiar caché selectivo: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Invalidar caché basado en eventos
     */
    public function invalidateCacheOnEvent(string $event): void
    {
        $invalidationConfig = config('cache.invalidation_events');

        if (isset($invalidationConfig[$event])) {
            foreach ($invalidationConfig[$event] as $cacheKey) {
                if (isset($this->cacheConfig[$cacheKey])) {
                    $this->clearSelectiveCache($this->cacheConfig[$cacheKey]['tags']);
                }
            }
        }
    }

    /**
     * Optimizar consultas frecuentes
     */
    public function optimizeFrequentQueries(): void
    {
        // Implementar lógica de optimización de consultas frecuentes
        // Por ejemplo, precalcular y cachear resultados comunes
    }

    /**
     * Manejar race conditions
     */
    public function handleRaceCondition(string $key, callable $callback, int $timeout = 5): mixed
    {
        $lock = Cache::lock($key, $timeout);

        try {
            $lock->block($timeout);
            return $callback();
        } finally {
            optional($lock)->release();
        }
    }

    /**
     * Monitorear rendimiento del caché
     */
    public function monitorCachePerformance(): array
    {
        return [
            'hit_rate' => $this->calculateHitRate(),
            'memory_usage' => $this->getMemoryUsage(),
            'keys_count' => $this->getKeysCount(),
        ];
    }

    /**
     * Calcular tasa de aciertos del caché
     */
    protected function calculateHitRate(): float
    {
        $hits = $this->redisConnection->get('cache_hits') ?? 0;
        $misses = $this->redisConnection->get('cache_misses') ?? 0;

        if (($hits + $misses) === 0) {
            return 0.0;
        }

        return ($hits / ($hits + $misses)) * 100;
    }

    /**
     * Obtener uso de memoria
     */
    protected function getMemoryUsage(): array
    {
        $info = $this->redisConnection->info('memory');

        return [
            'used_memory' => $info['used_memory_human'] ?? '0B',
            'peak_memory' => $info['used_memory_peak_human'] ?? '0B',
        ];
    }

    /**
     * Obtener conteo de claves
     */
    protected function getKeysCount(): int
    {
        return $this->redisConnection->dbsize() ?? 0;
    }
}
