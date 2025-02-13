<?php

namespace App\Http\Controllers;

use App\Models\ReporteVisita;
use App\Models\Visita;
use App\Models\Institucion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ReporteVisitaController extends Controller
{
    public function index()
    {
        $reportes = ReporteVisita::with(['visita', 'asesor', 'institucion'])
            ->orderBy('fecha_visita', 'desc')
            ->paginate(10);

        return view('reportes.visita.index', compact('reportes'));
    }

    public function create()
    {
        $instituciones = Institucion::all();
        $visitas = Visita::where('asesor_id', Auth::id())
            ->whereDate('fecha_hora', '>=', now()->subDays(30))
            ->get();

        return view('reportes.visita.create', compact('instituciones', 'visitas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'visita_id' => 'required|exists:visitas,id',
            'fecha_visita' => 'required|date',
            'institucion_id' => 'required|exists:institucions,id',
            'persona_contactada' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'motivo_visita' => 'required|string',
            'resumen_seguimiento' => 'required|string',
            'archivo_evidencia' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'estado_seguimiento' => 'required|boolean',
            'observaciones' => 'nullable|string'
        ]);

        $reporte = new ReporteVisita($request->except('archivo_evidencia'));
        $reporte->asesor_id = Auth::id();

        if ($request->hasFile('archivo_evidencia')) {
            $path = $request->file('archivo_evidencia')->store('evidencias-visita', 'public');
            $reporte->archivo_evidencia = $path;
        }

        $reporte->save();

        return redirect()->route('reportes.visita.index')
            ->with('success', 'Reporte de visita creado exitosamente.');
    }

    public function show(ReporteVisita $reporte)
    {
        $reporte->load(['visita', 'asesor', 'institucion']);
        return view('reportes.visita.show', compact('reporte'));
    }

    public function edit(ReporteVisita $reporte)
    {
        $instituciones = Institucion::all();
        $visitas = Visita::where('asesor_id', Auth::id())
            ->whereDate('fecha_hora', '>=', now()->subDays(30))
            ->get();

        return view('reportes.visita.edit', compact('reporte', 'instituciones', 'visitas'));
    }

    public function update(Request $request, ReporteVisita $reporte)
    {
        $request->validate([
            'visita_id' => 'required|exists:visitas,id',
            'fecha_visita' => 'required|date',
            'institucion_id' => 'required|exists:institucions,id',
            'persona_contactada' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'motivo_visita' => 'required|string',
            'resumen_seguimiento' => 'required|string',
            'archivo_evidencia' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'estado_seguimiento' => 'required|boolean',
            'observaciones' => 'nullable|string'
        ]);

        $reporte->fill($request->except('archivo_evidencia'));

        if ($request->hasFile('archivo_evidencia')) {
            // Eliminar archivo anterior si existe
            if ($reporte->archivo_evidencia) {
                Storage::disk('public')->delete($reporte->archivo_evidencia);
            }
            
            $path = $request->file('archivo_evidencia')->store('evidencias-visita', 'public');
            $reporte->archivo_evidencia = $path;
        }

        $reporte->save();

        return redirect()->route('reportes.visita.index')
            ->with('success', 'Reporte de visita actualizado exitosamente.');
    }

    public function destroy(ReporteVisita $reporte)
    {
        if ($reporte->archivo_evidencia) {
            Storage::disk('public')->delete($reporte->archivo_evidencia);
        }
        
        $reporte->delete();

        return redirect()->route('reportes.visita.index')
            ->with('success', 'Reporte de visita eliminado exitosamente.');
    }
}
