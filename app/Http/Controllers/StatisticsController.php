<?php

namespace App\Http\Controllers;

use App\Services\StatisticsService;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    protected $statisticsService;

    public function __construct(StatisticsService $statisticsService)
    {
        $this->statisticsService = $statisticsService;
    }

    public function index()
    {
        try {
            $totalCirugiasPorInstrumentista = $this->statisticsService->totalCirugiasPorInstrumentista();
            $tiposCirugiasPorDoctor = $this->statisticsService->tiposCirugiasPorDoctor();
            $cirugiasPorInstitucion = $this->statisticsService->cirugiasPorInstitucion();

            return view('statistics.index', compact('totalCirugiasPorInstrumentista', 'tiposCirugiasPorDoctor', 'cirugiasPorInstitucion'));
        } catch (\Exception $e) {
            // Manejo de errores mejorado
            return response()->json(['error' => 'Error al generar estadísticas.'], 500);
        }
    }
}
