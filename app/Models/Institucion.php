<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;

class Institucion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'instituciones';

    /**
     * Estados de la institución
     */
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    /**
     * Tipos de establecimiento
     */
    public const TIPO_HOSPITAL = 'hospital';
    public const TIPO_CLINICA = 'clinica';
    public const TIPO_OTRO = 'otro';

    protected $fillable = [
        'nombre',
        'codigo_renipress',
        'tipo_establecimiento',
        'categoria',
        'red_salud',
        'latitud',
        'longitud',
        'datos_ubicacion',
        'telefono',
        'email',
        'direccion',
        'ciudad',
        'estado'
    ];

    protected $casts = [
        'datos_ubicacion' => 'array',
        'latitud' => 'decimal:8',
        'longitud' => 'decimal:8',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Reglas de validación
     */
    public static $rules = [
        'nombre' => 'required|string|max:255',
        'codigo_renipress' => 'required|string|max:20|unique:instituciones,codigo_renipress',
        'tipo_establecimiento' => 'required|string|in:hospital,clinica,otro',
        'categoria' => 'required|string|max:50',
        'red_salud' => 'nullable|string|max:100',
        'latitud' => 'nullable|numeric',
        'longitud' => 'nullable|numeric',
        'datos_ubicacion' => 'nullable|array',
        'telefono' => 'required|string|max:20',
        'email' => 'required|email|max:255',
        'direccion' => 'required|string|max:255',
        'ciudad' => 'required|string|max:100',
        'estado' => 'required|string|in:active,inactive',
    ];

    /**
     * Obtiene el ícono según el tipo de establecimiento
     */
    public function getTipoIconClass(): string
    {
        return match(strtolower($this->tipo_establecimiento)) {
            self::TIPO_HOSPITAL => 'bi-hospital',
            self::TIPO_CLINICA => 'bi-heart-pulse',
            default => 'bi-house-heart'
        };
    }

    /**
     * Obtiene la clase de fondo según el tipo
     */
    public function getTipoBackgroundClass(): string
    {
        return 'tipo-' . strtolower($this->tipo_establecimiento ?? self::TIPO_OTRO);
    }

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
            self::STATUS_ACTIVE => 'Activa',
            self::STATUS_INACTIVE => 'Inactiva',
            default => 'Desconocido',
        };
    }

    /**
     * Verifica si la institución está activa
     */
    public function isActive(): bool
    {
        return $this->estado === self::STATUS_ACTIVE;
    }

    /**
     * Activa la institución
     */
    public function activate(): bool
    {
        $this->estado = self::STATUS_ACTIVE;
        return $this->save();
    }

    /**
     * Desactiva la institución
     */
    public function deactivate(): bool
    {
        $this->estado = self::STATUS_INACTIVE;
        return $this->save();
    }

    /**
     * Toggle el estado de la institución
     */
    public function toggleStatus(): bool
    {
        $this->estado = $this->isActive() ? self::STATUS_INACTIVE : self::STATUS_ACTIVE;
        return $this->save();
    }

    /**
     * Relación con líneas
     */
    public function lines(): BelongsToMany
    {
        return $this->belongsToMany(Line::class, 'institucion_line', 'institucion_id', 'line_id')
                    ->withTimestamps();
    }

    /**
     * Relación con médicos
     */
    public function medicos(): HasMany
    {
        return $this->hasMany(Medico::class);
    }

    /**
     * Relación con visitas
     */
    public function visitas(): HasMany
    {
        return $this->hasMany(Visita::class);
    }

    /**
     * Relación con cirugías
     */
    public function surgeries(): HasMany
    {
        return $this->hasMany(Surgery::class);
    }

    /**
     * Relación con zonas
     */
    public function zonas(): BelongsToMany
    {
        return $this->belongsToMany(Zona::class, 'zona_institucion');
    }

    /**
     * Scope para instituciones activas
     */
    public function scopeActive($query)
    {
        return $query->where('estado', self::STATUS_ACTIVE);
    }

    /**
     * Scope para instituciones por tipo
     */
    public function scopeByTipo($query, string $tipo)
    {
        return $query->where('tipo_establecimiento', $tipo);
    }

    /**
     * Scope para instituciones por ciudad
     */
    public function scopeByCity($query, string $ciudad)
    {
        return $query->where('ciudad', $ciudad);
    }

    /**
     * Obtiene las ciudades únicas
     */
    public static function getUniqueCities(): array
    {
        return self::distinct('ciudad')->pluck('ciudad')->filter()->toArray();
    }

    /**
     * Obtiene estadísticas de la institución
     */
    public function getStats(): array
    {
        return [
            'medicos_count' => $this->medicos()->count(),
            'visitas_count' => $this->visitas()->count(),
            'surgeries_count' => $this->surgeries()->count(),
            'lines_count' => $this->lines()->count(),
        ];
    }

    /**
     * Obtiene las próximas cirugías
     */
    public function getUpcomingSurgeries(int $days = 7): Collection
    {
        return $this->surgeries()
            ->where('surgery_date', '>=', now())
            ->where('surgery_date', '<=', now()->addDays($days))
            ->orderBy('surgery_date')
            ->get();
    }
}
