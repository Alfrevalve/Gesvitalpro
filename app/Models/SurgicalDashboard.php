<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;

class SurgicalDashboard extends Model
{
    use HasFactory;

    /**
     * Tipos de dashboard
     */
    public const DASHBOARD_EXECUTIVE = 'executive';
    public const DASHBOARD_OPERATIONAL = 'operational';
    public const DASHBOARD_ANALYTICAL = 'analytical';
    public const DASHBOARD_TACTICAL = 'tactical';

    /**
     * Períodos de tiempo predefinidos
     */
    public const PERIOD_TODAY = 'today';
    public const PERIOD_WEEK = 'week';
    public const PERIOD_MONTH = 'month';
    public const PERIOD_QUARTER = 'quarter';
    public const PERIOD_YEAR = 'year';
    public const PERIOD_CUSTOM = 'custom';

    protected $fillable = [
        'dashboard_type',
        'user_id',
        'preferences',
        'last_viewed_at',
    ];

    protected $casts = [
        'preferences' => 'array',
        'last_viewed_at' => 'datetime',
    ];

    /**
     * Obtener datos del dashboard ejecutivo
     */
    public function getExecutiveDashboard(string $period = self::PERIOD_MONTH): array
    {
        $dateRange = $this->getDateRange($period);
        $metrics = SurgicalProcessMetrics::generateMetrics($dateRange['start'], $dateRange['end']);

        return [
            'summary_cards' => $this->getExecutiveSummaryCards($metrics),
            'trend_charts' => $this->getExecutiveTrendCharts($metrics),
            'performance_indicators' => $this->getExecutivePerformanceIndicators($metrics),
            'alerts' => $this->getExecutiveAlerts($metrics),
        ];
    }

    /**
     * Obtener datos del dashboard operacional
     */
    public function getOperationalDashboard(string $period = self::PERIOD_WEEK): array
    {
        $dateRange = $this->getDateRange($period);
        $metrics = SurgicalProcessMetrics::generateMetrics($dateRange['start'], $dateRange['end']);

        return [
            'current_status' => $this->getCurrentOperationalStatus(),
            'process_timeline' => $this->getProcessTimeline(),
            'workload_distribution' => $this->getWorkloadDistribution(),
            'resource_utilization' => $this->getResourceUtilization(),
            'pending_tasks' => $this->getPendingTasks(),
            'upcoming_schedule' => $this->getUpcomingSchedule(),
        ];
    }

    /**
     * Obtener datos del dashboard analítico
     */
    public function getAnalyticalDashboard(string $period = self::PERIOD_QUARTER): array
    {
        $dateRange = $this->getDateRange($period);
        $metrics = SurgicalProcessMetrics::generateMetrics($dateRange['start'], $dateRange['end']);

        return [
            'detailed_metrics' => $this->getDetailedMetrics($metrics),
            'comparative_analysis' => $this->getComparativeAnalysis($metrics),
            'performance_breakdown' => $this->getPerformanceBreakdown($metrics),
            'trend_analysis' => $this->getTrendAnalysis($metrics),
            'correlation_data' => $this->getCorrelationData($metrics),
        ];
    }

    /**
     * Obtener datos del dashboard táctico
     */
    public function getTacticalDashboard(string $period = self::PERIOD_MONTH): array
    {
        $dateRange = $this->getDateRange($period);
        $metrics = SurgicalProcessMetrics::generateMetrics($dateRange['start'], $dateRange['end']);

        return [
            'resource_planning' => $this->getResourcePlanning(),
            'capacity_analysis' => $this->getCapacityAnalysis(),
            'efficiency_metrics' => $this->getEfficiencyMetrics($metrics),
            'quality_indicators' => $this->getQualityIndicators($metrics),
            'improvement_opportunities' => $this->getImprovementOpportunities($metrics),
        ];
    }

