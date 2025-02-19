<?php

namespace App\Services;

use App\Models\Institucion;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ServicioGeolocalizacion
{
    protected $urlApiMinsa = 'https://geominsa.minsa.gob.pe/geominsaportal/rest/services/';

    /**
     * Actualizar datos de geolocalización de una institución
     */
    public function actualizarUbicacion(Institucion $institucion)
    {
        try {
            $datos = $this->obtenerDatosMinsa($institucion->codigo_renipress);
            
            if ($datos) {
                $institucion->update([
                    'latitud' => $datos['latitud'],
                    'longitud' => $datos['longitud'],
                    'tipo_establecimiento' => $datos['tipo_establecimiento'],
                    'categoria' => $datos['categoria'],
                    'red_salud' => $datos['red_salud'],
                    'datos_ubicacion' => $datos['datos_adicionales']
                ]);

                return true;
            }
        } catch (\Exception $e) {
            Log::error('Error al actualizar ubicación de institución', [
                'institucion_id' => $institucion->id,
                'error' => $e->getMessage()
            ]);
        }

        return false;
    }

    /**
     * Obtener instituciones cercanas
     */
    public function obtenerInstitucionesCercanas($latitud, $longitud, $radioKm = 5)
    {
        $claveCache = "instituciones_cercanas_{$latitud}_{$longitud}_{$radioKm}";

        return Cache::remember($claveCache, now()->addHours(24), function () use ($latitud, $longitud, $radioKm) {
            return $this->buscarInstitucionesCercanas($latitud, $longitud, $radioKm);
        });
    }

    /**
     * Calcular ruta óptima para visitas
     */
    public function calcularRutaOptima(array $instituciones)
    {
        $puntos = collect($instituciones)->map(function ($institucion) {
            return [
                'id' => $institucion->id,
                'nombre' => $institucion->nombre,
                'latitud' => $institucion->latitud,
                'longitud' => $institucion->longitud
            ];
        })->toArray();

        return $this->optimizarRuta($puntos);
    }

    /**
     * Obtener datos desde la API de MINSA
     */
    protected function obtenerDatosMinsa($codigoRenipress)
    {
        try {
            $respuesta = Http::get($this->urlApiMinsa . 'query', [
                'where' => "codigo_renipress = '{$codigoRenipress}'",
                'outFields' => '*',
                'f' => 'json'
            ]);

            if ($respuesta->successful() && isset($respuesta->json()['features'][0])) {
                $datos = $respuesta->json()['features'][0]['attributes'];
                
                return [
                    'latitud' => $datos['LATITUD'] ?? null,
                    'longitud' => $datos['LONGITUD'] ?? null,
                    'tipo_establecimiento' => $datos['TIPO_ESTABLECIMIENTO'] ?? null,
                    'categoria' => $datos['CATEGORIA'] ?? null,
                    'red_salud' => $datos['RED_SALUD'] ?? null,
                    'datos_adicionales' => [
                        'departamento' => $datos['DEPARTAMENTO'] ?? null,
                        'provincia' => $datos['PROVINCIA'] ?? null,
                        'distrito' => $datos['DISTRITO'] ?? null,
                        'direccion' => $datos['DIRECCION'] ?? null,
                        'telefono' => $datos['TELEFONO'] ?? null,
                        'horario' => $datos['HORARIO_ATENCION'] ?? null
                    ]
                ];
            }
        } catch (\Exception $e) {
            Log::error('Error al obtener datos de MINSA', [
                'codigo_renipress' => $codigoRenipress,
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }

    /**
     * Buscar instituciones cercanas
     */
    protected function buscarInstitucionesCercanas($latitud, $longitud, $radioKm)
    {
        $radioMetros = $radioKm * 1000;

        try {
            $respuesta = Http::get($this->urlApiMinsa . 'query', [
                'geometry' => "{$longitud},{$latitud}",
                'geometryType' => 'esriGeometryPoint',
                'distance' => $radioMetros,
                'units' => 'esriSRUnit_Meter',
                'outFields' => '*',
                'f' => 'json'
            ]);

            if ($respuesta->successful()) {
                return collect($respuesta->json()['features'])->map(function ($feature) {
                    $datos = $feature['attributes'];
                    return [
                        'codigo_renipress' => $datos['CODIGO_RENIPRESS'],
                        'nombre' => $datos['NOMBRE_ESTABLECIMIENTO'],
                        'tipo' => $datos['TIPO_ESTABLECIMIENTO'],
                        'categoria' => $datos['CATEGORIA'],
                        'latitud' => $datos['LATITUD'],
                        'longitud' => $datos['LONGITUD'],
                        'distancia' => round($datos['DISTANCE'] / 1000, 2), // Convertir a kilómetros
                        'direccion' => $datos['DIRECCION'],
                        'telefono' => $datos['TELEFONO']
                    ];
                });
            }
        } catch (\Exception $e) {
            Log::error('Error al buscar instituciones cercanas', [
                'coordenadas' => "{$latitud},{$longitud}",
                'error' => $e->getMessage()
            ]);
        }

        return collect();
    }

    /**
     * Optimizar ruta entre puntos (algoritmo del vecino más cercano)
     */
    protected function optimizarRuta(array $puntos)
    {
        if (empty($puntos)) {
            return [];
        }

        $ruta = [];
        $noVisitados = $puntos;
        
        // Comenzar desde el primer punto
        $actual = array_shift($noVisitados);
        $ruta[] = $actual;

        // Mientras queden puntos por visitar
        while (!empty($noVisitados)) {
            $masCercano = null;
            $distanciaMinima = PHP_FLOAT_MAX;

            // Encontrar el punto más cercano
            foreach ($noVisitados as $indice => $punto) {
                $distancia = $this->calcularDistancia(
                    $actual['latitud'],
                    $actual['longitud'],
                    $punto['latitud'],
                    $punto['longitud']
                );

                if ($distancia < $distanciaMinima) {
                    $distanciaMinima = $distancia;
                    $masCercano = $indice;
                }
            }

            // Agregar el punto más cercano a la ruta
            $actual = $noVisitados[$masCercano];
            $ruta[] = $actual;
            unset($noVisitados[$masCercano]);
            $noVisitados = array_values($noVisitados);
        }

        return [
            'ruta' => $ruta,
            'distancia_total' => $this->calcularDistanciaTotal($ruta),
            'tiempo_estimado' => $this->estimarTiempoViaje($ruta)
        ];
    }

    /**
     * Calcular distancia entre dos puntos (fórmula de Haversine)
     */
    protected function calcularDistancia($lat1, $lon1, $lat2, $lon2)
    {
        $radioTierra = 6371; // Radio de la Tierra en kilómetros

        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $dLat = $lat2 - $lat1;
        $dLon = $lon2 - $lon1;

        $a = sin($dLat/2) * sin($dLat/2) +
             cos($lat1) * cos($lat2) *
             sin($dLon/2) * sin($dLon/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $radioTierra * $c;
    }

    /**
     * Calcular distancia total de la ruta
     */
    protected function calcularDistanciaTotal(array $ruta)
    {
        $distanciaTotal = 0;
        $puntos = count($ruta);

        for ($i = 0; $i < $puntos - 1; $i++) {
            $distanciaTotal += $this->calcularDistancia(
                $ruta[$i]['latitud'],
                $ruta[$i]['longitud'],
                $ruta[$i + 1]['latitud'],
                $ruta[$i + 1]['longitud']
            );
        }

        return round($distanciaTotal, 2);
    }

    /**
     * Estimar tiempo de viaje
     */
    protected function estimarTiempoViaje(array $ruta)
    {
        // Velocidad promedio estimada: 30 km/h en ciudad
        $velocidadPromedio = 30;
        
        // Tiempo promedio por visita: 30 minutos
        $tiempoPorVisita = 30;

        $distanciaTotal = $this->calcularDistanciaTotal($ruta);
        $tiempoViaje = ($distanciaTotal / $velocidadPromedio) * 60; // Convertir a minutos
        $tiempoVisitas = count($ruta) * $tiempoPorVisita;

        return [
            'minutos_viaje' => round($tiempoViaje),
            'minutos_visitas' => $tiempoVisitas,
            'minutos_totales' => round($tiempoViaje + $tiempoVisitas),
            'horas_estimadas' => round(($tiempoViaje + $tiempoVisitas) / 60, 1)
        ];
    }
}
