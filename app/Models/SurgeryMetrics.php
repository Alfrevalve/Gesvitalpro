<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;

class SurgeryMetrics extends Model
{
    use HasFactory;

    /**
     * Obtener cirugías realizadas por línea
     */
    public static function getSurgeriesByLine(): array
    {
        return Surgery::select('line_id', \DB::raw('count(*) as total'))
            ->groupBy('line_id')
            ->with('line:id,name')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->line->name => $item->total];
            })
            ->toArray();
    }

    /**
     * Obtener cirugías realizadas por instrumentista
     */
    public static function getSurgeriesByInstrumentist(): array
    {
        return Surgery::select('medico_id', \DB::raw('count(*) as total'))
            ->groupBy('medico_id')
            ->with('medico:id,nombre')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->medico->nombre => $item->total];
            })
            ->toArray();
    }

    /**
     * Obtener cirugías mensuales
     */
    public static function getMonthlySurgeries(int $year = null): array
    {
        $year = $year ?? now()->year;

        return Surgery::selectRaw('MONTH(surgery_date) as month, COUNT(*) as total')
            ->whereYear('surgery_date', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month')
            ->toArray();
    }

    /**
     * Obtener cirugías reprogramadas
     */
    public static function getRescheduledSurgeries(): array
    {
        return Surgery::where('status', 'reprogramada')
            ->count();
    }

    /**
     * Obtener cirugías realizadas
     */
    public static function getCompletedSurgeries(): array
    {
        return Surgery::where('status', 'completed')
            ->count();
    }

    /**
     * Obtener cirugías suspendidas
     */
    public static function getCancelledSurgeries(): array
    {
        return Surgery::where('status', 'cancelled')
            ->count();
    }

    /**
     * Obtener frecuencia de visitas
     */
    public static function getVisitFrequency(): array
    {
        return Visita::selectRaw('medico_id, COUNT(*) as total')
            ->groupBy('medico_id')
            ->with('medico:id,nombre')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->medico->nombre => $item->total];
            })
            ->toArray();
    }

    /**
     * Obtener cobertura de instituciones asignadas
     */
    public static function getInstitutionCoverage(): array
    {
        return Institucion::withCount('visitas')
            ->get()
            ->map(function ($institucion) {
                return [
                    'nombre' => $institucion->nombre,
                    'visitas' => $institucion->visitas_count,
                    'cobertura' => $institucion->visitas_count > 0 ? 'Activa' : 'Inactiva',
                ];
            })
            ->toArray();
    }
}
