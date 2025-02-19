<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Visita extends Model
{
    protected $fillable = [
        'fecha_hora',
        'institucion_id',
        'medico_id',
        'user_id',
        'motivo',
        'observaciones',
        'estado',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
    ];

    /**
     * Estados posibles de una visita
     */
    const ESTADO_PROGRAMADA = 'programada';
    const ESTADO_REALIZADA = 'realizada';
    const ESTADO_CANCELADA = 'cancelada';

    /**
     * Relación con institución
     */
    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class);
    }

    /**
     * Relación con médico
     */
    public function medico(): BelongsTo
    {
        return $this->belongsTo(Medico::class);
    }

    /**
     * Relación con asesor (user)
     */
    public function asesor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope para visitas realizadas
     */
    public function scopeRealizadas(Builder $query): Builder
    {
        return $query->where('estado', self::ESTADO_REALIZADA);
    }

    /**
     * Scope para visitas programadas
     */
    public function scopeProgramadas(Builder $query): Builder
    {
        return $query->where('estado', self::ESTADO_PROGRAMADA);
    }

    /**
     * Scope para visitas canceladas
     */
    public function scopeCanceladas(Builder $query): Builder
    {
        return $query->where('estado', self::ESTADO_CANCELADA);
    }

    /**
     * Scope para filtrar visitas entre fechas
     */
    public function scopeEntreFechas(Builder $query, $fechaInicio, $fechaFin): Builder
    {
        return $query->whereBetween('fecha_hora', [
            Carbon::parse($fechaInicio)->startOfDay(),
            Carbon::parse($fechaFin)->endOfDay()
        ]);
    }

    /**
     * Marcar visita como realizada
     */
    public function marcarComoRealizada(): bool
    {
        if ($this->estado !== self::ESTADO_PROGRAMADA) {
            return false;
        }

        return $this->update(['estado' => self::ESTADO_REALIZADA]);
    }

    /**
     * Marcar visita como cancelada
     */
    public function marcarComoCancelada(): bool
    {
        if ($this->estado !== self::ESTADO_PROGRAMADA) {
            return false;
        }

        return $this->update(['estado' => self::ESTADO_CANCELADA]);
    }

    /**
     * Reprogramar visita
     */
    public function reprogramar(Carbon $nuevaFecha): bool
    {
        if ($this->estado === self::ESTADO_REALIZADA) {
            return false;
        }

        return $this->update([
            'fecha_hora' => $nuevaFecha,
            'estado' => self::ESTADO_PROGRAMADA
        ]);
    }

    /**
     * Verificar si la visita está programada
     */
    public function estaProgramada(): bool
    {
        return $this->estado === self::ESTADO_PROGRAMADA;
    }

    /**
     * Verificar si la visita está realizada
     */
    public function estaRealizada(): bool
    {
        return $this->estado === self::ESTADO_REALIZADA;
    }

    /**
     * Verificar si la visita está cancelada
     */
    public function estaCancelada(): bool
    {
        return $this->estado === self::ESTADO_CANCELADA;
    }

    /**
     * Verificar si la visita se puede modificar
     */
    public function esModificable(): bool
    {
        return $this->estado === self::ESTADO_PROGRAMADA;
    }
}
