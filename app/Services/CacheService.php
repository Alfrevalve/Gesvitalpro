<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\Surgery;
use App\Models\Equipment;
use App\Models\Institucion;
use App\Models\Medico;
use Carbon\Carbon;

class CacheService
{
    /**
     * Tiempo de caché por defecto en segundos (1 hora)
     */
    const DEFAULT_CACHE_TIME = 3600;

    /**
     * Obtener cirugías próximas con caché
     */
    public function getUpcomingSurgeries(int $days = 7)
    {
        $cacheKey = "upcoming_surgeries_{$days}";

        return Cache::remember($cacheKey, self::DEFAULT_CACHE_TIME, function () use ($days) {
            return Surgery::with(['line', 'institucion', 'medico', 'equipment'])
                ->upcoming($days)
                ->get();
        });
    }

    /**
     * Obtener equipamiento disponible con caché
     */
    public function getAvailableEquipment()
    {
        return Cache::remember('available_equipment', self::DEFAULT_CACHE_TIME, function () {
            return Equipment::with('line')
                ->where('status', 'available')
                ->get();
        });
    }

    /**
     * Obtener estadísticas de cirugías con caché
     */
    public function getSurgeryStats()
    {
        return Cache::remember('surgery_stats', self::DEFAULT_CACHE_TIME, function () {
            $now = Carbon::now();
            $monthStart = $now->startOfMonth();
            $monthEnd = $now->copy()->endOfMonth();

            return [
                'total' => Surgery::count(),
                'pending' => Surgery::where('status', 'pending')->count(),
                'completed' => Surgery::where('status', 'completed')->count(),
                'this_month' => Surgery::whereBetween('surgery_date', [$monthStart, $monthEnd])->count(),
            ];
        });
    }

    /**
     * Obtener médicos por especialidad con caché
     */
    public function getMedicosBySpecialty(string $specialty)
    {
        $cacheKey = "medicos_specialty_{$specialty}";

        return Cache::remember($cacheKey, self::DEFAULT_CACHE_TIME, function () use ($specialty) {
            return Medico::with('institucion')
                ->where('especialidad', $specialty)
                ->where('estado', 'activo')
                ->get();
        });
    }

    /**
     * Obtener instituciones por tipo con caché
     */
    public function getInstitucionesByType(string $type)
    {
        $cacheKey = "instituciones_type_{$type}";

        return Cache::remember($cacheKey, self::DEFAULT_CACHE_TIME, function () use ($type) {
            return Institucion::where('tipo_establecimiento', $type)
                ->with(['zonas', 'medicos'])
                ->get();
        });
    }

    /**
     * Obtener equipamiento que requiere mantenimiento con caché
     */
    public function getEquipmentNeedingMaintenance()
    {
        return Cache::remember('equipment_maintenance', self::DEFAULT_CACHE_TIME, function () {
            return Equipment::with('line')
                ->where('next_maintenance_date', '<=', Carbon::now()->addDays(7))
                ->get();
        });
    }

    /**
     * Limpiar caché específica
     */
    public function clearCache(string $key)
    {
        Cache::forget($key);
    }

    /**
     * Limpiar todo el caché relacionado con cirugías
     */
    public function clearSurgeryCache()
    {
        Cache::tags(['surgeries'])->flush();
    }

    /**
     * Limpiar todo el caché relacionado con equipamiento
     */
    public function clearEquipmentCache()
    {
        Cache::tags(['equipment'])->flush();
    }

    /**
     * Refrescar todo el caché del sistema
     */
    public function refreshAllCache()
    {
        $this->getUpcomingSurgeries();
        $this->getAvailableEquipment();
        $this->getSurgeryStats();
        $this->getEquipmentNeedingMaintenance();
    }
}
