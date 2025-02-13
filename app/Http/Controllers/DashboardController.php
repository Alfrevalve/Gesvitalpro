<?php

namespace App\Http\Controllers;

use App\Services\EstadisticasService;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected $estadisticasService;
    protected $auditService;

    public function __construct(EstadisticasService $estadisticasService, AuditService $auditService)
    {
        $this->estadisticasService = $estadisticasService;
        $this->auditService = $auditService;
    }

    /**
     * Mostrar el dashboard principal
     */
    public function index()
    {
        // Cachear las estadísticas por 1 hora
        $estadisticas = Cache::remember('dashboard_stats', 3600, function () {
            return $this->estadisticasService->getEstadisticasGenerales();
        });

        // Obtener estadísticas de cirugías del mes actual
        $estadisticasCirugias = $this->estadisticasService->getEstadisticasCirugias('mes');

        // Análisis de inventario crítico
        $inventarioCritico = $this->estadisticasService->getAnalisisInventario()['productos_criticos'];

        // Predicciones de demanda
        $predicciones = $this->estadisticasService->getPredicionesDemanda();

        return view('dashboard.index', compact(
            'estadisticas',
            'estadisticasCirugias',
            'inventarioCritico',
            'predicciones'
        ));
    }

    /**
     * Obtener datos para gráficos vía AJAX
     */
    public function getChartData(Request $request)
    {
        $tipo = $request->input('tipo', 'cirugias');
        $periodo = $request->input('periodo', 'mes');

        switch ($tipo) {
            case 'cirugias':
                return $this->getCirugiasChartData($periodo);
            case 'inventario':
                return $this->getInventarioChartData();
            case 'pacientes':
                return $this->getPacientesChartData($periodo);
            default:
                return response()->json(['error' => 'Tipo de gráfico no válido'], 400);
        }
    }

    /**
     * Datos para el gráfico de cirugías
     */
    protected function getCirugiasChartData(string $periodo)
    {
        $stats = $this->estadisticasService->getEstadisticasCirugias($periodo);

        return response()->json([
            'labels' => array_keys($stats['por_estado']),
            'datasets' => [
                [
                    'label' => 'Cirugías por Estado',
                    'data' => array_values($stats['por_estado']),
                    'backgroundColor' => [
                        '#4CAF50', // Completadas
                        '#FFC107', // En proceso
                        '#2196F3', // Programadas
                        '#F44336', // Canceladas
                    ],
                ]
            ],
            'total' => $stats['total'],
            'tiempo_promedio' => round($stats['tiempo_promedio'], 2),
            'costo_total' => number_format($stats['costo_total'], 2),
        ]);
    }

    /**
     * Datos para el gráfico de inventario
     */
    protected function getInventarioChartData()
    {
        $analisis = $this->estadisticasService->getAnalisisInventario();

        return response()->json([
            'valorPorCategoria' => [
                'labels' => array_keys($analisis['valor_inventario']['por_categoria']),
                'datasets' => [
                    [
                        'label' => 'Valor del Inventario por Categoría',
                        'data' => array_values($analisis['valor_inventario']['por_categoria']),
                        'backgroundColor' => $this->getColorPalette(count($analisis['valor_inventario']['por_categoria'])),
                    ]
                ]
            ],
            'productosCriticos' => $analisis['productos_criticos'],
            'rotacion' => $analisis['rotacion'],
        ]);
    }

    /**
     * Datos para el gráfico de pacientes
     */
    protected function getPacientesChartData(string $periodo)
    {
        $inicio = $this->getPeriodoInicio($periodo);
        $datos = Cache::remember("pacientes_chart_{$periodo}", 3600, function () use ($inicio) {
            return \App\Models\Paciente::where('created_at', '>=', $inicio)
                ->selectRaw('DATE(created_at) as fecha, COUNT(*) as total')
                ->groupBy('fecha')
                ->get()
                ->pluck('total', 'fecha')
                ->toArray();
        });

        return response()->json([
            'labels' => array_keys($datos),
            'datasets' => [
                [
                    'label' => 'Nuevos Pacientes',
                    'data' => array_values($datos),
                    'borderColor' => '#2196F3',
                    'fill' => false,
                ]
            ]
        ]);
    }

    /**
     * Obtener fecha de inicio según período
     */
    protected function getPeriodoInicio(string $periodo): Carbon
    {
        switch ($periodo) {
            case 'semana':
                return Carbon::now()->subWeek();
            case 'mes':
                return Carbon::now()->subMonth();
            case 'año':
                return Carbon::now()->subYear();
            default:
                return Carbon::now()->subMonth();
        }
    }

    /**
     * Generar paleta de colores
     */
    protected function getColorPalette(int $count): array
    {
        $colors = [
            '#4CAF50', '#2196F3', '#FFC107', '#F44336',
            '#9C27B0', '#00BCD4', '#FF9800', '#795548',
            '#607D8B', '#E91E63', '#3F51B5', '#009688'
        ];

        $palette = [];
        for ($i = 0; $i < $count; $i++) {
            $palette[] = $colors[$i % count($colors)];
        }

        return $palette;
    }

    /**
     * Exportar datos del dashboard
     */
    public function exportar(Request $request)
    {
        $tipo = $request->input('tipo', 'general');
        $formato = $request->input('formato', 'pdf');

        $datos = [
            'estadisticas' => $this->estadisticasService->getEstadisticasGenerales(),
            'cirugias' => $this->estadisticasService->getEstadisticasCirugias('mes'),
            'inventario' => $this->estadisticasService->getAnalisisInventario(),
            'predicciones' => $this->estadisticasService->getPredicionesDemanda(),
        ];

        // Registrar la exportación en la auditoría
        $this->auditService->log(
            'exportar_dashboard',
            'Dashboard',
            0,
            ['tipo' => $tipo, 'formato' => $formato]
        );

        switch ($formato) {
            case 'pdf':
                return $this->exportarPDF($datos, $tipo);
            case 'excel':
                return $this->exportarExcel($datos, $tipo);
            case 'csv':
                return $this->exportarCSV($datos, $tipo);
            default:
                return back()->with('error', 'Formato de exportación no válido');
        }
    }

    /**
     * Exportar a PDF
     */
    protected function exportarPDF(array $datos, string $tipo)
    {
        $pdf = \PDF::loadView('exports.dashboard', [
            'datos' => $datos,
            'tipo' => $tipo,
            'fecha' => Carbon::now()->format('d/m/Y H:i')
        ]);

        return $pdf->download('dashboard_' . $tipo . '_' . Carbon::now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Exportar a Excel
     */
    protected function exportarExcel(array $datos, string $tipo)
    {
        return \Excel::download(
            new \App\Exports\DashboardExport($datos, $tipo),
            'dashboard_' . $tipo . '_' . Carbon::now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Exportar a CSV
     */
    protected function exportarCSV(array $datos, string $tipo)
    {
        return \Excel::download(
            new \App\Exports\DashboardExport($datos, $tipo),
            'dashboard_' . $tipo . '_' . Carbon::now()->format('Y-m-d') . '.csv'
        );
    }
}
