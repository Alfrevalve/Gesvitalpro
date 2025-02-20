<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SurgeryDashboard extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'preferences',
        'last_viewed_at',
    ];

    protected $casts = [
        'preferences' => 'array',
        'last_viewed_at' => 'datetime',
    ];

    /**
     * Obtener métricas para el dashboard
     */
    public function getMetrics(): array
    {
        return [
            'surgeries_by_line' => SurgeryMetrics::getSurgeriesByLine(),
            'surgeries_by_instrumentist' => SurgeryMetrics::getSurgeriesByInstrumentist(),
            'monthly_surgeries' => SurgeryMetrics::getMonthlySurgeries(),
            'rescheduled_surgeries' => SurgeryMetrics::getRescheduledSurgeries(),
            'completed_surgeries' => SurgeryMetrics::getCompletedSurgeries(),
            'cancelled_surgeries' => SurgeryMetrics::getCancelledSurgeries(),
            'visit_frequency' => SurgeryMetrics::getVisitFrequency(),
            'institution_coverage' => SurgeryMetrics::getInstitutionCoverage(),
        ];
    }

    /**
     * Generar reporte
     */
    public function generateReport(string $type, string $format = 'pdf'): string
    {
        $metrics = $this->getMetrics();

        // Lógica para generar el reporte basado en el tipo y formato
        // ...

        return "Reporte generado en formato {$format} para el tipo {$type}.";
    }
}
