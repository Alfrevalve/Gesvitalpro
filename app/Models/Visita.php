<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class Visita extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Estados de la visita
     */
    public const ESTADO_PROGRAMADA = 'programada';
    public const ESTADO_REALIZADA = 'realizada';
    public const ESTADO_CANCELADA = 'cancelada';

    protected $fillable = [
        'fecha_hora',
        'institucion_id',
        'medico_id',
        'user_id',
        'motivo',
        'observaciones',
        'estado',
        'duracion_minutos',
        'resultado',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Reglas de validación
     */
    public static $rules = [
        'fecha_hora' => 'required|date|after:now',
        'institucion_id' => 'required|exists:instituciones,id',
        'medico_id' => 'required|exists:medicos,id',
        'user_id' => 'required|exists:users,id',
        'motivo' => 'required|string|max:255',
        'observaciones' => 'nullable|string',
        'estado' => 'required|in:programada,realizada,cancelada',
        'duracion_minutos' => 'nullable|integer|min:1',
        'resultado' => 'nullable|string',
    ];

    /**
     * Relaciones
     */
    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class);
    }

    public function medico(): BelongsTo
    {
        return $this->belongsTo(Medico::class);
    }

    public function asesor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Obtiene el color del badge según el estado
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->estado) {
            self::ESTADO_PROGRAMADA => 'warning',
            self::ESTADO_REALIZADA => 'success',
            self::ESTADO_CANCELADA => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Obtiene el texto del estado
     */
    public function getStatusTextAttribute(): string
    {
        return match($this->estado) {
            self::ESTADO_PROGRAMADA => 'Programada',
            self::ESTADO_REALIZADA => 'Realizada',
            self::ESTADO_CANCELADA => 'Cancelada',
            default => 'Desconocido',
        };
    }

    /**
     * Scopes
     */
    public function scopeRealizadas(Builder $query): Builder
    {
        return $query->where('estado', self::ESTADO_REALIZADA);
    }

    public function scopeProgramadas(Builder $query): Builder
    {
        return $query->where('estado', self::ESTADO_PROGRAMADA);
    }

    public function scopeCanceladas(Builder $query): Builder
    {
        return $query->where('estado', self::ESTADO_CANCELADA);
    }

    public function scopeEntreFechas(Builder $query, $fechaInicio, $fechaFin): Builder
    {
        return $query->whereBetween('fecha_hora', [
            Carbon::parse($fechaInicio)->startOfDay(),
            Carbon::parse($fechaFin)->endOfDay()
        ]);
    }

    public function scopeProximas(Builder $query, int $dias = 7): Builder
    {
        return $query->where('fecha_hora', '>=', now())
                    ->where('fecha_hora', '<=', now()->addDays($dias))
                    ->orderBy('fecha_hora');
    }

    /**
     * Métodos de estado
     */
    public function marcarComoRealizada(array $detalles = []): bool
    {
        if (!$this->estaProgramada()) {
            return false;
        }

        return $this->update([
            'estado' => self::ESTADO_REALIZADA,
            'duracion_minutos' => $detalles['duracion_minutos'] ?? null,
            'resultado' => $detalles['resultado'] ?? null,
            'observaciones' => $detalles['observaciones'] ?? $this->observaciones,
        ]);
    }

    public function marcarComoCancelada(string $motivo = null): bool
    {
        if (!$this->estaProgramada()) {
            return false;
        }

        return $this->update([
            'estado' => self::ESTADO_CANCELADA,
            'observaciones' => $motivo ?? $this->observaciones,
        ]);
    }

    public function reprogramar(Carbon $nuevaFecha, string $motivo = null): bool
    {
        if ($this->estaRealizada()) {
            return false;
        }

        if ($this->existeConflicto($nuevaFecha)) {
            return false;
        }

        return $this->update([
            'fecha_hora' => $nuevaFecha,
            'estado' => self::ESTADO_PROGRAMADA,
            'observaciones' => $motivo ? "Reprogramada: $motivo" : $this->observaciones,
        ]);
    }

    /**
     * Verificadores de estado
     */
    public function estaProgramada(): bool
    {
        return $this->estado === self::ESTADO_PROGRAMADA;
    }

    public function estaRealizada(): bool
    {
        return $this->estado === self::ESTADO_REALIZADA;
    }

    public function estaCancelada(): bool
    {
        return $this->estado === self::ESTADO_CANCELADA;
    }

    public function esModificable(): bool
    {
        return $this->estaProgramada();
    }

    /**
     * Métodos de utilidad
     */
    public function existeConflicto(Carbon $fecha): bool
    {
        return static::where('medico_id', $this->medico_id)
            ->where('id', '!=', $this->id)
            ->where('estado', self::ESTADO_PROGRAMADA)
            ->whereDate('fecha_hora', $fecha->toDateString())
            ->exists();
    }

    public function getDuracionFormateada(): string
    {
        if (!$this->duracion_minutos) {
            return 'No registrada';
        }

        $horas = floor($this->duracion_minutos / 60);
        $minutos = $this->duracion_minutos % 60;

        return $horas > 0
            ? "{$horas}h {$minutos}m"
            : "{$minutos}m";
    }

    /**
     * Métodos de estadísticas
     */
    public static function getEstadisticasPorPeriodo(Carbon $inicio, Carbon $fin): array
    {
        $visitas = static::entreFechas($inicio, $fin)->get();

        return [
            'total' => $visitas->count(),
            'realizadas' => $visitas->where('estado', self::ESTADO_REALIZADA)->count(),
            'canceladas' => $visitas->where('estado', self::ESTADO_CANCELADA)->count(),
            'efectividad' => static::calcularEfectividad($visitas),
            'duracion_promedio' => static::calcularDuracionPromedio($visitas),
        ];
    }

    public static function getEstadisticasPorMedico(Medico $medico, Carbon $inicio, Carbon $fin): array
    {
        $visitas = static::where('medico_id', $medico->id)
            ->entreFechas($inicio, $fin)
            ->get();

        return [
            'total' => $visitas->count(),
            'realizadas' => $visitas->where('estado', self::ESTADO_REALIZADA)->count(),
            'canceladas' => $visitas->where('estado', self::ESTADO_CANCELADA)->count(),
            'efectividad' => static::calcularEfectividad($visitas),
            'duracion_promedio' => static::calcularDuracionPromedio($visitas),
        ];
    }

    public static function getEstadisticasPorInstitucion(Institucion $institucion, Carbon $inicio, Carbon $fin): array
    {
        $visitas = static::where('institucion_id', $institucion->id)
            ->entreFechas($inicio, $fin)
            ->get();

        return [
            'total' => $visitas->count(),
            'realizadas' => $visitas->where('estado', self::ESTADO_REALIZADA)->count(),
            'canceladas' => $visitas->where('estado', self::ESTADO_CANCELADA)->count(),
            'efectividad' => static::calcularEfectividad($visitas),
            'duracion_promedio' => static::calcularDuracionPromedio($visitas),
        ];
    }

    private static function calcularEfectividad(Collection $visitas): float
    {
        $total = $visitas->count();
        if ($total === 0) return 0;

        $realizadas = $visitas->where('estado', self::ESTADO_REALIZADA)->count();
        return round(($realizadas / $total) * 100, 2);
    }

    private static function calcularDuracionPromedio(Collection $visitas): ?float
    {
        $visitasConDuracion = $visitas->whereNotNull('duracion_minutos');
        if ($visitasConDuracion->isEmpty()) return null;

        return round($visitasConDuracion->avg('duracion_minutos'), 2);
    }

    /**
     * Obtener próximas visitas
     */
    public static function getProximasVisitas(int $dias = 7): Collection
    {
        return static::proximas($dias)
            ->with(['medico:id,nombre', 'institucion:id,nombre', 'asesor:id,name'])
            ->get();
    }

    /**
     * Verificar disponibilidad de horario
     */
    public static function verificarDisponibilidad(Carbon $fecha, int $medico_id): bool
    {
        return !static::where('medico_id', $medico_id)
            ->where('estado', self::ESTADO_PROGRAMADA)
            ->whereDate('fecha_hora', $fecha->toDateString())
            ->exists();
    }
}
