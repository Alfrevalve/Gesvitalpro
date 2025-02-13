<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportePostCirugia extends Model
{
    use HasFactory;

    protected $table = 'reportes_post_cirugia';

    protected $fillable = [
        'cirugia_id',
        'instrumentista_id',
        'medico_id',
        'paciente_id',
        'institucion_id',
        'fecha_cirugia',
        'hora_programada',
        'hora_inicio',
        'hora_fin',
        'hoja_consumo',
        'hoja_consumo_archivo',
        'sistemas',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'fecha_cirugia' => 'datetime',
        'hora_programada' => 'datetime',
        'hora_inicio' => 'datetime:H:i',
        'hora_fin' => 'datetime:H:i',
        'hoja_consumo' => 'boolean',
        'sistemas' => 'array'
    ];

    /**
     * Get the hoja de consumo file URL
     */
    public function getHojaConsumoUrlAttribute()
    {
        if ($this->hoja_consumo_archivo) {
            return Storage::url($this->hoja_consumo_archivo);
        }
        return null;
    }

    // Relación con la cirugía
    public function cirugia()
    {
        return $this->belongsTo(Cirugia::class);
    }

    // Relación con el instrumentista
    public function instrumentista()
    {
        return $this->belongsTo(User::class, 'instrumentista_id');
    }

    // Relación con el médico
    public function medico()
    {
        return $this->belongsTo(Doctor::class, 'medico_id');
    }

    // Relación con el paciente
    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    // Relación con la institución
    public function institucion()
    {
        return $this->belongsTo(Institucion::class, 'institucion_id');
    }
}
