<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\HealthCheckService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SystemHealthAlert;

class SystemHealthCheck extends Command
{
    protected $signature = 'system:health-check {--notify : Send notifications for issues}';
    protected $description = 'Check the health status of the system';

    protected $healthCheckService;

    public function __construct(HealthCheckService $healthCheckService)
    {
        parent::__construct();
        $this->healthCheckService = $healthCheckService;
    }

    public function handle()
    {
        $this->info('Iniciando verificación de salud del sistema...');
        $this->newLine();

        $healthCheck = $this->healthCheckService->checkSystem();

        // Mostrar estado general
        $this->displayOverallStatus($healthCheck['status']);

        // Mostrar resultados detallados
        foreach ($healthCheck['checks'] as $checkName => $checkResult) {
            $this->displayCheckResult($checkName, $checkResult);
        }

        // Registrar resultados
        $this->logResults($healthCheck);

        // Enviar notificaciones si hay problemas y la opción está activada
        if ($this->option('notify') && $healthCheck['status'] !== 'healthy') {
            $this->sendNotifications($healthCheck);
        }

        return $healthCheck['status'] === 'healthy' ? Command::SUCCESS : Command::FAILURE;
    }

    private function displayOverallStatus(string $status)
    {
        $statusColor = [
            'healthy' => 'green',
            'warning' => 'yellow',
            'error' => 'red',
        ][$status] ?? 'red';

        $this->newLine();
        $this->line('Estado General del Sistema: <fg=' . $statusColor . '>' . strtoupper($status) . '</>');
        $this->newLine();
    }

    private function displayCheckResult(string $name, array $check)
    {
        $status = $check['status'] ?? 'unknown';
        $statusColor = [
            'healthy' => 'green',
            'warning' => 'yellow',
            'error' => 'red',
            'unknown' => 'gray',
        ][$status] ?? 'gray';

        $this->line(sprintf(
            '%-20s [<fg=%s>%s</>]',
            ucfirst($name),
            $statusColor,
            strtoupper($status)
        ));

        // Mostrar detalles adicionales
        if (isset($check['message'])) {
            $this->line(sprintf('  ├─ Mensaje: %s', $check['message']));
        }

        foreach ($check as $key => $value) {
            if (!in_array($key, ['status', 'message'])) {
                if (is_array($value)) {
                    $this->line(sprintf('  ├─ %s:', ucfirst($key)));
                    foreach ($value as $subKey => $subValue) {
                        $this->line(sprintf('  │  ├─ %s: %s', ucfirst($subKey), $subValue));
                    }
                } else {
                    $this->line(sprintf('  ├─ %s: %s', ucfirst($key), $value));
                }
            }
        }

        $this->newLine();
    }

    private function logResults(array $healthCheck)
    {
        $logLevel = [
            'healthy' => 'info',
            'warning' => 'warning',
            'error' => 'error',
        ][$healthCheck['status']] ?? 'error';

        Log::channel('system')->$logLevel('System Health Check Results', [
            'status' => $healthCheck['status'],
            'checks' => $healthCheck['checks'],
            'timestamp' => $healthCheck['timestamp'],
        ]);
    }

    private function sendNotifications(array $healthCheck)
    {
        // Obtener administradores del sistema
        $admins = \App\Models\User::role('admin')->get();

        foreach ($admins as $admin) {
            Notification::send($admin, new SystemHealthAlert($healthCheck));
        }

        $this->info('Notificaciones enviadas a los administradores del sistema.');
    }
}
