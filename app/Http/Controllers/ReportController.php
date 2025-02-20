<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use App\Models\SurgicalDashboard;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Generar reporte en PDF
     */
    public function generatePDF(Request $request)
    {
        $dashboard = new SurgicalDashboard();
        $metrics = $dashboard->getMetrics();

        $fileName = $this->reportService->generatePDFReport($dashboard);

        return response()->download(storage_path("app/reports/{$fileName}"));
    }

    /**
     * Generar reporte en Excel
     */
    public function generateExcel(Request $request)
    {
        $dashboard = new SurgicalDashboard();
        $metrics = $dashboard->getMetrics();

        $fileName = $this->reportService->generateExcelReport($dashboard);

        return response()->download(storage_path("app/reports/{$fileName}"));
    }
}