    /**
     * Componentes del Dashboard Ejecutivo
     */
    protected function getExecutiveSummaryCards(SurgicalProcessMetrics $metrics): array
    {
        return [
            'total_processes' => [
                'value' => $metrics->metrics_data['summary']['total_processes'],
                'trend' => $this->calculateTrend('total_processes'),
                'status' => 'positive',
            ],
            'success_rate' => [
                'value' => $metrics->metrics_data['quality']['surgery_success_rate'],
                'trend' => $this->calculateTrend('surgery_success_rate'),
                'status' => $this->getStatusColor($metrics->metrics_data['quality']['surgery_success_rate'], 95),
            ],
            'revenue_impact' => [
                'value' => $this->calculateRevenueImpact($metrics),
                'trend' => $this->calculateTrend('revenue'),
                'status' => 'positive',
            ],
            'customer_satisfaction' => [
                'value' => $metrics->metrics_data['service']['customer_satisfaction_rate'],
                'trend' => $this->calculateTrend('customer_satisfaction'),
                'status' => $this->getStatusColor($metrics->metrics_data['service']['customer_satisfaction_rate'], 90),
            ],
        ];
    }

    /**
     * Componentes del Dashboard Operacional
     */
    protected function getCurrentOperationalStatus(): array
    {
        $processes = SurgicalProcess::with(['surgery', 'currentResponsible'])
            ->where('estado', '!=', SurgicalProcess::STATUS_COMPLETED)
            ->where('estado', '!=', SurgicalProcess::STATUS_CANCELLED)
            ->get();

        return [
            'active_processes' => $processes->map(function ($process) {
                return [
                    'id' => $process->id,
                    'current_stage' => $process->estado,
                    'progress' => $process->getProgress(),
                    'responsible' => $process->currentResponsible->name,
                    'time_in_stage' => $this->getTimeInStage($process),
                    'is_delayed' => $process->isDelayed(),
                    'next_action' => $this->getNextAction($process),
                ];
            })->toArray(),
            'stage_summary' => $this->getStageSummary($processes),
            'bottlenecks' => $this->identifyBottlenecks($processes),
            'staff_availability' => $this->getStaffAvailability(),
        ];
    }

    /**
     * Componentes del Dashboard Analítico
     */
    protected function getDetailedMetrics(SurgicalProcessMetrics $metrics): array
    {
        return [
            'process_efficiency' => [
                'time_metrics' => $this->getTimeMetrics($metrics),
                'resource_utilization' => $this->getResourceUtilizationMetrics($metrics),
                'cost_metrics' => $this->getCostMetrics($metrics),
            ],
            'quality_metrics' => [
                'success_rates' => $this->getSuccessRates($metrics),
                'error_rates' => $this->getErrorRates($metrics),
                'compliance_metrics' => $this->getComplianceMetrics($metrics),
            ],
            'service_metrics' => [
                'satisfaction_scores' => $this->getSatisfactionScores($metrics),
                'response_times' => $this->getResponseTimes($metrics),
                'service_issues' => $this->getServiceIssues($metrics),
            ],
        ];
    }

    /**
     * Componentes del Dashboard Táctico
     */
    protected function getResourcePlanning(): array
    {
        return [
            'staff_allocation' => $this->getStaffAllocation(),
            'equipment_availability' => $this->getEquipmentAvailability(),
            'material_inventory' => $this->getMaterialInventory(),
            'capacity_forecast' => $this->getCapacityForecast(),
        ];
    }

    /**
     * Métodos de utilidad para visualizaciones
     */
    protected function getTimeMetrics(SurgicalProcessMetrics $metrics): array
    {
        return [
            'average_duration' => [
                'value' => $metrics->metrics_data['efficiency']['avg_process_duration'],
                'trend' => $this->calculateTrend('avg_process_duration'),
                'breakdown' => $this->getDurationBreakdown($metrics),
            ],
            'stage_durations' => $metrics->metrics_data['efficiency']['stage_durations'],
            'delay_analysis' => [
                'frequency' => $this->getDelayFrequency($metrics),
                'impact' => $this->getDelayImpact($metrics),
                'patterns' => $this->getDelayPatterns($metrics),
            ],
        ];
    }

