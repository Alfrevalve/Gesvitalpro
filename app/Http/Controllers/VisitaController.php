<?php

namespace App\Http\Controllers;

use App\Models\Visita;
use App\Models\Medico;
use App\Models\Institucion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Http\Requests\StoreVisitaRequest;
use Illuminate\Support\Facades\DB;

class VisitaController extends Controller
{
    /**
     * Mostrar lista de visitas
     */
    public function index(Request $request)
    {
        $query = Visita::with(['institucion', 'medico', 'asesor']);
        $this->applyFilters($request, $query);

        $visitas = $query->latest('fecha_hora')->paginate(10);
        $instituciones = Institucion::orderBy('nombre')->pluck('nombre', 'id');
        $medicos = Medico::orderBy('nombre')->pluck('nombre', 'id');
        $instituciones_count = Institucion::count();

        return view('visitas.index', compact('visitas', 'instituciones', 'medicos', 'instituciones_count'));
    }

    private function applyFilters(Request $request, $query)
    {
        // Aplicar filtros
        if ($request->has('institucion_id')) {
            $query->where('institucion_id', $request->institucion_id);
        }
        if ($request->has('medico_id')) {
            $query->where('medico_id', $request->medico_id);
        }
        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
            $query->entreFechas($request->fecha_inicio, $request->fecha_fin);
        }
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        $instituciones = Institucion::orderBy('nombre')->pluck('nombre', 'id');
        $medicos = Medico::orderBy('nombre')->pluck('nombre', 'id');
        return view('visitas.create', compact('instituciones', 'medicos'));
    }

    /**
     * Almacenar nueva visita
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'fecha_hora' => 'required|date|after:now',
                'institucion_id' => 'required|exists:instituciones,id',
                'medico_id' => 'required|exists:medicos,id',
                'motivo' => 'required|string|max:255',
                'observaciones' => 'nullable|string'
            ], [
                'fecha_hora.after' => 'La fecha y hora de la visita debe ser futura',
                'institucion_id.required' => 'Debe seleccionar una institución',
                'medico_id.required' => 'Debe seleccionar un médico',
                'motivo.required' => 'El motivo de la visita es requerido'
            ]);

            $validated['user_id'] = Auth::id();
            $validated['estado'] = Visita::ESTADO_PROGRAMADA;

            DB::beginTransaction();

            $visita = Visita::create($validated);

            DB::commit();

            return redirect()->route('visitas.index')
                ->with('success', 'Visita programada exitosamente para el ' .
                    Carbon::parse($visita->fecha_hora)->format('d/m/Y H:i'));
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error('Error al registrar visita: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al registrar la visita. Por favor, intente nuevamente.');
        }
    }

    /**
     * Mostrar detalles de visita
     */
    public function show(Visita $visita)
    {
        return view('visitas.show', compact('visita'));
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Visita $visita)
    {
        $instituciones = Institucion::orderBy('nombre')->pluck('nombre', 'id');
        $medicos = Medico::orderBy('nombre')->pluck('nombre', 'id');
        return view('visitas.edit', compact('visita', 'instituciones', 'medicos'));
    }

    /**
     * Actualizar visita
     */
    public function update(Request $request, Visita $visita)
    {
        $validated = $request->validate([
            'fecha_hora' => 'required|date',
            'institucion_id' => 'required|exists:instituciones,id',
            'medico_id' => 'required|exists:medicos,id',
            'motivo' => 'required|string|max:255',
            'observaciones' => 'nullable|string',
            'estado' => 'required|in:programada,realizada,cancelada'
        ]);

        $visita->update($validated);

        return redirect()->route('visitas.show', $visita)
            ->with('success', 'Visita actualizada exitosamente.');
    }

    /**
     * Eliminar visita
     */
    public function destroy(Visita $visita)
    {
        $visita->delete();

        return redirect()->route('visitas.index')
            ->with('success', 'Visita eliminada exitosamente.');
    }

    /**
     * Marcar visita como realizada
     */
    public function marcarRealizada(Visita $visita)
    {
        $visita->marcarComoRealizada();
        return redirect()->back()->with('success', 'Visita marcada como realizada.');
    }

    /**
     * Marcar visita como cancelada
     */
    public function marcarCancelada(Visita $visita)
    {
        $visita->marcarComoCancelada();
        return redirect()->back()->with('success', 'Visita marcada como cancelada.');
    }

    /**
     * Mostrar reporte de frecuencia de visitas
     */
    public function reporteFrecuencia(Request $request)
    {
        $fechaInicio = Carbon::parse($request->input('fecha_inicio', now()->startOfMonth()));
        $fechaFin = Carbon::parse($request->input('fecha_fin', now()->endOfMonth()));

        // Asegurar que fechaFin no sea anterior a fechaInicio
        if ($fechaFin->isBefore($fechaInicio)) {
            $fechaFin = $fechaInicio->copy()->endOfMonth();
        }

        // Obtener visitas por día
        $visitasPorDia = Visita::where('estado', 'realizada')
            ->whereBetween('fecha_hora', [$fechaInicio->startOfDay(), $fechaFin->endOfDay()])
            ->selectRaw('DATE(fecha_hora) as fecha, COUNT(*) as total')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        // Obtener visitas por asesor
        $visitasPorAsesor = Visita::where('estado', 'realizada')
            ->whereBetween('fecha_hora', [$fechaInicio->startOfDay(), $fechaFin->endOfDay()])
            ->with('asesor:id,name')
            ->selectRaw('user_id, COUNT(*) as total')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->get();

        // Calcular estadísticas
        $estadisticas = [
            'total_visitas' => $visitasPorDia->sum('total'),
            'promedio_diario' => $visitasPorDia->avg('total'),
            'dia_mas_visitas' => $visitasPorDia->sortByDesc('total')->first(),
            'asesor_mas_visitas' => $visitasPorAsesor->first()
        ];

        return view('visitas.reporte-frecuencia', compact(
            'visitasPorDia',
            'visitasPorAsesor',
            'estadisticas',
            'fechaInicio',
            'fechaFin'
        ));
    }
}
