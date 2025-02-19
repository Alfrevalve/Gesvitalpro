<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Institucion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'instituciones';

    protected $fillable = [
        'nombre',
        'codigo_renipress',
        'tipo_establecimiento',
        'categoria',
        'red_salud',
        'latitud',
        'longitud',
        'datos_ubicacion',
        'telefono',
        'email',
        'direccion',
        'ciudad',
        'estado'
    ];

    protected $casts = [
        'datos_ubicacion' => 'array',
        'latitud' => 'decimal:8',
        'longitud' => 'decimal:8',
    ];

    protected $dates = ['deleted_at'];

    public function getTipoIconClass()
    {
        return match(strtolower($this->tipo_establecimiento)) {
            'hospital' => 'bi-hospital',
            'clinica' => 'bi-heart-pulse',
            default => 'bi-house-heart'
        };
    }

    public function getTipoBackgroundClass()
    {
        return 'tipo-' . strtolower($this->tipo_establecimiento ?? 'otro');
    }

    public function lines(): BelongsToMany
    {
        return $this->belongsToMany(Line::class, 'institucion_line', 'institucion_id', 'line_id')
                    ->withTimestamps();
    }

    public function medicos()
    {
        return $this->hasMany(Medico::class);
    }

    public function visitas()
    {
        return $this->hasMany(Visita::class);
    }

    public function surgeries()
    {
        return $this->hasMany(Surgery::class);
    }

    public function zonas()
    {
        return $this->belongsToMany(Zona::class, 'zona_institucion');
    }
}
