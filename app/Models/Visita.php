<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visita extends Model
{
    use HasFactory;

    protected $fillable = [
        'fecha_hora',
        'institucion',
        'persona_contactada',
        'motivo',
        'seguimiento_requerido',
        'duration',
        'notes',
        'outcome',
    ];
}