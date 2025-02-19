<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SurgicalProcessLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'surgical_process_id',
        'user_id',
        'old_state',
        'new_state',
        'data',
        'duration_minutes',
    ];

    protected $casts = [
        'data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Reglas de validación
     */
    public static $rules = [
        'surgical_process_id' => 'required|exists:surgical_processes,id',
        'user_id' => 'required|exists:users,id',
        'old_state' => 'required|string',
        'new_state' => 'required|string',
        'data' => 'nullable|array',
        'duration_minutes' => 'nullable|integer|min:0',
    ];

    /**
     * Relaciones
     */
    public function surgicalProcess(): BelongsTo
    {
        return $this->belongsTo(SurgicalProcess::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scopes
     */
    public function scopeByState($query, string $state)
    {
        return $query->where('new_state', $state);
    }

    public function scopeByUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeWithDuration($query)
    {
        return $query->whereNotNull('duration_minutes');
    }

    /**
     * Métodos de utilidad
     */
    public function getFormattedDuration(): ?string
    {
        if (!$this->duration_minutes) {
            return null;
        }

        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;

        return $hours > 0
            ? "{$hours}h {$minutes}m"
            : "{$minutes}m";
    }

    public function getStateChangeDescription(): string
    {
        $states = [
            SurgicalProcess::STATUS_VISIT_PENDING => 'Visita Pendiente',
            SurgicalProcess::STATUS_VISIT_COMPLETED => 'Visita Completada',
            SurgicalProcess::STATUS_SURGERY_PLANNED => 'Cirugía Planificada',
            SurgicalProcess::STATUS_MATERIAL_PREPARATION => 'Preparación de Material',
            SurgicalProcess::STATUS_MATERIAL_READY => 'Material Listo',
            SurgicalProcess::STATUS_DISPATCHED => 'Material Despachado',
            SurgicalProcess::STATUS_SURGERY_IN_PROGRESS => 'Cirugía en Progreso',
            SurgicalProcess::STATUS_SURGERY_COMPLETED => 'Cirugía Completada',
            SurgicalProcess::STATUS_CONSUMPTION_REGISTERED => 'Consumo Registrado',
            SurgicalProcess::STATUS_PICKUP_SCHEDULED => 'Recojo Programado',
            SurgicalProcess::STATUS_MATERIAL_RETURNED => 'Material Devuelto',
            SurgicalProcess::STATUS_COMPLETED => 'Proceso Completado',
            SurgicalProcess::STATUS_CANCELLED => 'Proceso Cancelado',
        ];

        $oldState = $states[$this->old_state] ?? $this->old_state;
        $newState = $states[$this->new_state] ?? $this->new_state;

        return "Cambio de estado: {$oldState} → {$newState}";
    }

    /**
     * Obtener datos adicionales formateados
     */
    public function getFormattedData(): array
    {
        $formatted = [];

        if (isset($this->data['note'])) {
            $formatted['Nota'] = $this->data['note'];
        }

        if (isset($this->data['cancellation_reason'])) {
            $formatted['Motivo de Cancelación'] = $this->data['cancellation_reason'];
        }

        if (isset($this->data['equipment'])) {
            $formatted['Equipos'] = collect($this->data['equipment'])
                ->map(fn($eq) => $eq['name'])
                ->join(', ');
        }

        if (isset($this->data['materials'])) {
            $formatted['Materiales'] = collect($this->data['materials'])
                ->map(fn($mat) => "{$mat['name']} ({$mat['quantity']})")
                ->join(', ');
        }

        return $formatted;
    }

    /**
     * Obtener estadísticas de tiempos por estado
     */
    public static function getStateTimingStats(): array
    {
        return static::withDuration()
            ->selectRaw('new_state,
                        AVG(duration_minutes) as avg_duration,
                        MIN(duration_minutes) as min_duration,
                        MAX(duration_minutes) as max_duration,
                        COUNT(*) as total_occurrences')
            ->groupBy('new_state')
            ->get()
            ->mapWithKeys(function ($stat) {
                return [$stat->new_state => [
                    'promedio' => round($stat->avg_duration),
                    'minimo' => $stat->min_duration,
                    'maximo' => $stat->max_duration,
                    'ocurrencias' => $stat->total_occurrences,
                ]];
            })
            ->toArray();
    }

    /**
     * Obtener estadísticas de eficiencia por usuario
     */
    public static function getUserEfficiencyStats(): array
    {
        return static::withDuration()
            ->selectRaw('user_id,
                        AVG(duration_minutes) as avg_duration,
                        COUNT(*) as total_changes,
                        COUNT(DISTINCT surgical_process_id) as total_processes')
            ->groupBy('user_id')
            ->with('user:id,name')
            ->get()
            ->mapWithKeys(function ($stat) {
                return [$stat->user->name => [
                    'tiempo_promedio' => round($stat->avg_duration),
                    'cambios_totales' => $stat->total_changes,
                    'procesos_totales' => $stat->total_processes,
                    'cambios_por_proceso' => round($stat->total_changes / $stat->total_processes, 2),
                ]];
            })
            ->toArray();
    }
}