    /**
     * Visualizaciones específicas
     */
    protected function generateProcessTimeline(Collection $processes): array
    {
        return $processes->map(function ($process) {
            $timeline = [];
            foreach ($process->statusLogs as $log) {
                $timeline[] = [
                    'stage' => $log->new_state,
                    'start_time' => $log->created_at,
                    'duration' => $log->duration_minutes,
                    'responsible' => $log->user->name,
                    'status' => $this->getStageStatus($log),
                ];
            }
            return [
                'process_id' => $process->id,
                'stages' => $timeline,
                'total_duration' => $process->getDuration(),
                'current_stage' => $process->estado,
            ];
        })->toArray();
    }

    protected function generatePerformanceChart(array $metrics): array
    {
        return [
            'labels' => collect($metrics['efficiency']['stage_durations'])->keys(),
            'datasets' => [
                [
                    'label' => 'Duración Promedio',
                    'data' => collect($metrics['efficiency']['stage_durations'])
                        ->pluck('avg_duration')
                        ->toArray(),
                    'backgroundColor' => $this->getChartColors(),
                ],
                [
                    'label' => 'Objetivo',
                    'data' => $this->getTargetDurations(),
                    'type' => 'line',
                ],
            ],
        ];
    }

    protected function generateResourceUtilizationChart(array $metrics): array
    {
        return [
            'equipment' => [
                'labels' => ['Disponible', 'En Uso', 'Mantenimiento'],
                'data' => [
                    $metrics['productivity']['equipment_utilization_rate'],
                    100 - $metrics['productivity']['equipment_utilization_rate'],
                    $metrics['quality']['equipment_maintenance_rate'],
                ],
            ],
            'staff' => [
                'labels' => collect($metrics['productivity']['staff_performance'])
                    ->keys()
                    ->toArray(),
                'data' => collect($metrics['productivity']['staff_performance'])
                    ->pluck('utilization_rate')
                    ->toArray(),
            ],
        ];
    }

    /**
     * Métodos de formato y presentación
     */
    protected function formatDuration(int $minutes): string
    {
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        if ($hours > 0) {
            return "{$hours}h {$remainingMinutes}m";
        }

        return "{$remainingMinutes}m";
    }

    protected function getStatusColor(float $value, float $threshold): string
    {
        if ($value >= $threshold) {
            return 'success';
        } elseif ($value >= $threshold * 0.8) {
            return 'warning';
        }
        return 'danger';
    }

    protected function getChartColors(): array
    {
        return [
            '#4CAF50', // success
            '#FFC107', // warning
            '#F44336', // danger
            '#2196F3', // info
            '#9C27B0', // purple
            '#FF9800', // orange
        ];
    }

    /**
     * Métodos de cálculo de tendencias
     */
    protected function calculateTrend(string $metric, int $periods = 3): array
    {
        $history = $this->getMetricHistory($metric, $periods);

        return [
            'direction' => $this->getTrendDirection($history),
            'percentage' => $this->getTrendPercentage($history),
            'history' => $history,
        ];
    }

    protected function getTrendDirection(array $history): string
    {
        $last = end($history);
        $first = reset($history);

        if ($last > $first) {
            return 'up';
        } elseif ($last < $first) {
            return 'down';
        }

        return 'stable';
    }

    protected function getTrendPercentage(array $history): float
    {
        if (empty($history)) {
            return 0;
        }

        $last = end($history);
        $first = reset($history);

        if ($first == 0) {
            return 0;
        }

        return round((($last - $first) / $first) * 100, 2);
    }

    /**
     * Métodos de exportación
     */
    public function exportDashboard(string $type, string $format = 'pdf'): string
    {
        $data = match($type) {
            self::DASHBOARD_EXECUTIVE => $this->getExecutiveDashboard(),
            self::DASHBOARD_OPERATIONAL => $this->getOperationalDashboard(),
            self::DASHBOARD_ANALYTICAL => $this->getAnalyticalDashboard(),
            self::DASHBOARD_TACTICAL => $this->getTacticalDashboard(),
            default => throw new \InvalidArgumentException("Invalid dashboard type: {$type}"),
        };

        return match($format) {
            'pdf' => $this->exportToPDF($data),
            'excel' => $this->exportToExcel($data),
            'json' => json_encode($data, JSON_PRETTY_PRINT),
            default => throw new \InvalidArgumentException("Invalid export format: {$format}"),
        };
    }
}
