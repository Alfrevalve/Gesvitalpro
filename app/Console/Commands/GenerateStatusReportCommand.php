<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SystemMetric;
use App\Models\PerformanceMetric;
use App\Models\SystemAlert;
use App\Services\SystemMonitoringService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use PDF;

class GenerateStatusReportCommand extends Command
{
    protected $signature = 'system:status-report
                          {--type=full : Type of report (full, summary, health, performance)}
                          {--period=daily : Report period (daily, weekly, monthly)}
                          {--format=pdf : Output format (pdf, html, json)}
                          {--email= : Email address to send the report to}
                          {--store : Store the report in storage}';

    protected $description = 'Generate a system status report';

    protected $monitoringService;

    public function __construct(SystemMonitoringService $monitoringService)
    {
        parent::__construct();
        $this->monitoringService = $monitoringService;
    }

    public function handle()
    {
        $startTime = microtime(true);
        $this->info('Generating system status report...');

        try {
            // Recopilar datos
            $data = $this->collectReportData();

            // Generar reporte
            $report = $this->generateReport($data);

            // Almacenar reporte si se solicita
            if ($this->option('store')) {
                $path = $this->storeReport($report);
                $this->info("Report stored at: {$path}");
            }

            // Enviar por email si se especifica
            if ($email = $this->option('email')) {
                $this->emailReport($report, $email);
                $this->info("Report sent to: {$email}");
            }

            $duration = round(microtime(true) - $startTime, 2);
            $this->info("Report generated successfully in {$duration} seconds");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error generating report: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    protected function collectReportData(): array
    {
        $period = $this->option('period');
        $startDate = $this->getStartDate($period);

        return [
            'generated_at' => now(),
            'period' => $period,
            'type' => $this->option('type'),
            'system_info' => $this->getSystemInfo(),
            'metrics' => $this->getMetrics($startDate),
            'alerts' => $this->getAlerts($startDate),
            'performance' => $this->getPerformanceData($startDate),
            'health_check' => $this->monitoringService->runChecks(),
        ];
    }

    protected function getSystemInfo(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'environment' => config('app.env'),
            'debug_mode' => config('app.debug'),
            'maintenance_mode' => app()->isDownForMaintenance(),
            'server' => [
                'software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'os' => PHP_OS,
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
            ],
            'database' => [
                'driver' => config('database.default'),
                'version' => $this->getDatabaseVersion(),
            ],
        ];
    }

    protected function getMetrics(Carbon $startDate): array
    {
        return [
            'system' => SystemMetric::where('created_at', '>=', $startDate)
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy(function ($metric) {
                    return $metric->created_at->format('Y-m-d');
                }),
            'summary' => $this->calculateMetricsSummary($startDate),
        ];
    }

    protected function getAlerts(Carbon $startDate): array
    {
        $alerts = SystemAlert::where('created_at', '>=', $startDate)->get();

        return [
            'total' => $alerts->count(),
            'critical' => $alerts->where('type', 'critical')->count(),
            'warning' => $alerts->where('type', 'warning')->count(),
            'resolved' => $alerts->where('resolved', true)->count(),
            'unresolved' => $alerts->where('resolved', false)->count(),
            'average_resolution_time' => $this->calculateAverageResolutionTime($alerts),
            'recent' => $alerts->sortByDesc('created_at')->take(10),
        ];
    }

    protected function getPerformanceData(Carbon $startDate): array
    {
        return [
            'response_times' => $this->getAverageResponseTimes($startDate),
            'memory_usage' => $this->getMemoryUsageStats($startDate),
            'database' => $this->getDatabaseStats($startDate),
            'cache' => $this->getCacheStats($startDate),
            'queue' => $this->getQueueStats($startDate),
        ];
    }

    protected function generateReport(array $data): string
    {
        $format = $this->option('format');
        $view = View::make('reports.system-status', $data);

        switch ($format) {
            case 'pdf':
                return PDF::loadHtml($view->render())->output();
            case 'html':
                return $view->render();
            case 'json':
                return json_encode($data, JSON_PRETTY_PRINT);
            default:
                throw new \InvalidArgumentException("Unsupported format: {$format}");
        }
    }

    protected function storeReport(string $content): string
    {
        $filename = sprintf(
            'reports/system-status-%s-%s.%s',
            $this->option('type'),
            now()->format('Y-m-d-His'),
            $this->option('format')
        );

        Storage::put($filename, $content);
        return $filename;
    }

    protected function emailReport(string $report, string $email): void
    {
        Mail::send([], [], function ($message) use ($report, $email) {
            $message->to($email)
                ->subject('System Status Report - ' . now()->format('Y-m-d'))
                ->attachData(
                    $report,
                    'system-status-report.' . $this->option('format'),
                    ['mime' => $this->getContentType()]
                );
        });
    }

    protected function getStartDate(string $period): Carbon
    {
        return match($period) {
            'daily' => now()->subDay(),
            'weekly' => now()->subWeek(),
            'monthly' => now()->subMonth(),
            default => now()->subDay(),
        };
    }

    protected function getContentType(): string
    {
        return match($this->option('format')) {
            'pdf' => 'application/pdf',
            'html' => 'text/html',
            'json' => 'application/json',
            default => 'text/plain',
        };
    }

    protected function getDatabaseVersion(): string
    {
        try {
            return \DB::select('SELECT VERSION() as version')[0]->version;
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    protected function calculateMetricsSummary(Carbon $startDate): array
    {
        // Implementar cálculo de resumen de métricas
        return [];
    }

    protected function calculateAverageResolutionTime($alerts): string
    {
        $resolvedAlerts = $alerts->where('resolved', true);
        
        if ($resolvedAlerts->isEmpty()) {
            return 'N/A';
        }

        $totalMinutes = $resolvedAlerts->sum(function ($alert) {
            return $alert->resolved_at->diffInMinutes($alert->created_at);
        });

        $averageMinutes = $totalMinutes / $resolvedAlerts->count();

        if ($averageMinutes < 60) {
            return round($averageMinutes) . ' minutos';
        }

        return round($averageMinutes / 60, 1) . ' horas';
    }

    protected function getAverageResponseTimes(Carbon $startDate): array
    {
        // Implementar cálculo de tiempos de respuesta promedio
        return [];
    }

    protected function getMemoryUsageStats(Carbon $startDate): array
    {
        // Implementar estadísticas de uso de memoria
        return [];
    }

    protected function getDatabaseStats(Carbon $startDate): array
    {
        // Implementar estadísticas de base de datos
        return [];
    }

    protected function getCacheStats(Carbon $startDate): array
    {
        // Implementar estadísticas de caché
        return [];
    }

    protected function getQueueStats(Carbon $startDate): array
    {
        // Implementar estadísticas de cola
        return [];
    }
}
