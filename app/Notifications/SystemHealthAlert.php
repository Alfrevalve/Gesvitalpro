<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SystemHealthAlert extends Notification implements ShouldQueue
{
    use Queueable;

    protected $healthCheck;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $healthCheck)
    {
        $this->healthCheck = $healthCheck;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mailMessage = (new MailMessage)
            ->subject('⚠️ Alerta de Salud del Sistema - GesVitalPro')
            ->greeting('¡Atención!')
            ->line('Se han detectado problemas en el sistema:')
            ->line('Estado general: ' . strtoupper($this->healthCheck['status']));

        foreach ($this->healthCheck['checks'] as $checkName => $checkResult) {
            if ($checkResult['status'] !== 'healthy') {
                $mailMessage->line(sprintf(
                    '• %s: %s - %s',
                    ucfirst($checkName),
                    strtoupper($checkResult['status']),
                    $checkResult['message'] ?? 'Sin mensaje adicional'
                ));
            }
        }

        $mailMessage
            ->line('Por favor, revise el panel de administración para más detalles.')
            ->action('Ver Panel de Administración', url('/admin/system-health'))
            ->line('Este es un mensaje automático del sistema de monitoreo.');

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $issues = [];
        foreach ($this->healthCheck['checks'] as $checkName => $checkResult) {
            if ($checkResult['status'] !== 'healthy') {
                $issues[$checkName] = [
                    'status' => $checkResult['status'],
                    'message' => $checkResult['message'] ?? null,
                ];
            }
        }

        return [
            'status' => $this->healthCheck['status'],
            'timestamp' => $this->healthCheck['timestamp'],
            'issues' => $issues,
        ];
    }

    /**
     * Get the notification's database type.
     */
    public function databaseType(): string
    {
        return 'system_health_alert';
    }

    /**
     * Determine the notification's urgency level.
     */
    public function urgency(): string
    {
        return match ($this->healthCheck['status']) {
            'error' => 'high',
            'warning' => 'medium',
            default => 'low',
        };
    }

    /**
     * Determine if the notification should be sent.
     */
    public function shouldSend(object $notifiable): bool
    {
        // Evitar enviar notificaciones duplicadas en un corto período de tiempo
        $recentNotification = $notifiable->notifications()
            ->where('type', self::class)
            ->where('created_at', '>=', now()->subMinutes(30))
            ->exists();

        return !$recentNotification;
    }
}
