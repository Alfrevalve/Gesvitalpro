<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;

class Line extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Roles del personal
     */
    public const ROLE_MANAGER = 'jefe_linea';
    public const ROLE_INSTRUMENTIST = 'instrumentista';
    public const ROLE_SALES = 'vendedor';

    protected $fillable = [
        'name',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Reglas de validación
     */
    public static $rules = [
        'name' => 'required|string|max:255|unique:lines,name',
        'description' => 'nullable|string|max:1000',
    ];

    /**
     * Obtener los equipos asociados a la línea
     */
    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class);
    }

    /**
     * Obtener las cirugías asociadas a la línea
     */
    public function surgeries(): HasMany
    {
        return $this->hasMany(Surgery::class);
    }

    /**
     * Obtener el personal asignado a la línea
     */
    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'line_staff')
            ->withTimestamps()
            ->withPivot(['role']);
    }

    /**
     * Obtener el jefe de línea
     */
    public function getLineManagerAttribute()
    {
        return $this->staff()
            ->whereHas('role', function($query) {
                $query->where('slug', self::ROLE_MANAGER);
            })
            ->first();
    }

    /**
     * Obtener los instrumentistas
     */
    public function getInstrumentistsAttribute()
    {
        return $this->staff()
            ->whereHas('role', function($query) {
                $query->where('slug', self::ROLE_INSTRUMENTIST);
            })
            ->get();
    }

    /**
     * Obtener los vendedores
     */
    public function getSalesStaffAttribute()
    {
        return $this->staff()
            ->whereHas('role', function($query) {
                $query->where('slug', self::ROLE_SALES);
            })
            ->get();
    }

    /**
     * Verificar si un usuario está asignado a la línea
     */
    public function hasStaffMember(User $user): bool
    {
        return $this->staff->contains($user);
    }

    /**
     * Asignar un miembro del personal con un rol específico
     */
    public function assignStaffMember(User $user, string $role): bool
    {
        if (!$this->hasStaffMember($user)) {
            return $this->staff()->attach($user, ['role' => $role]);
        }
        return false;
    }

    /**
     * Remover un miembro del personal
     */
    public function removeStaffMember(User $user): bool
    {
        return $this->staff()->detach($user);
    }

    /**
     * Obtener el conteo de equipos disponibles
     */
    public function getAvailableEquipmentCountAttribute(): int
    {
        return $this->equipment()->where('status', 'available')->count();
    }

    /**
     * Obtener el conteo de equipos en uso
     */
    public function getInUseEquipmentCountAttribute(): int
    {
        return $this->equipment()->where('status', 'in_use')->count();
    }

    /**
     * Obtener el conteo de equipos en mantenimiento
     */
    public function getMaintenanceEquipmentCountAttribute(): int
    {
        return $this->equipment()->where('status', 'maintenance')->count();
    }

    /**
     * Obtener el conteo de cirugías pendientes
     */
    public function getPendingSurgeriesCountAttribute(): int
    {
        return $this->surgeries()->where('status', 'pending')->count();
    }

    /**
     * Obtener el conteo de cirugías completadas
     */
    public function getCompletedSurgeriesCountAttribute(): int
    {
        return $this->surgeries()->where('status', 'completed')->count();
    }

    /**
     * Scope para filtrar líneas por usuario asignado
     */
    public function scopeForUser($query, User $user)
    {
        if ($user->isAdmin() || $user->isGerente()) {
            return $query;
        }

        return $query->whereHas('staff', function($q) use ($user) {
            $q->where('users.id', $user->id);
        });
    }

    /**
     * Obtener estadísticas detalladas de la línea
     */
    public function getStats(): array
    {
        return [
            'equipment' => [
                'total' => $this->equipment()->count(),
                'available' => $this->available_equipment_count,
                'in_use' => $this->in_use_equipment_count,
                'maintenance' => $this->maintenance_equipment_count,
            ],
            'surgeries' => [
                'total' => $this->surgeries()->count(),
                'pending' => $this->pending_surgeries_count,
                'completed' => $this->completed_surgeries_count,
                'success_rate' => $this->getSurgerySuccessRate(),
            ],
            'staff' => [
                'total' => $this->staff()->count(),
                'managers' => $this->staff()->where('role', self::ROLE_MANAGER)->count(),
                'instrumentists' => $this->staff()->where('role', self::ROLE_INSTRUMENTIST)->count(),
                'sales' => $this->staff()->where('role', self::ROLE_SALES)->count(),
            ],
        ];
    }

    /**
     * Obtener tasa de éxito de cirugías
     */
    public function getSurgerySuccessRate(): float
    {
        $total = $this->surgeries()->count();
        if ($total === 0) return 0;

        $completed = $this->completed_surgeries_count;
        return round(($completed / $total) * 100, 2);
    }

    /**
     * Verificar disponibilidad de equipos para una fecha
     */
    public function hasAvailableEquipmentForDate(Carbon $date): bool
    {
        $scheduledSurgeries = $this->surgeries()
            ->whereDate('surgery_date', $date)
            ->count();

        return $this->available_equipment_count > $scheduledSurgeries;
    }

    /**
     * Obtener equipos que necesitan mantenimiento
     */
    public function getEquipmentNeedingMaintenance(): Collection
    {
        return $this->equipment()
            ->where(function($query) {
                $query->whereDate('next_maintenance', '<=', now())
                    ->orWhere('status', 'maintenance');
            })
            ->get();
    }

    /**
     * Obtener próximas cirugías
     */
    public function getUpcomingSurgeries(int $days = 7): Collection
    {
        return $this->surgeries()
            ->where('surgery_date', '>=', now())
            ->where('surgery_date', '<=', now()->addDays($days))
            ->orderBy('surgery_date')
            ->with(['medico:id,nombre', 'institucion:id,nombre'])
            ->get();
    }

    /**
     * Verificar si la línea tiene personal suficiente
     */
    public function hasRequiredStaff(): bool
    {
        return $this->line_manager !== null &&
               $this->instrumentists->count() > 0 &&
               $this->sales_staff->count() > 0;
    }

    /**
     * Obtener rendimiento mensual
     */
    public function getMonthlyPerformance(int $month = null, int $year = null): array
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        $surgeries = $this->surgeries()
            ->whereYear('surgery_date', $year)
            ->whereMonth('surgery_date', $month)
            ->get();

        return [
            'total_surgeries' => $surgeries->count(),
            'completed' => $surgeries->where('status', 'completed')->count(),
            'cancelled' => $surgeries->where('status', 'cancelled')->count(),
            'success_rate' => $this->getSurgerySuccessRate(),
            'equipment_utilization' => $this->getEquipmentUtilizationRate(),
        ];
    }

    /**
     * Obtener tasa de utilización de equipos
     */
    private function getEquipmentUtilizationRate(): float
    {
        $totalEquipment = $this->equipment()->count();
        if ($totalEquipment === 0) return 0;

        $inUse = $this->in_use_equipment_count;
        return round(($inUse / $totalEquipment) * 100, 2);
    }
}
