<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PacienteUpdated extends Model
{
    protected $table = 'pacientes'; // Especificar el nombre de la tabla

    use HasFactory;

    protected $fillable = [
        'nombre', // Cambiar 'name' a 'nombre'
        'apellido', // Agregar 'apellido'
        'institucion', // Agregar 'institucion'
        // Agrega más campos según sea necesario
    ];

    public $timestamps = true; // Activar las marcas de tiempo

    public function cirugias()
    {
        return $this->hasMany(Cirugia::class, 'patient_id');  
    }
}
