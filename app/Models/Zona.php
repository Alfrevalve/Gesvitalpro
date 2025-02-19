<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Zona extends Model
{
    protected $fillable = [
        'nombre',
        'color',
        'poligono',
    ];

    protected $casts = [
        'poligono' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Obtener las instituciones dentro de la zona
     */
    public function instituciones(): BelongsToMany
    {
        return $this->belongsToMany(Institucion::class, 'zona_institucion')
            ->withTimestamps();
    }

    /**
     * Verificar si un punto está dentro de la zona
     */
    public function contienePunto($latitud, $longitud): bool
    {
        $vertices = collect($this->poligono)->map(function ($punto) {
            return [$punto['lat'], $punto['lng']];
        })->toArray();

        return $this->puntoEnPoligono($latitud, $longitud, $vertices);
    }

    /**
     * Algoritmo de Ray Casting para determinar si un punto está dentro de un polígono
     */
    protected function puntoEnPoligono($lat, $lng, $vertices): bool
    {
        $dentro = false;
        $j = count($vertices) - 1;

        for ($i = 0; $i < count($vertices); $i++) {
            if ((($vertices[$i][1] > $lng) != ($vertices[$j][1] > $lng)) &&
                ($lat < ($vertices[$j][0] - $vertices[$i][0]) * ($lng - $vertices[$i][1]) /
                    ($vertices[$j][1] - $vertices[$i][1]) + $vertices[$i][0])) {
                $dentro = !$dentro;
            }
            $j = $i;
        }

        return $dentro;
    }

    /**
     * Calcular el centro aproximado de la zona
     */
    public function getCentro(): array
    {
        $latitudes = collect($this->poligono)->pluck('lat');
        $longitudes = collect($this->poligono)->pluck('lng');

        return [
            'lat' => $latitudes->sum() / $latitudes->count(),
            'lng' => $longitudes->sum() / $longitudes->count(),
        ];
    }

    /**
     * Obtener el área aproximada de la zona en kilómetros cuadrados
     */
    public function getArea(): float
    {
        $vertices = collect($this->poligono)->map(function ($punto) {
            return [$punto['lat'], $punto['lng']];
        })->toArray();

        return $this->calcularAreaPoligono($vertices);
    }

    /**
     * Calcular el área de un polígono usando la fórmula del área de Gauss
     */
    protected function calcularAreaPoligono($vertices): float
    {
        $n = count($vertices);
        if ($n < 3) return 0;

        $area = 0;
        $j = $n - 1;

        for ($i = 0; $i < $n; $i++) {
            $area += ($vertices[$j][0] + $vertices[$i][0]) * ($vertices[$j][1] - $vertices[$i][1]);
            $j = $i;
        }

        // Convertir a kilómetros cuadrados (aproximado)
        return abs($area * 111 * 111 / 2);
    }

    /**
     * Scope para buscar zonas que contengan un punto
     */
    public function scopeConteniendo($query, $latitud, $longitud)
    {
        return $query->get()->filter(function ($zona) use ($latitud, $longitud) {
            return $zona->contienePunto($latitud, $longitud);
        });
    }

    /**
     * Scope para buscar por nombre
     */
    public function scopeBuscar($query, $termino)
    {
        return $query->where('nombre', 'like', "%{$termino}%");
    }
}
