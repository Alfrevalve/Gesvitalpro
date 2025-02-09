<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ReportService;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function generateVisitsReport()
    {
        try {
            $visits = $this->reportService->generateVisitsReport();
            return response()->json($visits);
        } catch (\Exception $e) {
            // Manejo de errores mejorado
            return response()->json(['error' => 'Error generando el informe de visitas.'], 500);
        }
    }

    public function generateInventoryReport()
    {
        try {
            $inventory = $this->reportService->generateInventoryReport();
            return response()->json($inventory);
        } catch (\Exception $e) {
            // Manejo de errores mejorado
            return response()->json(['error' => 'Error generando el informe de inventario.'], 500);
        }
    }

    public function generateSurgeriesReport()
    {
        try {
            $surgeries = $this->reportService->generateSurgeriesReport();
            return response()->json($surgeries);
        } catch (\Exception $e) {
            // Manejo de errores mejorado
            return response()->json(['error' => 'Error generando el informe de cirugías.'], 500);
        }
    }
}
