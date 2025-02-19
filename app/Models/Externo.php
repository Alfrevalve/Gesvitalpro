<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Externo extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Estados del personal externo
     */
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_BLOCKED = 'blocked';

    protected $fillable = [
        'nombre',
        'email',
        'telefono',
        'especialidad',
        'institucion_id',
        'notas',
        'estado',
        'documento_identidad',
        'fecha_validacion',
        'validado_por',
        'metadata',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'fecha_validacion' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Reglas de validación
     */
    public static $rules = [
        'nombre' => 'required|string|max:255',
        'email' => 'required|email|unique:externos,email',
        'telefono' => 'required|string|max:20',
        'especialidad' => 'required|string|max:100',
        'institucion_id' => 'required|exists:instituciones,id',
        'notas' => 'nullable|string',
        'estado' => 'required|in:active,inactive,blocked',
        'documento_identidad' => 'required|string|max:20|unique:externos,documento_identidad',
        'fecha_validacion' => 'nullable|date',
        'validado_por' => 'nullable|exists:users,id',
        'metadata' => 'nullable|array',
    ];

    /**
     * Relaciones
     */
    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class);
    }

    public function visitas(): BelongsToMany
    {
        return $this->belongsToMany(Visita::class, 'visita_externo')
            ->withTimestamps();
    }

    public function surgeries(): BelongsToMany
    {
        return $this->belongsToMany(Surgery::class, 'surgery_externo')
            ->withTimestamps();
    }

    public function validadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validado_por');
    }

    /**
     * Métodos de estado
     */
    public function isActive(): bool
    {
        return $this->estado === self::STATUS_ACTIVE;
    }

    public function isInactive(): bool
    {
        return $this->estado === self::STATUS_INACTIVE;
    }

    public function isBlocked(): bool
    {
        return $this->estado === self::STATUS_BLOCKED;
    }

    public function activate(User $validador = null): bool
    {
        return $this->update([
            'estado' => self::STATUS_ACTIVE,
            'fecha_validacion' => now(),
            'validado_por' => $validador ? $validador->id : null,
        ]);
    }

    public function deactivate(string $motivo = null): bool
    {
        return $this->update([
            'estado' => self::STATUS_INACTIVE,
            'notas' => $motivo ? "Desactivado: {$motivo}" : $this->notas,
        ]);
    }

    public function block(string $motivo): bool
    {
        return $this->update([
            'estado' => self::STATUS_BLOCKED,
            'notas' => "Bloqueado: {$motivo}",
        ]);
    }

    /**
     * Métodos de validación
     */
    public function isValidated(): bool
    {
        return $this->fecha_validacion !== null;
    }

    public function needsRevalidation(): bool
    {
        if (!$this->fecha_validacion) {
            return true;
        }

        // Requiere revalidación después de 1 año
        return $this->fecha_validacion->addYear()->isPast();
    }

    /**
     * Métodos de disponibilidad
     */
    public function isAvailableOn(Carbon $fecha): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        return !$this->surgeries()
            ->whereDate('surgery_date', $fecha)
            ->exists();
    }

    public function getConflictingEvents(Carbon $fecha): array
    {
        return [
            'surgeries' => $this->surgeries()
                ->whereDate('surgery_date', $fecha)
                ->get(),
            'visitas' => $this->visitas()
                ->whereDate('fecha_hora', $fecha)
                ->get(),
        ];
    }

    /**
     * Scopes
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', self::STATUS_ACTIVE);
    }

    public function scopeInactivos($query)
    {
        return $query->where('estado', self::STATUS_INACTIVE);
    }

    public function scopeBloqueados($query)
    {
        return $query->where('estado', self::STATUS_BLOCKED);
    }

    public function scopeInstitucion($query, $institucionId)
    {
        return $query->where('institucion_id', $institucionId);
    }

    public function scopeEspecialidad($query, $especialidad)
    {
        return $query->where('especialidad', $especialidad);
    }

    public function scopeBuscar($query, $termino)
    {
        return $query->where('nombre', 'like', "%{$termino}%")
            ->orWhere('especialidad', 'like', "%{$termino}%")
            ->orWhere('email', 'like', "%{$termino}%")
            ->orWhere('documento_identidad', 'like', "%{$termino}%");
    }

    public function scopeNecesitanRevalidacion($query)
    {
        return $query->where(function($q) {
            $q->whereNull('fecha_validacion')
              ->orWhere('fecha_validacion', '<=', now()->subYear());
        });
    }

    /**
     * Métodos de estadísticas
     */
    public function getStats(Carbon $inicio = null, Carbon $fin = null): array
    {
        $query = function($relation) use ($inicio, $fin) {
            $q = $relation();
            if ($inicio && $fin) {
                if ($relation === $this->surgeries()) {
                    $q->whereBetween('surgery_date', [$inicio, $fin]);
                } else {
                    $q->whereBetween('fecha_hora', [$inicio, $fin]);
                }
            }
            return $q;
        };

        return [
            'participaciones' => [
                'cirugias' => [
                    'total' => $query($this->surgeries)->count(),
                    'completadas' => $query($this->surgeries)->where('status', 'completed')->count(),
                    'pendientes' => $query($this->surgeries)->where('status', 'pending')->count(),
                ],
                'visitas' => [
                    'total' => $query($this->visitas)->count(),
                    'realizadas' => $query($this->visitas)->where('estado', 'realizada')->count(),
                    'programadas' => $query($this->visitas)->where('estado', 'programada')->count(),
                ],
            ],
            'instituciones' => [
                'total' => $this->surgeries()
                    ->distinct('institucion_id')
                    ->count('institucion_id'),
            ],
            'validacion' => [
                'validado' => $this->isValidated(),
                'necesita_revalidacion' => $this->needsRevalidation(),
                'ultima_validacion' => $this->fecha_validacion?->format('Y-m-d'),
            ],
        ];
    }

    /**
     * Obtener próximas actividades
     */
    public function getProximasActividades(int $dias = 7): array
    {
        $fin = now()->addDays($dias);

        return [
            'cirugias' => $this->surgeries()
                ->where('surgery_date', '>=', now())
                ->where('surgery_date', '<=', $fin)
                ->orderBy('surgery_date')
                ->get(),
            'visitas' => $this->visitas()
                ->where('fecha_hora', '>=', now())
                ->where('fecha_hora', '<=', $fin)
                ->orderBy('fecha_hora')
                ->get(),
        ];
    }

    /**
     * Obtener especialidades únicas
     */
    public static function getEspecialidadesUnicas(): array
    {
        return self::distinct('especialidad')
            ->pluck('especialidad')
            ->filter()
            ->toArray();
    }
}
