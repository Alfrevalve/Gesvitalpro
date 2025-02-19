<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Medico extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Estados del médico
     */
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Validation rules for the model.
     *
     * @var array
     */
    public static $rules = [
        'nombre' => 'required|string|max:255',
        'especialidad' => 'required|string|max:100',
        'email' => 'required|email|unique:medicos,email',
        'telefono' => 'required|string|max:20',
        'estado' => 'required|in:active,inactive',
        'institucion_id' => 'required|exists:instituciones,id',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nombre',
        'especialidad',
        'email',
        'telefono',
        'estado',
        'institucion_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'deleted_at',
    ];

    /**
     * Obtiene el color del badge según el estado
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->estado) {
            self::STATUS_ACTIVE => 'success',
            self::STATUS_INACTIVE => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Obtiene el texto del estado
     */
    public function getStatusTextAttribute(): string
    {
        return match($this->estado) {
            self::STATUS_ACTIVE => 'Activo',
            self::STATUS_INACTIVE => 'Inactivo',
            default => 'Desconocido',
        };
    }

    /**
     * Verifica si el médico está activo
     */
    public function isActive(): bool
    {
        return $this->estado === self::STATUS_ACTIVE;
    }

    /**
     * Activa el médico
     */
    public function activate(): bool
    {
        $this->estado = self::STATUS_ACTIVE;
        return $this->save();
    }

    /**
     * Desactiva el médico
     */
    public function deactivate(): bool
    {
        $this->estado = self::STATUS_INACTIVE;
        return $this->save();
    }

    /**
     * Toggle el estado del médico
     */
    public function toggleStatus(): bool
    {
        $this->estado = $this->isActive() ? self::STATUS_INACTIVE : self::STATUS_ACTIVE;
        return $this->save();
    }

    /**
     * Get all visits associated with the doctor.
     */
    public function visitas(): HasMany
    {
        return $this->hasMany(Visita::class);
    }

    /**
     * Get all surgeries associated with the doctor.
     */
    public function surgeries(): HasMany
    {
        return $this->hasMany(Surgery::class, 'medico_id');
    }

    /**
     * Get the institution associated with the doctor.
     */
    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class);
    }

    /**
     * Scope a query to only include active doctors.
     */
    public function scopeActive($query)
    {
        return $query->where('estado', self::STATUS_ACTIVE);
    }

    /**
     * Scope a query to only include doctors with pending surgeries.
     */
    public function scopeWithPendingSurgeries($query)
    {
        return $query->whereHas('surgeries', function($q) {
            $q->where('status', 'pending');
        });
    }

    /**
     * Scope para médicos por especialidad
     */
    public function scopeByEspecialidad($query, string $especialidad)
    {
        return $query->where('especialidad', $especialidad);
    }

    /**
     * Get the total number of surgeries performed by the doctor.
     */
    public function getTotalSurgeries(): int
    {
        return $this->surgeries()->count();
    }

    /**
     * Get the number of surgeries performed this month.
     */
    public function getSurgeriesThisMonth(): int
    {
        return $this->surgeries()
            ->whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->count();
    }

    /**
     * Get surgeries grouped by institution with eager loading optimization.
     */
    public function getInstitutionSurgeries()
    {
        return $this->surgeries()
            ->select('institucion_id', \DB::raw('count(*) as total'))
            ->groupBy('institucion_id')
            ->with(['institucion' => function($query) {
                $query->select('id', 'nombre');
            }])
            ->get();
    }

    /**
     * Get the success rate of surgeries.
     */
    public function getSurgerySuccessRate(): float
    {
        $total = $this->surgeries()->count();
        if ($total === 0) return 0;

        $successful = $this->surgeries()
            ->where('status', 'completed')
            ->count();

        return round(($successful / $total) * 100, 2);
    }

    /**
     * Get upcoming surgeries for the doctor.
     */
    public function getUpcomingSurgeries(int $days = 7)
    {
        return $this->surgeries()
            ->where('fecha', '>=', Carbon::now())
            ->where('fecha', '<=', Carbon::now()->addDays($days))
            ->orderBy('fecha')
            ->with(['institucion:id,nombre', 'equipment:id,name'])
            ->get();
    }

    /**
     * Check if the doctor is available on a specific date.
     */
    public function isAvailableOn(Carbon $date): bool
    {
        return !$this->surgeries()
            ->whereDate('fecha', $date)
            ->exists();
    }

    /**
     * Obtiene las especialidades únicas
     */
    public static function getUniqueSpecialties(): array
    {
        return self::distinct('especialidad')
            ->pluck('especialidad')
            ->filter()
            ->toArray();
    }

    /**
     * Obtiene estadísticas del médico
     */
    public function getStats(): array
    {
        return [
            'total_surgeries' => $this->getTotalSurgeries(),
            'surgeries_this_month' => $this->getSurgeriesThisMonth(),
            'success_rate' => $this->getSurgerySuccessRate(),
            'visitas_count' => $this->visitas()->count(),
            'pending_surgeries' => $this->surgeries()->where('status', 'pending')->count(),
        ];
    }

    /**
     * Verifica si el médico tiene cirugías programadas
     */
    public function hasScheduledSurgeries(): bool
    {
        return $this->surgeries()
            ->where('fecha', '>=', now())
            ->exists();
    }
}
