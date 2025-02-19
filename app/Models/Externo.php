<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Externo extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nombre',
        'email',
        'telefono',
        'especialidad',
        'institucion_id',
        'notas',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Obtener la institución a la que pertenece el personal externo
     */
    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class);
    }

    /**
     * Obtener las visitas asociadas al personal externo
     */
    public function visitas(): BelongsToMany
    {
        return $this->belongsToMany(Visita::class, 'visita_externo')
            ->withTimestamps();
    }

    /**
     * Obtener las cirugías asociadas al personal externo
     */
    public function surgeries(): BelongsToMany
    {
        return $this->belongsToMany(Surgery::class, 'surgery_externo')
            ->withTimestamps();
    }

    /**
     * Scope para filtrar por institución
     */
    public function scopeInstitucion($query, $institucionId)
    {
        return $query->where('institucion_id', $institucionId);
    }

    /**
     * Scope para filtrar por especialidad
     */
    public function scopeEspecialidad($query, $especialidad)
    {
        return $query->where('especialidad', $especialidad);
    }

    /**
     * Scope para búsqueda por nombre o especialidad
     */
    public function scopeBuscar($query, $termino)
    {
        return $query->where('nombre', 'like', "%{$termino}%")
            ->orWhere('especialidad', 'like', "%{$termino}%");
    }
}
