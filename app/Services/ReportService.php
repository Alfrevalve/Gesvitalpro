<?php

namespace App\Services;

use App\Models\SurgeryMetrics;
use App\Models\SurgicalDashboard;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SurgeryMetricsExport;
use Dompdf\Dompdf;
use Dompdf\Options;

class ReportService
{
    /**
     * Generar reporte en PDF
     */
    public function generatePDFReport(SurgicalDashboard $dashboard): string
    {
        $metrics = $dashboard->getMetrics();
        $html = view('reports.surgery_metrics', compact('metrics'))->render();

        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $fileName = 'surgery_metrics_' . now()->format('Ymd_His') . '.pdf';
        Storage::put('reports/' . $fileName, $dompdf->output());

        return $fileName;
    }

    /**
     * Generar reporte en Excel
     */
    public function generateExcelReport(SurgicalDashboard $dashboard): string
    {
        $metrics = $dashboard->getMetrics();
        $fileName = 'surgery_metrics_' . now()->format('Ymd_His') . '.xlsx';

        Excel::store(new SurgeryMetricsExport($metrics), 'reports/' . $fileName);

        return $fileName;
    }
}
