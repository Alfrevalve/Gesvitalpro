<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\Process\Process;
use Carbon\Carbon;

class MonitorSystemCommand extends Command
{
    protected $signature = 'system:monitor
                          {--interval=5 : Interval between checks in seconds}
                          {--metrics=all : Comma-separated list of metrics to monitor}
                          {--threshold=90 : Alert threshold percentage}
                          {--log : Log metrics to database}';

    protected $description = 'Monitor system performance metrics in real-time';

    protected $running = true;
    protected $metrics = [];
    protected $startTime;

    public function handle()
    {
        $this->startTime = now();
        $interval = (int) $this->option('interval');
        $threshold = (int) $this->option('threshold');
        $selectedMetrics = $this->parseMetrics();

        $this->info('Starting system monitoring...');
        $this->info('Press Ctrl+C to stop monitoring.');
        $this->newLine();

        // Registrar señal de interrupción
        pcntl_signal(SIGINT, function () {
            $this->running = false;
        });

        while ($this->running) {
            $metrics = $this->collectMetrics($selectedMetrics);
            $this->displayMetrics($metrics);
            
            if ($this->option('log')) {
                $this->logMetrics($metrics);
            }

            // Verificar alertas
            $this->checkAlerts($metrics, $threshold);

            // Esperar el intervalo especificado
            sleep($interval);
            pcntl_signal_dispatch();
        }

        $this->newLine();
        $this->info('Monitoring stopped.');
        $this->displaySummary();

        return Command::SUCCESS;
    }

    protected function parseMetrics(): array
    {
        $metrics = $this->option('metrics');
        if ($metrics === 'all') {
            return ['cpu', 'memory', 'disk', 'mysql', 'redis', 'queue'];
        }
        return explode(',', $metrics);
    }

    protected function collectMetrics(array $selectedMetrics): array
    {
        $metrics = [];

        foreach ($selectedMetrics as $metric) {
            switch ($metric) {
                case 'cpu':
                    $metrics['cpu'] = $this->getCpuUsage();
                    break;
                case 'memory':
                    $metrics['memory'] = $this->getMemoryUsage();
                    break;
                case 'disk':
                    $metrics['disk'] = $this->getDiskUsage();
                    break;
                case 'mysql':
                    $metrics['mysql'] = $this->getMySQLMetrics();
                    break;
                case 'redis':
                    $metrics['redis'] = $this->getRedisMetrics();
                    break;
                case 'queue':
                    $metrics['queue'] = $this->getQueueMetrics();
                    break;
            }
        }

        $metrics['timestamp'] = now();
        return $metrics;
    }

    protected function displayMetrics(array $metrics): void
    {
        $this->output->write("\033[H\033[2J"); // Limpiar pantalla
        $this->info('System Monitoring - ' . $metrics['timestamp']->format('Y-m-d H:i:s'));
        $this->newLine();

        foreach ($metrics as $key => $value) {
            if ($key === 'timestamp') continue;

            $this->line(sprintf(
                "<fg=yellow>%s</> Metrics:",
                strtoupper($key)
            ));

            if (is_array($value)) {
                foreach ($value as $subKey => $subValue) {
                    $this->line(sprintf(
                        "  ├─ %s: %s",
                        str_pad($subKey, 20),
                        $this->formatValue($subValue)
                    ));
                }
            } else {
                $this->line(sprintf(
                    "  └─ %s",
                    $this->formatValue($value)
                ));
            }
            $this->newLine();
        }

        $this->line(str_repeat('-', 50));
        $this->line('Running for: ' . $this->getRunningTime());
        $this->line('Press Ctrl+C to stop monitoring.');
    }

    protected function getCpuUsage(): array
    {
        if (PHP_OS_FAMILY === 'Linux') {
            $load = sys_getloadavg();
            $cores = (int) shell_exec('nproc');
            
            return [
                'load_1m' => round($load[0] / $cores * 100, 2),
                'load_5m' => round($load[1] / $cores * 100, 2),
                'load_15m' => round($load[2] / $cores * 100, 2),
                'cores' => $cores,
            ];
        }

        // Fallback para Windows
        return [
            'usage' => random_int(1, 100), // Simulado para Windows
        ];
    }

