<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\Surgery;
use App\Models\Equipment;
use App\Models\Visita;
use App\Models\SurgeryRequest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class DashboardOptimizer
{
    /**
     * Tiempo de caché para estadísticas (5 minutos por defecto)
     */
    protected int $cacheDuration;

    public function __construct()
    {
        $this->cacheDuration = env('CACHE_DURATION', 300);
    }

    /**
     * Obtener estadísticas optimizadas de cirugías
     */
    public function getSurgeriesStats(): array
    {
        $cacheKey = 'dashboard_surgeries_stats_' . auth()->id();
        return Cache::remember($cacheKey, $this->cacheDuration, function () {
            $today = Carbon::today();
            $thisMonth = Carbon::now()->startOfMonth();

            return [
                'today' => $this->getTodaySurgeriesStats($today),
                'month' => $this->getMonthlySurgeriesStats($thisMonth),
                'upcoming' => $this->getUpcomingSurgeriesStats(),
            ];
        });
    }

    /**
     * Obtener estadísticas optimizadas de equipos
     */
    public function getEquipmentStats(): array
    {
        return Cache::remember('dashboard_equipment_stats', $this->cacheDuration, function () {
            return Equipment::selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "available" THEN 1 ELSE 0 END) as available,
                SUM(CASE WHEN status = "maintenance" THEN 1 ELSE 0 END) as in_maintenance,
                SUM(CASE WHEN next_maintenance_date <= ? THEN 1 ELSE 0 END) as maintenance_due
            ', [now()->addDays(30)])
            ->first()
            ->toArray();
        });
    }

    /**
     * Obtener estadísticas optimizadas de visitas
     */
    public function getVisitStats(): array
    {
        return Cache::remember('dashboard_visit_stats', $this->cacheDuration, function () {
            $today = Carbon::today();
            $thisWeek = Carbon::now()->startOfWeek();

            return [
                'today' => $this->getTodayVisitStats($today),
                'week' => $this->getWeeklyVisitStats($thisWeek),
                'upcoming' => $this->getUpcomingVisitStats(),
            ];
        });
    }

    /**
     * Obtener estadísticas optimizadas de logística
     */
    public function getLogisticsStats(): array
    {
        return Cache::remember('dashboard_logistics_stats', $this->cacheDuration, function () {
            return SurgeryRequest::selectRaw('
                SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = "pending" AND priority = "high" THEN 1 ELSE 0 END) as urgent,
                SUM(CASE WHEN status = "in_progress" THEN 1 ELSE 0 END) as processing,
                SUM(CASE WHEN status = "completed" AND DATE(updated_at) = ? THEN 1 ELSE 0 END) as completed_today
            ', [Carbon::today()])
            ->first()
            ->toArray();
        });
    }

    /**
     * Obtener próximas visitas optimizadas
     */
    public function getUpcomingVisits(): Builder
    {
        return Visita::query()
            ->select([
                'visitas.id',
                'visitas.fecha',
                'visitas.estado',
                'visitas.tipo',
                'visitas.notas',
                'medicos.nombre as medico_nombre',
                'instituciones.nombre as institucion_nombre',
                'users.name as responsable_nombre'
            ])
            ->join('medicos', 'visitas.medico_id', '=', 'medicos.id')
            ->join('instituciones', 'visitas.institucion_id', '=', 'instituciones.id')
            ->join('users', 'visitas.user_id', '=', 'users.id')
            ->where('visitas.fecha', '>=', now())
            ->where('visitas.fecha', '<=', now()->addDays(7))
            ->where('visitas.estado', 'pendiente')
            ->orderBy('visitas.fecha');
    }

    /**
     * Estadísticas de cirugías de hoy
     */
    protected function getTodaySurgeriesStats(Carbon $today): array
    {
        return Surgery::selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed
        ')
        ->whereDate('surgery_date', $today)
        ->first()
        ->toArray();
    }

    /**
     * Estadísticas de cirugías del mes
     */
    protected function getMonthlySurgeriesStats(Carbon $thisMonth): array
    {
        return Surgery::selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed
        ')
        ->whereDate('surgery_date', '>=', $thisMonth)
        ->first()
        ->toArray();
    }

    /**
     * Estadísticas de próximas cirugías
     */
    protected function getUpcomingSurgeriesStats(): array
    {
        return Surgery::selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN priority = "high" THEN 1 ELSE 0 END) as high_priority
        ')
        ->where('surgery_date', '>', now())
        ->where('surgery_date', '<=', now()->addDays(7))
        ->where('status', 'pending')
        ->first()
        ->toArray();
    }

    /**
     * Estadísticas de visitas de hoy
     */
    protected function getTodayVisitStats(Carbon $today): array
    {
        return Visita::selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN estado = "realizada" THEN 1 ELSE 0 END) as completed
        ')
        ->whereDate('fecha', $today)
        ->first()
        ->toArray();
    }

    /**
     * Estadísticas de visitas de la semana
     */
    protected function getWeeklyVisitStats(Carbon $thisWeek): array
    {
        return Visita::selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN estado = "realizada" THEN 1 ELSE 0 END) as completed
        ')
        ->whereBetween('fecha', [$thisWeek, now()])
        ->first()
        ->toArray();
    }

    /**
     * Estadísticas de próximas visitas
     */
    protected function getUpcomingVisitStats(): array
    {
        return Visita::selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN tipo = "primera" THEN 1 ELSE 0 END) as first_visits,
            SUM(CASE WHEN tipo = "seguimiento" THEN 1 ELSE 0 END) as follow_ups
        ')
        ->where('fecha', '>', now())
        ->where('fecha', '<=', now()->addDays(7))
        ->where('estado', 'pendiente')
        ->first()
        ->toArray();
    }

    /**
     * Limpiar caché del dashboard
     */
    public function clearDashboardCache(): void
    {
        $userId = auth()->id();
        Cache::forget('dashboard_surgeries_stats_' . $userId);
        Cache::forget('dashboard_equipment_stats_' . $userId);
        Cache::forget('dashboard_visit_stats_' . $userId);
        Cache::forget('dashboard_logistics_stats_' . $userId);

        // Limpiar caché global también
        Cache::forget('dashboard_surgeries_stats');
        Cache::forget('dashboard_equipment_stats');
        Cache::forget('dashboard_visit_stats');
        Cache::forget('dashboard_logistics_stats');
    }

    /**
     * Verificar si el caché está activo y funcionando
     */
    public function isCacheHealthy(): bool
    {
        try {
            Cache::put('health_check', true, 1);
            return Cache::get('health_check') === true;
        } catch (\Exception $e) {
            return false;
        } finally {
            Cache::forget('health_check');
        }
    }

    /**
     * Precalcular estadísticas del dashboard
     */
    public function warmupDashboardCache(): void
    {
        $this->getSurgeriesStats();
        $this->getEquipmentStats();
        $this->getVisitStats();
        $this->getLogisticsStats();
    }

    /**
     * Programar actualización de caché
     */
    public function scheduleCacheWarming(): void
    {
        // Este método se puede llamar desde un comando programado
        $this->clearDashboardCache();
        $this->warmupDashboardCache();
    }
}
