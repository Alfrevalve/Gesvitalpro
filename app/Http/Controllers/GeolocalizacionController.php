<?php

namespace App\Http\Controllers;

use App\Models\Institucion;
use App\Models\Zona;
use App\Models\User;
use App\Services\ServicioGeolocalizacion;
use Illuminate\Http\Request;

class GeolocalizacionController extends Controller
{
    protected $servicioGeolocalizacion;

    public function __construct(ServicioGeolocalizacion $servicioGeolocalizacion)
    {
        $this->servicioGeolocalizacion = $servicioGeolocalizacion;
    }

    public function mapa()
    {
        $instituciones = Institucion::with(['zona', 'staff'])
            ->whereNotNull('latitud')
            ->whereNotNull('longitud')
            ->get();

        $staff = User::whereHas('roles', function($query) {
            $query->whereIn('slug', ['instrumentista', 'vendedor']);
        })->get();

        return view('geolocalizacion.mapa', compact('instituciones', 'staff'));
    }

    public function rutas()
    {
        $instituciones = Institucion::with(['zona', 'staff'])
            ->whereNotNull('latitud')
            ->whereNotNull('longitud')
            ->get();

        $staff = User::whereHas('roles', function($query) {
            $query->whereIn('slug', ['instrumentista', 'vendedor']);
        })->get();

        $zonas = Zona::with('instituciones')->get();

        return view('geolocalizacion.rutas', compact('instituciones', 'staff', 'zonas'));
    }

    public function zonas()
    {
        $zonas = Zona::with(['instituciones'])->get();
        $instituciones = Institucion::whereNotNull('latitud')
            ->whereNotNull('longitud')
            ->get();

        return view('geolocalizacion.zonas', compact('zonas', 'instituciones'));
    }

    public function actualizarCoordenadas(Request $request)
    {
        $validated = $request->validate([
            'institucion_id' => 'required|exists:instituciones,id',
            'latitud' => 'required|numeric|between:-90,90',
            'longitud' => 'required|numeric|between:-180,180',
        ]);

        $institucion = Institucion::findOrFail($validated['institucion_id']);
        $institucion->update([
            'latitud' => $validated['latitud'],
            'longitud' => $validated['longitud'],
        ]);

        return response()->json([
            'message' => 'Coordenadas actualizadas correctamente',
            'institucion' => $institucion,
        ]);
    }

    public function calcularRuta(Request $request)
    {
        $validated = $request->validate([
            'origen_id' => 'required|exists:instituciones,id',
            'destino_id' => 'required|exists:instituciones,id',
            'modo' => 'required|in:driving,walking,bicycling,transit',
        ]);

        $origen = Institucion::findOrFail($validated['origen_id']);
        $destino = Institucion::findOrFail($validated['destino_id']);

        $ruta = $this->servicioGeolocalizacion->calcularRuta(
            [$origen->latitud, $origen->longitud],
            [$destino->latitud, $destino->longitud],
            $validated['modo']
        );

        return response()->json($ruta);
    }

    public function guardarZona(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:500',
            'poligono' => 'required|array',
            'poligono.*' => 'required|array',
            'poligono.*.*' => 'required|numeric',
            'instituciones' => 'required|array',
            'instituciones.*' => 'exists:instituciones,id',
        ]);

        $zona = Zona::create([
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'],
            'poligono' => $validated['poligono'],
        ]);

        $zona->instituciones()->sync($validated['instituciones']);

        return response()->json([
            'message' => 'Zona guardada correctamente',
            'zona' => $zona->load('instituciones'),
        ]);
    }

    public function obtenerPersonalCercano(Request $request)
    {
        $validated = $request->validate([
            'latitud' => 'required|numeric|between:-90,90',
            'longitud' => 'required|numeric|between:-180,180',
            'radio' => 'required|numeric|min:0',
            'tipo_personal' => 'required|in:instrumentista,vendedor',
        ]);

        $personalCercano = $this->servicioGeolocalizacion->buscarPersonalCercano(
            $validated['latitud'],
            $validated['longitud'],
            $validated['radio'],
            $validated['tipo_personal']
        );

        return response()->json($personalCercano);
    }

    public function actualizarUbicacionPersonal(Request $request)
    {
        $validated = $request->validate([
            'latitud' => 'required|numeric|between:-90,90',
            'longitud' => 'required|numeric|between:-180,180',
        ]);

        $user = auth()->user();
        $this->servicioGeolocalizacion->actualizarUbicacionPersonal(
            $user->id,
            $validated['latitud'],
            $validated['longitud']
        );

        return response()->json([
            'message' => 'Ubicación actualizada correctamente',
        ]);
    }
}
