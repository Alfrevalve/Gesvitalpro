<?php

namespace App\Http\Controllers;

use App\Models\Medico;
use App\Models\Institucion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MedicoController extends Controller
{
    /**
     * Mostrar lista de médicos
     */
    public function index(Request $request)
    {
        $query = Medico::with('institucion');

        // Filtrar por institución si se proporciona
        if ($request->has('institucion_id')) {
            $query->where('institucion_id', $request->institucion_id);
        }

        $medicos = $query->orderBy('nombre')->paginate(10);
        $instituciones = Institucion::orderBy('nombre')->pluck('nombre', 'id');

        return view('medicos.index', compact('medicos', 'instituciones'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        $instituciones = Institucion::orderBy('nombre')->pluck('nombre', 'id');
        return view('medicos.create', compact('instituciones'));
    }

    /**
     * Almacenar nuevo médico
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'especialidad' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'institucion_id' => 'required|exists:instituciones,id'
        ]);

        Medico::create($validated);

        return redirect()->route('medicos.index')
            ->with('success', 'Médico registrado exitosamente.');
    }

    /**
     * Mostrar detalles de médico
     */
    public function show(Medico $medico)
    {
        // Obtener estadísticas de visitas
        $visitas = $medico->visitas()
            ->with(['institucion', 'asesor'])
            ->latest('fecha_hora')
            ->paginate(10);

        $estadisticas = [
            'total_visitas' => $medico->visitas()->realizadas()->count(),
            'visitas_mes' => $medico->frecuenciaVisitas(now()->startOfMonth(), now()->endOfMonth()),
            'ultima_visita' => $medico->ultimaVisita()
        ];

        return view('medicos.show', compact('medico', 'visitas', 'estadisticas'));
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Medico $medico)
    {
        $instituciones = Institucion::orderBy('nombre')->pluck('nombre', 'id');
        return view('medicos.edit', compact('medico', 'instituciones'));
    }

    /**
     * Actualizar médico
     */
    public function update(Request $request, Medico $medico)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'especialidad' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'institucion_id' => 'required|exists:instituciones,id'
        ]);

        $medico->update($validated);

        return redirect()->route('medicos.show', $medico)
            ->with('success', 'Información del médico actualizada exitosamente.');
    }

    /**
     * Eliminar médico
     */
    public function destroy(Medico $medico)
    {
        $medico->delete();

        return redirect()->route('medicos.index')
            ->with('success', 'Médico eliminado exitosamente.');
    }

    /**
     * Mostrar reporte de cobertura
     */
    public function reporteCobertura(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->startOfMonth());
        $fechaFin = $request->input('fecha_fin', now()->endOfMonth());

        $medicos = Medico::with('institucion')
            ->withCount(['visitas' => function($query) use ($fechaInicio, $fechaFin) {
                $query->realizadas()->entreFechas($fechaInicio, $fechaFin);
            }])
            ->get();

        $estadisticas = [
            'total_medicos' => $medicos->count(),
            'medicos_visitados' => $medicos->where('visitas_count', '>', 0)->count(),
            'porcentaje_cobertura' => $medicos->count() > 0 
                ? ($medicos->where('visitas_count', '>', 0)->count() / $medicos->count()) * 100 
                : 0
        ];

        return view('medicos.reporte-cobertura', compact('medicos', 'estadisticas', 'fechaInicio', 'fechaFin'));
    }
}
