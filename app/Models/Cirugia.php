<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cirugia extends Model  
{
    use HasFactory;

    protected $fillable = [
        'fecha_hora',
        'hospital',
        'equipo_requerido',
        'consumibles',
        'personal_asignado',
        'tipo_cirugia',
        'estado',
        'notas',
        'tiempo_estimado',
        'patient_id', // Add this line to link to the Patient model
    ];

    public function inventario()
    {
        return $this->belongsTo(Inventario::class); // Added relationship to Inventario
    }
}