    protected function getMemoryUsage(): array
    {
        if (PHP_OS_FAMILY === 'Linux') {
            $free = shell_exec('free');
            $free = trim($free);
            $free_arr = explode("\n", $free);
            $mem = explode(" ", $free_arr[1]);
            $mem = array_filter($mem);
            $mem = array_merge($mem);

            return [
                'total' => $this->formatBytes($mem[1] * 1024),
                'used' => $this->formatBytes($mem[2] * 1024),
                'free' => $this->formatBytes($mem[3] * 1024),
                'usage' => round($mem[2] / $mem[1] * 100, 2),
            ];
        }

        // Fallback para Windows
        $memory_info = [
            'total' => memory_get_peak_usage(true),
            'used' => memory_get_usage(true),
        ];

        return [
            'total' => $this->formatBytes($memory_info['total']),
            'used' => $this->formatBytes($memory_info['used']),
            'usage' => round($memory_info['used'] / $memory_info['total'] * 100, 2),
        ];
    }

    protected function getDiskUsage(): array
    {
        $path = '/';
        return [
            'total' => $this->formatBytes(disk_total_space($path)),
            'free' => $this->formatBytes(disk_free_space($path)),
            'used' => $this->formatBytes(disk_total_space($path) - disk_free_space($path)),
            'usage' => round((disk_total_space($path) - disk_free_space($path)) / disk_total_space($path) * 100, 2),
        ];
    }

    protected function getMySQLMetrics(): array
    {
        try {
            $variables = DB::select('SHOW GLOBAL STATUS');
            $metrics = [];
            
            foreach ($variables as $var) {
                switch ($var->Variable_name) {
                    case 'Threads_connected':
                        $metrics['connections'] = $var->Value;
                        break;
                    case 'Questions':
                        $metrics['queries'] = $var->Value;
                        break;
                    case 'Slow_queries':
                        $metrics['slow_queries'] = $var->Value;
                        break;
                }
            }

            return $metrics;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    protected function getRedisMetrics(): array
    {
        try {
            $info = Redis::info();
            return [
                'connected_clients' => $info['connected_clients'] ?? 0,
                'used_memory' => $this->formatBytes($info['used_memory'] ?? 0),
                'total_commands' => $info['total_commands_processed'] ?? 0,
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    protected function getQueueMetrics(): array
    {
        try {
            return [
                'jobs' => Queue::size(),
                'failed' => DB::table('failed_jobs')->count(),
                'processed' => Cache::get('queue_processed', 0),
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    protected function checkAlerts(array $metrics, int $threshold): void
    {
        foreach ($metrics as $key => $value) {
            if ($key === 'timestamp') continue;

            if (is_array($value) && isset($value['usage']) && $value['usage'] > $threshold) {
                $this->error(sprintf(
                    'Alert: %s usage is above %d%% (Current: %.2f%%)',
                    strtoupper($key),
                    $threshold,
                    $value['usage']
                ));
            }
        }
    }

    protected function logMetrics(array $metrics): void
    {
        DB::table('system_metrics')->insert([
            'metrics' => json_encode($metrics),
            'created_at' => $metrics['timestamp'],
        ]);
    }

    protected function formatBytes($bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    protected function formatValue($value): string
    {
        if (is_bool($value)) {
            return $value ? '<fg=green>Yes</>' : '<fg=red>No</>';
        }

        if (is_numeric($value)) {
            return number_format($value, 2);
        }

        return (string) $value;
    }

    protected function getRunningTime(): string
    {
        $diff = $this->startTime->diff(now());
        return $diff->format('%H:%I:%S');
    }

    protected function displaySummary(): void
    {
        // Implementar resumen de monitoreo
    }
}
