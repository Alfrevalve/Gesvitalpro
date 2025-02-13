<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReporteVisita extends Model
{
    use HasFactory;

    protected $table = 'reportes_visita';

    protected $fillable = [
        'visita_id',
        'fecha_visita',
        'asesor_id',
        'institucion_id',
        'persona_contactada',
        'telefono',
        'motivo_visita',
        'resumen_seguimiento',
        'archivo_evidencia',
        'estado_seguimiento',
        'observaciones'
    ];

    protected $casts = [
        'fecha_visita' => 'date',
        'estado_seguimiento' => 'boolean'
    ];

    // Relación con la visita
    public function visita()
    {
        return $this->belongsTo(Visita::class);
    }

    // Relación con el asesor (usuario)
    public function asesor()
    {
        return $this->belongsTo(User::class, 'asesor_id');
    }

    // Relación con la institución
    public function institucion()
    {
        return $this->belongsTo(Institucion::class);
    }

    // Obtener URL del archivo de evidencia
    public function getEvidenciaUrlAttribute()
    {
        if ($this->archivo_evidencia) {
            return Storage::url($this->archivo_evidencia);
        }
        return null;
    }
}
