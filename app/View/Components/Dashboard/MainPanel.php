<?php

namespace App\View\Components\Dashboard;

use Illuminate\View\Component;
use App\Services\UiOptimizationService;
use App\Services\PerformanceMonitor;
use App\Models\Surgery;
use App\Models\Equipment;
use App\Models\Medico;
use Carbon\Carbon;

class MainPanel extends Component
{
    protected $uiOptimizer;
    protected $performanceMonitor;
    public $stats;
    public $chartData;
    public $alerts;

    public function __construct(
        UiOptimizationService $uiOptimizer,
        PerformanceMonitor $performanceMonitor
    ) {
        $this->uiOptimizer = $uiOptimizer;
        $this->performanceMonitor = $performanceMonitor;
        $this->loadDashboardData();
    }

    protected function loadDashboardData(): void
    {
        $this->stats = $this->getStats();
        $this->chartData = $this->getChartData();
        $this->alerts = $this->getAlerts();
    }

    protected function getStats(): array
    {
        return [
            'surgeries' => [
                'title' => 'Cirugías Pendientes',
                'value' => Surgery::where('status', 'pending')->count(),
                'icon' => 'calendar-alt',
                'color' => 'primary',
                'trend' => $this->calculateTrend('surgeries'),
                'refresh_interval' => 30
            ],
            'equipment' => [
                'title' => 'Equipos Disponibles',
                'value' => Equipment::where('status', 'available')->count(),
                'icon' => 'tools',
                'color' => 'success',
                'trend' => $this->calculateTrend('equipment'),
                'refresh_interval' => 60
            ],
            'maintenance' => [
                'title' => 'Mantenimientos Próximos',
                'value' => Equipment::whereDate('next_maintenance', '<=', Carbon::now()->addDays(7))->count(),
                'icon' => 'wrench',
                'color' => 'warning',
                'trend' => null,
                'refresh_interval' => 300
            ],
            'doctors' => [
                'title' => 'Médicos Activos',
                'value' => Medico::where('estado', 'activo')->count(),
                'icon' => 'user-md',
                'color' => 'info',
                'trend' => $this->calculateTrend('doctors'),
                'refresh_interval' => 600
            ]
        ];
    }

    protected function calculateTrend(string $metric): array
    {
        $now = Carbon::now();
        $lastMonth = $now->copy()->subMonth();

        switch ($metric) {
            case 'surgeries':
                $current = Surgery::whereMonth('created_at', $now->month)->count();
                $previous = Surgery::whereMonth('created_at', $lastMonth->month)->count();
                break;
            case 'equipment':
                $current = Equipment::where('status', 'available')->count();
                $previous = Equipment::where('status', 'available')
                    ->whereMonth('updated_at', $lastMonth->month)
                    ->count();
                break;
            case 'doctors':
                $current = Medico::where('estado', 'activo')->count();
                $previous = Medico::where('estado', 'activo')
                    ->whereMonth('updated_at', $lastMonth->month)
                    ->count();
                break;
            default:
                return ['direction' => 'neutral', 'value' => 0];
        }

        if ($previous == 0) {
            return ['direction' => 'neutral', 'value' => 0];
        }

        $change = (($current - $previous) / $previous) * 100;
        return [
            'direction' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'neutral'),
            'value' => abs(round($change, 1))
        ];
    }

    protected function getChartData(): array
    {
        $dates = collect(range(6, 0))->map(function($days) {
            return Carbon::now()->subDays($days)->format('Y-m-d');
        });

        return [
            'labels' => $dates->map(fn($date) => Carbon::parse($date)->format('d M'))->toArray(),
            'datasets' => [
                [
                    'label' => 'Cirugías',
                    'data' => $dates->map(function($date) {
                        return Surgery::whereDate('created_at', $date)->count();
                    })->toArray(),
                    'borderColor' => '#4e73df',
                    'backgroundColor' => 'rgba(78, 115, 223, 0.1)',
                ],
                [
                    'label' => 'Equipos en Uso',
                    'data' => $dates->map(function($date) {
                        return Equipment::where('status', 'in_use')
                            ->whereDate('updated_at', $date)
                            ->count();
                    })->toArray(),
                    'borderColor' => '#1cc88a',
                    'backgroundColor' => 'rgba(28, 200, 138, 0.1)',
                ]
            ]
        ];
    }

    protected function getAlerts(): array
    {
        return [
            'maintenance' => Equipment::whereDate('next_maintenance', '<=', Carbon::now()->addDays(7))
                ->get()
                ->map(function($equipment) {
                    return [
                        'type' => 'warning',
                        'message' => "Mantenimiento próximo para {$equipment->name}",
                        'date' => $equipment->next_maintenance
                    ];
                }),
            'performance' => $this->performanceMonitor->shouldShowPerformanceAlert() ? [
                [
                    'type' => 'danger',
                    'message' => $this->performanceMonitor->getPerformanceAlertMessage(),
                    'date' => Carbon::now()
                ]
            ] : [],
        ];
    }

    public function render()
    {
        return view('components.dashboard.main-panel', [
            'darkMode' => session('dark_mode', false),
            'animations' => $this->uiOptimizer->getAnimationsConfig(),
            'performance' => $this->performanceMonitor->generatePerformanceReport(),
        ]);
    }
}
