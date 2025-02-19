<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;

class Zona extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Estados de la zona
     */
    public const STATUS_ACTIVE = 'activa';
    public const STATUS_INACTIVE = 'inactiva';

    protected $fillable = [
        'nombre',
        'descripcion',
        'color',
        'poligono',
        'estado',
        'radio_km',
        'centro_lat',
        'centro_lng',
        'metadata',
    ];

    protected $casts = [
        'poligono' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'centro_lat' => 'float',
        'centro_lng' => 'float',
        'radio_km' => 'float',
    ];

    /**
     * Reglas de validación
     */
    public static $rules = [
        'nombre' => 'required|string|max:255',
        'descripcion' => 'nullable|string|max:1000',
        'color' => 'required|string|max:7',
        'poligono' => 'required|array',
        'estado' => 'required|in:activa,inactiva',
        'radio_km' => 'nullable|numeric|min:0',
        'centro_lat' => 'nullable|numeric|between:-90,90',
        'centro_lng' => 'nullable|numeric|between:-180,180',
        'metadata' => 'nullable|array',
    ];

    /**
     * Relaciones
     */
    public function instituciones(): BelongsToMany
    {
        return $this->belongsToMany(Institucion::class, 'zona_institucion')
            ->withTimestamps();
    }

    /**
     * Métodos de estado
     */
    public function isActive(): bool
    {
        return $this->estado === self::STATUS_ACTIVE;
    }

    public function activate(): bool
    {
        return $this->update(['estado' => self::STATUS_ACTIVE]);
    }

    public function deactivate(): bool
    {
        return $this->update(['estado' => self::STATUS_INACTIVE]);
    }

    /**
     * Métodos geoespaciales
     */
    public function contienePunto(float $latitud, float $longitud): bool
    {
        if ($this->radio_km) {
            return $this->puntoEnRadio($latitud, $longitud);
        }

        $vertices = collect($this->poligono)->map(function ($punto) {
            return [$punto['lat'], $punto['lng']];
        })->toArray();

        return $this->puntoEnPoligono($latitud, $longitud, $vertices);
    }

    protected function puntoEnPoligono(float $lat, float $lng, array $vertices): bool
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

    protected function puntoEnRadio(float $lat, float $lng): bool
    {
        if (!$this->centro_lat || !$this->centro_lng || !$this->radio_km) {
            return false;
        }

        $distancia = $this->calcularDistancia(
            $this->centro_lat,
            $this->centro_lng,
            $lat,
            $lng
        );

        return $distancia <= $this->radio_km;
    }

    protected function calcularDistancia(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $r = 6371; // Radio de la Tierra en km

        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $dlon = $lon2 - $lon1;
        $dlat = $lat2 - $lat1;

        $a = sin($dlat/2) * sin($dlat/2) + cos($lat1) * cos($lat2) * sin($dlon/2) * sin($dlon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $r * $c;
    }

    public function getCentro(): array
    {
        if ($this->centro_lat && $this->centro_lng) {
            return [
                'lat' => $this->centro_lat,
                'lng' => $this->centro_lng,
            ];
        }

        $latitudes = collect($this->poligono)->pluck('lat');
        $longitudes = collect($this->poligono)->pluck('lng');

        return [
            'lat' => $latitudes->sum() / $latitudes->count(),
            'lng' => $longitudes->sum() / $longitudes->count(),
        ];
    }

    public function getArea(): float
    {
        if ($this->radio_km) {
            return pi() * pow($this->radio_km, 2);
        }

        $vertices = collect($this->poligono)->map(function ($punto) {
            return [$punto['lat'], $punto['lng']];
        })->toArray();

        return $this->calcularAreaPoligono($vertices);
    }

    protected function calcularAreaPoligono(array $vertices): float
    {
        $n = count($vertices);
        if ($n < 3) return 0;

        $area = 0;
        $j = $n - 1;

        for ($i = 0; $i < $n; $i++) {
            $area += ($vertices[$j][0] + $vertices[$i][0]) * ($vertices[$j][1] - $vertices[$i][1]);
            $j = $i;
        }

        return abs($area * 111 * 111 / 2);
    }

    /**
     * Métodos de utilidad
     */
    public function actualizarCentroide(): bool
    {
        $centro = $this->getCentro();
        return $this->update([
            'centro_lat' => $centro['lat'],
            'centro_lng' => $centro['lng'],
        ]);
    }

    public function getInstitucionesEnZona(): Collection
    {
        return Institucion::whereNotNull('latitud')
            ->whereNotNull('longitud')
            ->get()
            ->filter(function ($institucion) {
                return $this->contienePunto($institucion->latitud, $institucion->longitud);
            });
    }

    public function getCobertura(): array
    {
        $instituciones = $this->getInstitucionesEnZona();
        $total = Institucion::count();

        return [
            'total_instituciones' => $instituciones->count(),
            'porcentaje_cobertura' => $total > 0 ? ($instituciones->count() / $total) * 100 : 0,
            'area_km2' => $this->getArea(),
            'densidad' => $this->getArea() > 0 ? $instituciones->count() / $this->getArea() : 0,
        ];
    }

    /**
     * Scopes
     */
    public function scopeActivas($query)
    {
        return $query->where('estado', self::STATUS_ACTIVE);
    }

    public function scopeConteniendo($query, float $latitud, float $longitud)
    {
        return $query->get()->filter(function ($zona) use ($latitud, $longitud) {
            return $zona->contienePunto($latitud, $longitud);
        });
    }

    public function scopeBuscar($query, string $termino)
    {
        return $query->where('nombre', 'like', "%{$termino}%")
            ->orWhere('descripcion', 'like', "%{$termino}%");
    }

    public function scopePorArea($query, float $minArea = null, float $maxArea = null)
    {
        return $query->get()->filter(function ($zona) use ($minArea, $maxArea) {
            $area = $zona->getArea();
            if ($minArea && $area < $minArea) return false;
            if ($maxArea && $area > $maxArea) return false;
            return true;
        });
    }

    /**
     * Obtener estadísticas de la zona
     */
    public function getStats(): array
    {
        $instituciones = $this->getInstitucionesEnZona();

        return [
            'cobertura' => $this->getCobertura(),
            'instituciones' => [
                'total' => $instituciones->count(),
                'por_tipo' => $instituciones->groupBy('tipo_establecimiento')->map->count(),
            ],
            'visitas' => [
                'total' => Visita::whereIn('institucion_id', $instituciones->pluck('id'))->count(),
                'este_mes' => Visita::whereIn('institucion_id', $instituciones->pluck('id'))
                    ->whereMonth('fecha_hora', now()->month)
                    ->count(),
            ],
            'cirugias' => [
                'total' => Surgery::whereIn('institucion_id', $instituciones->pluck('id'))->count(),
                'este_mes' => Surgery::whereIn('institucion_id', $instituciones->pluck('id'))
                    ->whereMonth('surgery_date', now()->month)
                    ->count(),
            ],
        ];
    }
}
