<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Line extends Model
{
    use HasFactory, SoftDeletes;

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
                $query->where('slug', 'jefe_linea');
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
                $query->where('slug', 'instrumentista');
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
                $query->where('slug', 'vendedor');
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
}
