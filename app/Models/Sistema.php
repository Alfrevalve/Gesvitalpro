<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sistema extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    // Relación con los reportes post cirugía
    public function reportesPostCirugia()
    {
        return $this->belongsToMany(ReportePostCirugia::class, 'reporte_sistema', 'sistema_id', 'reporte_id');
    }
}
