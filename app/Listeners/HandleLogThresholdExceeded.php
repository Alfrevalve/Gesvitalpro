<?php

namespace App\Listeners;

use App\Events\LogThresholdExceeded;
use App\Models\SystemAlert;
use App\Notifications\LogThresholdExceededNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Models\User;

class HandleLogThresholdExceeded
{
    /**
     * Handle the event.
     */
    public function handle(LogThresholdExceeded $event): void
    {
        // Registrar el evento
        Log::channel('monitoring')->warning($event->getDescription(), [
            'summary' => $event->getSummary(),
            'tags' => $event->getTags(),
        ]);

        // Crear alerta del sistema
        $this->createSystemAlert($event);

        // Notificar a los administradores
        $this->notifyAdministrators($event);

        // Ejecutar acciones adicionales según la urgencia
        if ($event->isCritical()) {
            $this->handleCriticalSituation($event);
        }
    }

    /**
     * Crear una alerta del sistema
     */
    protected function createSystemAlert(LogThresholdExceeded $event): void
    {
        SystemAlert::create([
            'type' => $event->isCritical() ? 'critical' : 'warning',
            'metric' => 'log_errors',
            'threshold' => $event->threshold,
            'current_value' => $event->getErrorCount(),
            'message' => $event->getDescription(),
            'metadata' => [
                'summary' => $event->getSummary(),
                'most_frequent_errors' => $event->getMostFrequentErrors(),
            ],
        ]);
    }

    /**
     * Notificar a los administradores
     */
    protected function notifyAdministrators(LogThresholdExceeded $event): void
    {
        // Obtener canales de notificación configurados
        $channels = $event->getNotificationChannels();

        if (empty($channels)) {
            return;
        }

        // Obtener administradores
        $admins = User::role('admin')->get();

        // Enviar notificaciones
        Notification::send($admins, new LogThresholdExceededNotification(
            $event,
            $channels
        ));
    }

    /**
     * Manejar situación crítica
     */
    protected function handleCriticalSituation(LogThresholdExceeded $event): void
    {
        // Registrar en el log de emergencia
        Log::emergency('Critical log threshold exceeded', [
            'summary' => $event->getSummary(),
            'error_details' => $event->getErrorDetails(),
        ]);

        // Realizar acciones de emergencia según la configuración
        if (config('logging.monitoring.emergency_actions.enabled', false)) {
            $this->executeEmergencyActions($event);
        }

        // Notificar al equipo de guardia si está configurado
        if ($oncallTeam = config('logging.monitoring.oncall_team')) {
            $this->notifyOncallTeam($event, $oncallTeam);
        }
    }

    /**
     * Ejecutar acciones de emergencia
     */
    protected function executeEmergencyActions(LogThresholdExceeded $event): void
    {
        $actions = config('logging.monitoring.emergency_actions.actions', []);

        foreach ($actions as $action) {
            try {
                switch ($action) {
                    case 'rotate_logs':
                        \Artisan::call('log:rotate', ['--force' => true]);
                        break;

                    case 'clear_cache':
                        \Artisan::call('cache:clear');
                        break;

                    case 'restart_queue':
                        \Artisan::call('queue:restart');
                        break;

                    case 'backup_logs':
                        $this->backupLogs();
                        break;

                    default:
                        Log::warning("Unknown emergency action: {$action}");
                }
            } catch (\Exception $e) {
                Log::error("Failed to execute emergency action: {$action}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }
    }

    /**
     * Notificar al equipo de guardia
     */
    protected function notifyOncallTeam(LogThresholdExceeded $event, array $oncallTeam): void
    {
        foreach ($oncallTeam as $member) {
            try {
                // Enviar notificación por cada canal configurado
                foreach ($member['channels'] as $channel => $contact) {
                    switch ($channel) {
                        case 'sms':
                            $this->sendSmsNotification($contact, $event);
                            break;

                        case 'phone':
                            $this->makePhoneCall($contact, $event);
                            break;

                        case 'telegram':
                            $this->sendTelegramMessage($contact, $event);
                            break;
                    }
                }
            } catch (\Exception $e) {
                Log::error("Failed to notify oncall team member", [
                    'member' => $member,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Realizar backup de logs
     */
    protected function backupLogs(): void
    {
        $timestamp = now()->format('Y-m-d_His');
        $backupDir = storage_path("logs/backup/{$timestamp}");

        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        foreach (glob(storage_path('logs/*.log')) as $logFile) {
            $filename = basename($logFile);
            copy($logFile, "{$backupDir}/{$filename}");
        }

        // Comprimir backup
        $zip = new \ZipArchive();
        $zipName = storage_path("logs/backup/logs_{$timestamp}.zip");

        if ($zip->open($zipName, \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($backupDir),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($backupDir) + 1);
                    $zip->addFile($filePath, $relativePath);
                }
            }

            $zip->close();
            
            // Limpiar directorio temporal
            $this->removeDirectory($backupDir);
        }
    }

    /**
     * Eliminar directorio y su contenido
     */
    protected function removeDirectory(string $dir): void
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . DIRECTORY_SEPARATOR . $object)) {
                        $this->removeDirectory($dir . DIRECTORY_SEPARATOR . $object);
                    } else {
                        unlink($dir . DIRECTORY_SEPARATOR . $object);
                    }
                }
            }
            rmdir($dir);
        }
    }
}
