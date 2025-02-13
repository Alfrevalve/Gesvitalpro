<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\BrokenLink;
use App\Models\LinkCheckHistory;
use Carbon\Carbon;

class GenerateSystemReportCommand extends Command
{
    protected $signature = 'system:report
                          {--type=full : Type of report (full, summary, health, performance)}
                          {--format=html : Output format (html, json, csv)}
                          {--period=daily : Report period (daily, weekly, monthly)}
                          {--email= : Email address to send the report to}';

    protected $description = 'Generate system health and performance report';

    public function handle()
    {
        $startTime = microtime(true);
        $this->info('Generating system report...');

        try {
            $reportType = $this->option('type');
            $format = $this->option('format');
            $period = $this->option('period');

            // Recopilar datos del sistema
            $data = $this->collectSystemData($reportType, $period);

            // Generar el reporte en el formato solicitado
            $report = $this->generateReport($data, $format);

            // Guardar el reporte
            $filename = $this->saveReport($report, $format);

            // Enviar por email si se especificó
            if ($email = $this->option('email')) {
                $this->emailReport($filename, $email);
            }

            $duration = round(microtime(true) - $startTime, 2);
            $this->info("Report generated successfully in {$duration} seconds: {$filename}");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to generate report: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    protected function collectSystemData(string $type, string $period): array
    {
        $startDate = $this->getStartDate($period);
        $data = [
            'generated_at' => now()->toIso8601String(),
            'period' => $period,
            'type' => $type,
        ];

        switch ($type) {
            case 'full':
                $data = array_merge($data, [
                    'system' => $this->getSystemInfo(),
                    'health' => $this->getHealthMetrics(),
                    'performance' => $this->getPerformanceMetrics($startDate),
                    'security' => $this->getSecurityMetrics($startDate),
                    'usage' => $this->getUsageMetrics($startDate),
                ]);
                break;

            case 'summary':
                $data = array_merge($data, [
                    'health_summary' => $this->getHealthSummary(),
                    'performance_summary' => $this->getPerformanceSummary($startDate),
                    'alerts' => $this->getRecentAlerts($startDate),
                ]);
                break;

            case 'health':
                $data['health'] = $this->getHealthMetrics();
                break;

            case 'performance':
                $data['performance'] = $this->getPerformanceMetrics($startDate);
                break;
        }

        return $data;
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
                'version' => DB::select('SELECT VERSION() as version')[0]->version ?? 'Unknown',
            ],
        ];
    }

    protected function getHealthMetrics(): array
    {
        return [
            'disk_usage' => $this->getDiskUsage(),
            'database_size' => $this->getDatabaseSize(),
            'cache_status' => $this->getCacheStatus(),
            'queue_status' => $this->getQueueStatus(),
            'recent_errors' => $this->getRecentErrors(),
            'broken_links' => $this->getBrokenLinksStats(),
        ];
    }

    protected function getPerformanceMetrics(Carbon $startDate): array
    {
        return [
            'response_times' => $this->getAverageResponseTimes($startDate),
            'memory_usage' => $this->getMemoryUsageStats(),
            'database_queries' => $this->getDatabaseQueryStats($startDate),
            'cache_hits' => $this->getCacheHitRatio($startDate),
            'queue_performance' => $this->getQueuePerformanceStats($startDate),
        ];
    }

    protected function getSecurityMetrics(Carbon $startDate): array
    {
        return [
            'failed_logins' => $this->getFailedLogins($startDate),
            'suspicious_activities' => $this->getSuspiciousActivities($startDate),
            'permission_changes' => $this->getPermissionChanges($startDate),
            'system_updates' => $this->getSystemUpdates($startDate),
        ];
    }

    protected function getUsageMetrics(Carbon $startDate): array
    {
        return [
            'active_users' => $this->getActiveUsers($startDate),
            'api_usage' => $this->getApiUsage($startDate),
            'resource_usage' => $this->getResourceUsage($startDate),
            'feature_usage' => $this->getFeatureUsage($startDate),
        ];
    }

    protected function generateReport(array $data, string $format): string
    {
        switch ($format) {
            case 'json':
                return json_encode($data, JSON_PRETTY_PRINT);

            case 'csv':
                return $this->arrayToCsv($this->flattenArray($data));

            case 'html':
            default:
                return $this->generateHtmlReport($data);
        }
    }

    protected function saveReport(string $content, string $format): string
    {
        $filename = sprintf(
            'reports/system_%s_%s.%s',
            $this->option('type'),
            now()->format('Y-m-d_His'),
            $format
        );

        Storage::put($filename, $content);
        return $filename;
    }

    protected function emailReport(string $filename, string $email): void
    {
        // Implementar lógica de envío de email
    }

    protected function getStartDate(string $period): Carbon
    {
        return match ($period) {
            'daily' => now()->subDay(),
            'weekly' => now()->subWeek(),
            'monthly' => now()->subMonth(),
            default => now()->subDay(),
        };
    }

    protected function generateHtmlReport(array $data): string
    {
        // Implementar generación de reporte HTML usando una vista
        return view('reports.system', ['data' => $data])->render();
    }

    protected function arrayToCsv(array $data): string
    {
        $output = fopen('php://temp', 'r+');
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        return $csv;
    }

    protected function flattenArray(array $array, string $prefix = ''): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, $this->flattenArray($value, $prefix . $key . '.'));
            } else {
                $result[$prefix . $key] = $value;
            }
        }
        return $result;
    }

    // Métodos auxiliares para recopilar métricas específicas...
    // (Implementar según necesidades específicas del sistema)
}
