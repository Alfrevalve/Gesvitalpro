<?php

namespace App\Http\Controllers;

use App\Models\Institucion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InstitucionController extends Controller
{
    /**
     * Mostrar lista de instituciones
     */
    public function index()
    {
        $instituciones = Institucion::orderBy('nombre')->paginate(10);
        return view('instituciones.index', compact('instituciones'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        return view('instituciones.create');
    }

    /**
     * Almacenar nueva institución
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo_renipress' => 'nullable|string|max:50',
            'tipo_establecimiento' => 'required|string|in:hospital,clinica,consultorio',
            'categoria' => 'nullable|string|max:50',
            'red_salud' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'direccion' => 'required|string|max:255',
            'ciudad' => 'nullable|string|max:100',
            'estado' => 'string|in:active,inactive',
        ]);

        // Set default estado if not provided
        if (!isset($validated['estado'])) {
            $validated['estado'] = 'active';
        }

        $institucion = Institucion::create($validated);

        return redirect()
            ->route('instituciones.index')
            ->with('success', 'Institución creada exitosamente.');
    }

    /**
     * Mostrar detalles de institución
     */
    public function show(Institucion $institucion)
    {
        // Obtener estadísticas de visitas
        $visitas = $institucion->visitas()
            ->with(['medico', 'asesor'])
            ->latest('fecha_hora')
            ->paginate(10);

        $estadisticas = [
            'total_visitas' => $institucion->visitas()->count(),
            'visitas_mes' => $institucion->visitas()
                ->whereMonth('fecha_hora', now()->month)
                ->whereYear('fecha_hora', now()->year)
                ->count(),
            'ultima_visita' => $institucion->visitas()
                ->latest('fecha_hora')
                ->first(),
            'medicos_count' => $institucion->medicos()->count()
        ];

        return view('instituciones.show', compact('institucion', 'visitas', 'estadisticas'));
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Institucion $institucion)
    {
        return view('instituciones.edit', compact('institucion'));
    }

    /**
     * Actualizar institución
     */
    public function update(Request $request, Institucion $institucion)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo_renipress' => 'nullable|string|max:50',
            'tipo_establecimiento' => 'required|string|in:hospital,clinica,consultorio',
            'categoria' => 'nullable|string|max:50',
            'red_salud' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'direccion' => 'required|string|max:255',
            'ciudad' => 'nullable|string|max:100',
            'estado' => 'string|in:active,inactive',
        ]);

        $institucion->update($validated);

        return redirect()
            ->route('instituciones.show', $institucion)
            ->with('success', 'Institución actualizada exitosamente.');
    }

    /**
     * Eliminar institución
     */
    public function destroy(Institucion $institucion)
    {
        $institucion->delete();

        return redirect()
            ->route('instituciones.index')
            ->with('success', 'Institución eliminada exitosamente.');
    }
}
