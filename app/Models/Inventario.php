<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventario extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'categoria',
        'quantity', // Updated from 'cantidad' to 'quantity'
        'nivel_minimo',
        'ubicacion',
        'fecha_mantenimiento',
        'supplier',
        'expiration_date',
        'cost',
    ];

    public function cirugias()
    {
        return $this->hasMany(Cirugia::class); // Added relationship to Cirugia
    }
}
