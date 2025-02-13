<?php

namespace App\Http\Controllers;

use App\Models\ReportePostCirugia;
use App\Models\Cirugia;
use App\Models\Sistema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportePostCirugiaController extends Controller
{
    public function create()
    {
        // Obtener el instrumentista logueado
        $instrumentista = Auth::user();
        
        // Obtener las cirugías asignadas al instrumentista para la fecha actual
        $cirugias = Cirugia::where('personal_asignado', 'LIKE', '%' . $instrumentista->id . '%')
            ->whereDate('fecha_hora', now())
            ->with(['medico', 'paciente', 'institucion'])
            ->get();

        // Obtener los sistemas disponibles
        $sistemas = Sistema::all();

        return view('reportes.post-cirugia.create', compact('cirugias', 'sistemas', 'instrumentista'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cirugia_id' => 'required|exists:cirugias,id',
            'fecha_cirugia' => 'required|date',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'hoja_consumo' => 'required|boolean',
            'hoja_consumo_archivo' => 'required_if:hoja_consumo,1|file|mimes:jpg,jpeg,pdf|max:2048',
            'sistemas' => 'required|array',
            'sistemas.*' => 'exists:sistemas,id'
        ]);

        // Obtener la cirugía seleccionada
        $cirugia = Cirugia::with(['medico', 'paciente', 'institucion'])->findOrFail($request->cirugia_id);

        // Crear el reporte
        $reporte = new ReportePostCirugia();
        $reporte->cirugia_id = $cirugia->id;
        $reporte->instrumentista_id = Auth::id();
        $reporte->medico_id = $cirugia->medico_id;
        $reporte->paciente_id = $cirugia->paciente_id;
        $reporte->institucion_id = $cirugia->institucion_id;
        $reporte->fecha_cirugia = $request->fecha_cirugia;
        $reporte->hora_programada = $cirugia->fecha_hora;
        $reporte->hora_inicio = $request->hora_inicio;
        $reporte->hora_fin = $request->hora_fin;
        $reporte->hoja_consumo = $request->hoja_consumo;
        $reporte->sistemas = $request->sistemas;

        // Manejar la subida del archivo de hoja de consumo
        if ($request->hasFile('hoja_consumo_archivo') && $request->hoja_consumo) {
            $file = $request->file('hoja_consumo_archivo');
            $path = $file->store('hojas-consumo', 'public');
            $reporte->hoja_consumo_archivo = $path;
        }

        $reporte->save();

        return redirect()->route('reportes.index')
            ->with('success', 'Reporte post cirugía creado exitosamente.');
    }

    public function index()
    {
        $reportes = ReportePostCirugia::with(['cirugia', 'instrumentista', 'medico', 'paciente', 'institucion'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('reportes.post-cirugia.index', compact('reportes'));
    }

    public function show(ReportePostCirugia $reporte)
    {
        $reporte->load(['cirugia', 'instrumentista', 'medico', 'paciente', 'institucion']);
        return view('reportes.post-cirugia.show', compact('reporte'));
    }

    /**
     * Obtiene las cirugías programadas para una fecha específica
     * y asignadas al instrumentista autenticado
     */
    public function getCirugiasPorFecha($fecha)
    {
        $instrumentista = Auth::user();
        
        $cirugias = Cirugia::where('personal_asignado', 'LIKE', '%' . $instrumentista->id . '%')
            ->whereDate('fecha_hora', $fecha)
            ->with(['medico', 'paciente', 'institucion'])
            ->get();

        return response()->json($cirugias);
    }
}
