<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class LogReportCommand extends Command
{
    protected $signature = 'log:report
                          {--period=daily : Report period (daily, weekly, monthly)}
                          {--type=all : Type of logs to analyze (all, system, security, etc.)}
                          {--format=html : Output format (html, json, csv)}
                          {--email= : Email address to send the report to}
                          {--threshold=100 : Error threshold for alerts}';

    protected $description = 'Generate a log analysis report';

    protected $logTypes = [
        'system' => 'logs/system.log',
        'monitoring' => 'logs/monitoring.log',
        'performance' => 'logs/performance.log',
        'security' => 'logs/security.log',
        'audit' => 'logs/audit.log',
    ];

    protected $patterns = [
        'error' => '/\b(?:error|exception|fatal|failed|failure)\b/i',
        'warning' => '/\b(?:warning|warn|attention)\b/i',
        'critical' => '/\b(?:critical|emergency|alert)\b/i',
        'success' => '/\b(?:success|successful|completed)\b/i',
    ];

    public function handle()
    {
        $startTime = microtime(true);
        $this->info('Generating log analysis report...');

        try {
            // Recopilar datos
            $data = $this->analyzeLogFiles();

            // Generar reporte
            $report = $this->generateReport($data);

            // Guardar reporte
            $filename = $this->saveReport($report);

            // Enviar por email si se especifica
            if ($email = $this->option('email')) {
                $this->emailReport($report, $email);
            }

            // Verificar umbrales
            $this->checkThresholds($data);

            $duration = round(microtime(true) - $startTime, 2);
            $this->info("Report generated successfully in {$duration} seconds: {$filename}");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error generating report: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    protected function analyzeLogFiles(): array
    {
        $data = [
            'period' => $this->option('period'),
            'generated_at' => now(),
            'summary' => [
                'total_entries' => 0,
                'errors' => 0,
                'warnings' => 0,
                'critical' => 0,
                'success' => 0,
            ],
            'by_type' => [],
            'error_details' => [],
        ];

        $startDate = $this->getStartDate();

        foreach ($this->getLogTypes() as $type => $path) {
            $logPath = storage_path($path);
            if (!File::exists($logPath)) {
                continue;
            }

            $typeData = $this->analyzeSingleLog($logPath, $startDate);
            $data['by_type'][$type] = $typeData;

            // Actualizar resumen
            $data['summary']['total_entries'] += $typeData['total_entries'];
            $data['summary']['errors'] += $typeData['errors'];
            $data['summary']['warnings'] += $typeData['warnings'];
            $data['summary']['critical'] += $typeData['critical'];
            $data['summary']['success'] += $typeData['success'];

            // Agregar detalles de errores
            if (!empty($typeData['error_details'])) {
                $data['error_details'][$type] = $typeData['error_details'];
            }
        }

        return $data;
    }

    protected function analyzeSingleLog(string $path, Carbon $startDate): array
    {
        $data = [
            'total_entries' => 0,
            'errors' => 0,
            'warnings' => 0,
            'critical' => 0,
            'success' => 0,
            'by_hour' => [],
            'error_details' => [],
        ];

        $handle = fopen($path, 'r');
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                if ($this->isLineInPeriod($line, $startDate)) {
                    $data['total_entries']++;
                    $this->categorizeLine($line, $data);
                }
            }
            fclose($handle);
        }

        return $data;
    }

    protected function isLineInPeriod(string $line, Carbon $startDate): bool
    {
        if (preg_match('/\[([^\]]+)\]/', $line, $matches)) {
            try {
                $lineDate = Carbon::parse($matches[1]);
                return $lineDate->gte($startDate);
            } catch (\Exception $e) {
                return false;
            }
        }
        return false;
    }

    protected function categorizeLine(string $line, array &$data): void
    {
        foreach ($this->patterns as $type => $pattern) {
            if (preg_match($pattern, $line)) {
                $data[$type]++;

                if ($type === 'error' || $type === 'critical') {
                    $data['error_details'][] = $this->extractErrorDetails($line);
                }

                // Agrupar por hora
                if (preg_match('/\[([^\]]+)\]/', $line, $matches)) {
                    try {
                        $hour = Carbon::parse($matches[1])->format('H:00');
                        $data['by_hour'][$hour] = ($data['by_hour'][$hour] ?? 0) + 1;
                    } catch (\Exception $e) {
                        // Ignorar errores de parseo de fecha
                    }
                }
            }
        }
    }

    protected function extractErrorDetails(string $line): array
    {
        $details = [
            'timestamp' => '',
            'message' => $line,
            'type' => '',
            'context' => [],
        ];

        // Extraer timestamp
        if (preg_match('/\[([^\]]+)\]/', $line, $matches)) {
            $details['timestamp'] = $matches[1];
        }

        // Extraer tipo de error
        if (preg_match('/\b(error|exception|fatal|critical)\b/i', $line, $matches)) {
            $details['type'] = strtoupper($matches[1]);
        }

        // Extraer contexto JSON si existe
        if (preg_match('/\{.*\}/', $line, $matches)) {
            try {
                $details['context'] = json_decode($matches[0], true) ?? [];
            } catch (\Exception $e) {
                // Ignorar errores de parseo JSON
            }
        }

        return $details;
    }

    protected function generateReport(array $data): string
    {
        $format = $this->option('format');

        switch ($format) {
            case 'json':
                return json_encode($data, JSON_PRETTY_PRINT);
            case 'csv':
                return $this->generateCsvReport($data);
            case 'html':
            default:
                return view('reports.logs', ['data' => $data])->render();
        }
    }

    protected function generateCsvReport(array $data): string
    {
        $output = fopen('php://temp', 'r+');

        // Escribir encabezados
        fputcsv($output, ['Log Type', 'Total Entries', 'Errors', 'Warnings', 'Critical', 'Success']);

        // Escribir datos por tipo
        foreach ($data['by_type'] as $type => $typeData) {
            fputcsv($output, [
                $type,
                $typeData['total_entries'],
                $typeData['errors'],
                $typeData['warnings'],
                $typeData['critical'],
                $typeData['success'],
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    protected function saveReport(string $content): string
    {
        $filename = sprintf(
            'reports/logs-%s-%s.%s',
            $this->option('period'),
            now()->format('Y-m-d-His'),
            $this->option('format')
        );

        Storage::put($filename, $content);
        return $filename;
    }

    protected function emailReport(string $report, string $email): void
    {
        Mail::raw($report, function ($message) use ($email) {
            $message->to($email)
                ->subject('Log Analysis Report - ' . now()->format('Y-m-d'));
        });
    }

    protected function checkThresholds(array $data): void
    {
        $threshold = (int) $this->option('threshold');

        if ($data['summary']['errors'] > $threshold) {
            $this->warn("Error threshold exceeded: {$data['summary']['errors']} errors found (threshold: {$threshold})");
            
            // Notificar si es necesario
            if (config('logging.monitoring.enabled')) {
                event(new \App\Events\LogThresholdExceeded($data));
            }
        }
    }

    protected function getStartDate(): Carbon
    {
        return match($this->option('period')) {
            'weekly' => now()->subWeek(),
            'monthly' => now()->subMonth(),
            default => now()->subDay(),
        };
    }

    protected function getLogTypes(): array
    {
        $type = $this->option('type');
        if ($type === 'all') {
            return $this->logTypes;
        }

        return array_intersect_key($this->logTypes, array_flip((array) $type));
    }
}
