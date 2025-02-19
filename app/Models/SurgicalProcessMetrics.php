<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;

class SurgicalProcessMetrics extends Model
{
    use HasFactory;

    protected $fillable = [
        'period_start',
        'period_end',
        'metrics_data',
        'generated_by',
        'is_snapshot',
    ];

    protected $casts = [
        'period_start' => 'datetime',
        'period_end' => 'datetime',
        'metrics_data' => 'array',
        'is_snapshot' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * KPIs principales
     */
    public const KPI_CATEGORIES = [
        'efficiency' => [
            'avg_process_duration',
            'on_time_completion_rate',
            'delay_rate',
            'cancellation_rate',
        ],
        'quality' => [
            'material_preparation_accuracy',
            'surgery_success_rate',
            'equipment_return_condition',
            'documentation_completion_rate',
        ],
        'productivity' => [
            'processes_per_period',
            'surgeries_per_staff',
            'equipment_utilization_rate',
            'storage_turnover_rate',
        ],
        'service' => [
            'visit_to_surgery_conversion',
            'customer_satisfaction_rate',
            'complaint_resolution_time',
            'scheduling_accuracy',
        ],
    ];

    /**
     * Objetivos por KPI
     */
    public const KPI_TARGETS = [
        'avg_process_duration' => 14, // días
        'on_time_completion_rate' => 90, // porcentaje
        'delay_rate' => 10, // porcentaje
        'cancellation_rate' => 5, // porcentaje
        'material_preparation_accuracy' => 98, // porcentaje
        'surgery_success_rate' => 95, // porcentaje
        'equipment_return_condition' => 90, // porcentaje
        'documentation_completion_rate' => 100, // porcentaje
        'processes_per_period' => 50, // cantidad
        'surgeries_per_staff' => 10, // cantidad
        'equipment_utilization_rate' => 80, // porcentaje
        'storage_turnover_rate' => 5, // veces
        'visit_to_surgery_conversion' => 70, // porcentaje
        'customer_satisfaction_rate' => 90, // porcentaje
        'complaint_resolution_time' => 48, // horas
        'scheduling_accuracy' => 95, // porcentaje
    ];

    /**
     * Generar métricas para un período específico
     */
    public static function generateMetrics(Carbon $start, Carbon $end, bool $saveSnapshot = false): self
    {
        $metrics = new self();
        $metrics->period_start = $start;
        $metrics->period_end = $end;
        $metrics->is_snapshot = $saveSnapshot;
        $metrics->generated_by = auth()->id();

        $processes = SurgicalProcess::whereBetween('created_at', [$start, $end])->get();
        $metrics->metrics_data = $metrics->calculateMetrics($processes);

        if ($saveSnapshot) {
            $metrics->save();
        }

        return $metrics;
    }

    /**
     * Calcular todas las métricas
     */
    protected function calculateMetrics(Collection $processes): array
    {
        return [
            'efficiency' => $this->calculateEfficiencyMetrics($processes),
            'quality' => $this->calculateQualityMetrics($processes),
            'productivity' => $this->calculateProductivityMetrics($processes),
            'service' => $this->calculateServiceMetrics($processes),
            'summary' => $this->calculateSummaryMetrics($processes),
        ];
    }

    /**
     * Métricas de eficiencia
     */
    protected function calculateEfficiencyMetrics(Collection $processes): array
    {
        $completed = $processes->where('estado', SurgicalProcess::STATUS_COMPLETED);
        $totalCompleted = $completed->count();

        return [
            'avg_process_duration' => $completed->average(function ($process) {
                return $process->getDuration() ?? 0;
            }),
            'on_time_completion_rate' => $totalCompleted > 0 ?
                ($completed->filter(fn($p) => !$p->isDelayed())->count() / $totalCompleted) * 100 : 0,
            'delay_rate' => $processes->filter->isDelayed()->count() / max($processes->count(), 1) * 100,
            'cancellation_rate' => $processes->where('estado', SurgicalProcess::STATUS_CANCELLED)->count()
                / max($processes->count(), 1) * 100,
            'stage_durations' => $this->calculateStageDurations($processes),
        ];
    }

    /**
     * Métricas de calidad
     */
    protected function calculateQualityMetrics(Collection $processes): array
    {
        $completedProcesses = $processes->where('estado', SurgicalProcess::STATUS_COMPLETED);

        return [
            'material_preparation_accuracy' => $this->calculateMaterialAccuracy($processes),
            'surgery_success_rate' => $this->calculateSurgerySuccessRate($processes),
            'equipment_return_condition' => $this->calculateEquipmentReturnRate($processes),
            'documentation_completion_rate' => $this->calculateDocumentationRate($processes),
            'quality_issues' => $this->analyzeQualityIssues($processes),
        ];
    }

    /**
     * Métricas de productividad
     */
    protected function calculateProductivityMetrics(Collection $processes): array
    {
        return [
            'processes_per_period' => $processes->count(),
            'surgeries_per_staff' => $this->calculateSurgeriesPerStaff($processes),
            'equipment_utilization_rate' => $this->calculateEquipmentUtilization($processes),
            'storage_turnover_rate' => $this->calculateStorageTurnover($processes),
            'staff_performance' => $this->analyzeStaffPerformance($processes),
        ];
    }

    /**
     * Métricas de servicio
     */
    protected function calculateServiceMetrics(Collection $processes): array
    {
        return [
            'visit_to_surgery_conversion' => $this->calculateConversionRate($processes),
            'customer_satisfaction_rate' => $this->calculateSatisfactionRate($processes),
            'complaint_resolution_time' => $this->calculateResolutionTime($processes),
            'scheduling_accuracy' => $this->calculateSchedulingAccuracy($processes),
            'service_issues' => $this->analyzeServiceIssues($processes),
        ];
    }

    /**
     * Métricas de resumen
     */
    protected function calculateSummaryMetrics(Collection $processes): array
    {
        return [
            'total_processes' => $processes->count(),
            'completed_processes' => $processes->where('estado', SurgicalProcess::STATUS_COMPLETED)->count(),
            'cancelled_processes' => $processes->where('estado', SurgicalProcess::STATUS_CANCELLED)->count(),
            'in_progress_processes' => $processes->whereNotIn('estado', [
                SurgicalProcess::STATUS_COMPLETED,
                SurgicalProcess::STATUS_CANCELLED
            ])->count(),
            'delayed_processes' => $processes->filter->isDelayed()->count(),
            'avg_completion_time' => $this->calculateAverageCompletionTime($processes),
            'top_delays' => $this->analyzeTopDelays($processes),
            'improvement_areas' => $this->identifyImprovementAreas($processes),
        ];
    }

    /**
     * Métodos de análisis específicos
     */
    protected function calculateStageDurations(Collection $processes): array
    {
        $logs = SurgicalProcessLog::whereIn('surgical_process_id', $processes->pluck('id'))
            ->get()
            ->groupBy('new_state');

        return collect(SurgicalProcess::$stateResponsibilities)
            ->mapWithKeys(function ($role, $state) use ($logs) {
                $stateLogs = $logs->get($state, collect());
                return [$state => [
                    'avg_duration' => $stateLogs->average('duration_minutes'),
                    'min_duration' => $stateLogs->min('duration_minutes'),
                    'max_duration' => $stateLogs->max('duration_minutes'),
                    'total_occurrences' => $stateLogs->count(),
                ]];
            })
            ->toArray();
    }

    protected function calculateMaterialAccuracy(Collection $processes): float
    {
        $storageProcesses = StorageProcess::whereIn('surgery_request_id',
            $processes->pluck('surgery.surgery_request_id')
        )->get();

        $accurate = $storageProcesses->where('quality_check_passed', true)->count();
        return $storageProcesses->count() > 0 ?
            ($accurate / $storageProcesses->count()) * 100 : 0;
    }

    protected function calculateSurgerySuccessRate(Collection $processes): float
    {
        $completedSurgeries = $processes->filter(function ($process) {
            return $process->surgery && $process->surgery->status === 'completed';
        });

        return $processes->count() > 0 ?
            ($completedSurgeries->count() / $processes->count()) * 100 : 0;
    }

    /**
     * Análisis de tendencias
     */
    public function analyzeTrends(int $previousPeriods = 3): array
    {
        $trends = [];

        foreach (self::KPI_CATEGORIES as $category => $kpis) {
            foreach ($kpis as $kpi) {
                $historicalData = $this->getHistoricalData($kpi, $previousPeriods);
                $trends[$category][$kpi] = [
                    'current' => $this->metrics_data[$category][$kpi] ?? 0,
                    'previous' => $historicalData,
                    'trend' => $this->calculateTrend($historicalData),
                    'target' => self::KPI_TARGETS[$kpi] ?? null,
                    'status' => $this->getKPIStatus($kpi, $this->metrics_data[$category][$kpi] ?? 0),
                ];
            }
        }

        return $trends;
    }

    /**
     * Generar reporte detallado
     */
    public function generateDetailedReport(): array
    {
        return [
            'period' => [
                'start' => $this->period_start->format('Y-m-d'),
                'end' => $this->period_end->format('Y-m-d'),
                'duration_days' => $this->period_start->diffInDays($this->period_end),
            ],
            'metrics' => $this->metrics_data,
            'trends' => $this->analyzeTrends(),
            'performance' => [
                'overall_score' => $this->calculateOverallScore(),
                'improvement_areas' => $this->identifyImprovementAreas(),
                'recommendations' => $this->generateRecommendations(),
            ],
            'comparisons' => [
                'vs_targets' => $this->compareWithTargets(),
                'vs_previous' => $this->compareWithPreviousPeriod(),
            ],
            'details' => [
                'by_staff' => $this->getDetailsByStaff(),
                'by_institution' => $this->getDetailsByInstitution(),
                'by_procedure' => $this->getDetailsByProcedure(),
            ],
        ];
    }

    /**
     * Métodos de utilidad para reportes
     */
    protected function calculateOverallScore(): float
    {
        $weights = [
            'efficiency' => 0.3,
            'quality' => 0.3,
            'productivity' => 0.2,
            'service' => 0.2,
        ];

        $scores = [];
        foreach ($weights as $category => $weight) {
            $categoryMetrics = $this->metrics_data[$category] ?? [];
            $categoryScore = collect($categoryMetrics)
                ->filter(fn($value) => is_numeric($value))
                ->average();
            $scores[$category] = $categoryScore * $weight;
        }

        return array_sum($scores);
    }

    protected function identifyImprovementAreas(): array
    {
        $improvements = [];
        foreach (self::KPI_CATEGORIES as $category => $kpis) {
            foreach ($kpis as $kpi) {
                $current = $this->metrics_data[$category][$kpi] ?? 0;
                $target = self::KPI_TARGETS[$kpi] ?? null;

                if ($target && $current < $target) {
                    $improvements[] = [
                        'kpi' => $kpi,
                        'category' => $category,
                        'current' => $current,
                        'target' => $target,
                        'gap' => $target - $current,
                        'priority' => $this->calculateImprovementPriority($kpi, $current, $target),
                    ];
                }
            }
        }

        return collect($improvements)
            ->sortByDesc('priority')
            ->values()
            ->toArray();
    }

    protected function generateRecommendations(): array
    {
        $recommendations = [];
        $improvements = $this->identifyImprovementAreas();

        foreach ($improvements as $improvement) {
            $recommendations[] = [
                'kpi' => $improvement['kpi'],
                'recommendation' => $this->getRecommendationForKPI(
                    $improvement['kpi'],
                    $improvement['current'],
                    $improvement['target']
                ),
                'priority' => $improvement['priority'],
                'expected_impact' => $this->calculateExpectedImpact($improvement),
            ];
        }

        return $recommendations;
    }

    /**
     * Métodos de comparación
     */
    protected function compareWithTargets(): array
    {
        $comparisons = [];
        foreach (self::KPI_TARGETS as $kpi => $target) {
            foreach ($this->metrics_data as $category => $metrics) {
                if (isset($metrics[$kpi])) {
                    $current = $metrics[$kpi];
                    $comparisons[$kpi] = [
                        'current' => $current,
                        'target' => $target,
                        'difference' => $current - $target,
                        'achievement_rate' => ($current / $target) * 100,
                        'status' => $this->getKPIStatus($kpi, $current),
                    ];
                }
            }
        }
        return $comparisons;
    }

    protected function compareWithPreviousPeriod(): array
    {
        $previousPeriodStart = $this->period_start->copy()->subDays($this->period_start->diffInDays($this->period_end));
        $previousMetrics = self::generateMetrics($previousPeriodStart, $this->period_start);

        $comparisons = [];
        foreach ($this->metrics_data as $category => $metrics) {
            foreach ($metrics as $key => $value) {
                if (is_numeric($value)) {
                    $previous = $previousMetrics->metrics_data[$category][$key] ?? 0;
                    $comparisons["{$category}.{$key}"] = [
                        'current' => $value,
                        'previous' => $previous,
                        'change' => $value - $previous,
                        'change_percentage' => $previous != 0 ? (($value - $previous) / $previous) * 100 : 0,
                    ];
                }
            }
        }
        return $comparisons;
    }

    /**
     * Métodos de desglose
     */
    protected function getDetailsByStaff(): array
    {
        return User::whereIn('id', function($query) {
            $query->select('current_responsible_id')
                ->from('surgical_processes')
                ->whereBetween('created_at', [$this->period_start, $this->period_end])
                ->distinct();
        })->get()->map(function($user) {
            return [
                'staff' => $user->name,
                'role' => $user->roles->first()?->name,
                'metrics' => $this->calculateStaffMetrics($user),
            ];
        })->toArray();
    }

    protected function getDetailsByInstitution(): array
    {
        return Institucion::whereIn('id', function($query) {
            $query->select('institucion_id')
                ->from('surgeries')
                ->whereIn('id', function($subquery) {
                    $subquery->select('surgery_id')
                        ->from('surgical_processes')
                        ->whereBetween('created_at', [$this->period_start, $this->period_end]);
                });
        })->get()->map(function($institucion) {
            return [
                'institution' => $institucion->nombre,
                'metrics' => $this->calculateInstitutionMetrics($institucion),
            ];
        })->toArray();
    }

    protected function getDetailsByProcedure(): array
    {
        return Surgery::whereIn('id', function($query) {
            $query->select('surgery_id')
                ->from('surgical_processes')
                ->whereBetween('created_at', [$this->period_start, $this->period_end]);
        })->get()->groupBy('surgery_type')->map(function($surgeries, $type) {
            return [
                'procedure_type' => $type,
                'metrics' => $this->calculateProcedureMetrics($surgeries),
            ];
        })->toArray();
    }
}
