<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Equipment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'description',
        'status',
        'line_id',
        'serial_number',
        'last_maintenance',
        'next_maintenance'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'last_maintenance' => 'datetime',
        'next_maintenance' => 'datetime',
    ];

    /**
     * Estados posibles del equipo
     */
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_IN_USE = 'in_use';
    public const STATUS_MAINTENANCE = 'maintenance';

    /**
     * Reglas de validación
     */
    public static $rules = [
        'name' => 'required|string|max:255',
        'type' => 'required|string|max:255',
        'description' => 'nullable|string',
        'status' => 'required|in:available,in_use,maintenance',
        'line_id' => 'required|exists:lines,id',
        'serial_number' => 'required|string|max:255|unique:equipment,serial_number',
        'last_maintenance' => 'nullable|date',
        'next_maintenance' => 'nullable|date|after:last_maintenance',
    ];

    /**
     * Relación con la línea
     */
    public function line(): BelongsTo
    {
        return $this->belongsTo(Line::class);
    }

    /**
     * Relación con cirugías
     */
    public function surgeries(): BelongsToMany
    {
        return $this->belongsToMany(Surgery::class, 'surgery_equipment')
            ->withTimestamps();
    }

    /**
     * Verifica si el equipo está disponible
     */
    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE;
    }

    /**
     * Verifica si el equipo está en uso
     */
    public function isInUse(): bool
    {
        return $this->status === self::STATUS_IN_USE;
    }

    /**
     * Verifica si el equipo está en mantenimiento
     */
    public function isUnderMaintenance(): bool
    {
        return $this->status === self::STATUS_MAINTENANCE;
    }

    /**
     * Obtiene el color del badge según el estado
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            self::STATUS_AVAILABLE => 'success',
            self::STATUS_IN_USE => 'primary',
            self::STATUS_MAINTENANCE => 'warning',
            default => 'secondary',
        };
    }

    /**
     * Obtiene el texto del estado
     */
    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            self::STATUS_AVAILABLE => 'Disponible',
            self::STATUS_IN_USE => 'En Uso',
            self::STATUS_MAINTENANCE => 'En Mantenimiento',
            default => 'Desconocido',
        };
    }

    /**
     * Programa el mantenimiento del equipo
     */
    public function scheduleMaintenance(Carbon $date): bool
    {
        if ($this->isInUse()) {
            return false;
        }

        $this->status = self::STATUS_MAINTENANCE;
        $this->next_maintenance = $date;
        return $this->save();
    }

    /**
     * Completa el mantenimiento del equipo
     */
    public function completeMaintenance(): bool
    {
        if (!$this->isUnderMaintenance()) {
            return false;
        }

        $this->status = self::STATUS_AVAILABLE;
        $this->last_maintenance = now();
        $this->next_maintenance = now()->addDays(90); // Programa el próximo mantenimiento en 90 días
        return $this->save();
    }

    /**
     * Marca el equipo como en uso
     */
    public function markAsInUse(): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }

        $this->status = self::STATUS_IN_USE;
        return $this->save();
    }

    /**
     * Marca el equipo como disponible
     */
    public function markAsAvailable(): bool
    {
        if (!$this->isInUse()) {
            return false;
        }

        $this->status = self::STATUS_AVAILABLE;
        return $this->save();
    }

    /**
     * Verifica si el equipo necesita mantenimiento
     */
    public function needsMaintenance(): bool
    {
        if (!$this->next_maintenance) {
            return false;
        }

        return $this->next_maintenance->isPast() || $this->next_maintenance->isToday();
    }

    /**
     * Scope para equipos que necesitan mantenimiento
     */
    public function scopeNeedingMaintenance($query)
    {
        return $query->where(function($q) {
            $q->whereDate('next_maintenance', '<=', now())
              ->where('status', '!=', self::STATUS_MAINTENANCE);
        });
    }

    /**
     * Scope para equipos por tipo
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Obtiene los tipos de equipo únicos
     */
    public static function getUniqueTypes(): array
    {
        return self::distinct()->pluck('type')->filter()->toArray();
    }
}
