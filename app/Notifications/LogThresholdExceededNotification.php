<?php

namespace App\Notifications;

use App\Events\LogThresholdExceeded;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class LogThresholdExceededNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $event;
    protected $channels;
    protected $urgency;

    /**
     * Create a new notification instance.
     */
    public function __construct(LogThresholdExceeded $event, array $channels)
    {
        $this->event = $event;
        $this->channels = $channels;
        $this->urgency = $event->getUrgencyLevel();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return array_intersect(
            $this->channels,
            ['mail', 'slack', 'database']
        );
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mailMessage = (new MailMessage)
            ->subject($this->getEmailSubject())
            ->greeting($this->getGreeting())
            ->line($this->event->getDescription());

        // Agregar resumen
        $summary = $this->event->getSummary();
        $mailMessage->line('**Resumen:**')
            ->line("- Total de errores: {$summary['error_count']}")
            ->line("- Errores críticos: {$summary['critical_count']}")
            ->line("- Advertencias: {$summary['warning_count']}")
            ->line("- Tasa de error: {$summary['error_rate']}%")
            ->line("- Nivel de urgencia: " . ucfirst($summary['urgency_level']));

        // Agregar errores más frecuentes
        if (!empty($summary['most_frequent_errors'])) {
            $mailMessage->line('**Errores más frecuentes:**');
            foreach ($summary['most_frequent_errors'] as $error => $count) {
                $mailMessage->line("- {$error} ({$count} veces)");
            }
        }

        // Agregar enlace al panel de administración
        $mailMessage->action(
            'Ver Panel de Logs',
            url('/admin/monitoring/logs')
        );

        // Agregar nota de urgencia para situaciones críticas
        if ($this->event->isCritical()) {
            $mailMessage->error(
                'Esta es una situación crítica que requiere atención inmediata.'
            );
        }

        return $mailMessage;
    }

    /**
     * Get the Slack representation of the notification.
     */
    public function toSlack(object $notifiable): SlackMessage
    {
        $message = (new SlackMessage)
            ->from('GesVitalPro Monitor')
            ->to(config('logging.monitoring.slack_channel'))
            ->content($this->event->getDescription());

        if ($this->event->isCritical()) {
            $message->error();
        } else {
            $message->warning();
        }

        $summary = $this->event->getSummary();
        
        $message->attachment(function ($attachment) use ($summary) {
            $attachment
                ->title('Detalles del Umbral Excedido')
                ->fields([
                    'Total de Errores' => $summary['error_count'],
                    'Errores Críticos' => $summary['critical_count'],
                    'Advertencias' => $summary['warning_count'],
                    'Tasa de Error' => $summary['error_rate'] . '%',
                    'Nivel de Urgencia' => ucfirst($summary['urgency_level']),
                    'Período' => ucfirst($summary['period']),
                ]);
        });

        if (!empty($summary['most_frequent_errors'])) {
            $message->attachment(function ($attachment) use ($summary) {
                $attachment
                    ->title('Errores más Frecuentes')
                    ->content(
                        collect($summary['most_frequent_errors'])
                            ->map(fn($count, $error) => "• {$error} ({$count} veces)")
                            ->join("\n")
                    );
            });
        }

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'event' => 'log_threshold_exceeded',
            'summary' => $this->event->getSummary(),
            'description' => $this->event->getDescription(),
            'urgency_level' => $this->urgency,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Get the notification's tags.
     *
     * @return array
     */
    public function tags(): array
    {
        return [
            'log-threshold',
            'monitoring',
            'urgency:' . $this->urgency,
        ];
    }

    /**
     * Determine the notification's urgency level.
     */
    public function urgency(): string
    {
        return $this->urgency;
    }

    /**
     * Get the notification's email subject.
     */
    protected function getEmailSubject(): string
    {
        $prefix = $this->event->isCritical() ? '🚨' : '⚠️';
        return sprintf(
            '%s Umbral de Logs Excedido - GesVitalPro',
            $prefix
        );
    }

    /**
     * Get the notification's greeting.
     */
    protected function getGreeting(): string
    {
        return $this->event->isCritical() 
            ? '¡Atención Inmediata Requerida!'
            : '¡Atención!';
    }

    /**
     * Get the time before the notification should be sent.
     *
     * @return \DateTimeInterface|\DateInterval|int|null
     */
    public function delay()
    {
        // Enviar inmediatamente si es crítico
        if ($this->event->isCritical()) {
            return null;
        }

        // Retrasar otros niveles de urgencia
        return match($this->urgency) {
            'high' => now()->addMinutes(5),
            'medium' => now()->addMinutes(15),
            'low' => now()->addMinutes(30),
            default => now()->addMinutes(15),
        };
    }

    /**
     * Determine if the notification should be sent.
     */
    public function shouldSend(object $notifiable): bool
    {
        if ($this->event->isCritical()) {
            return true;
        }

        // Verificar si ya se envió una notificación similar recientemente
        return !$notifiable->notifications()
            ->where('type', self::class)
            ->where('created_at', '>=', now()->subHours(1))
            ->exists();
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware(): array
    {
        return [new WithoutOverlapping($this->event->getSummary()['period'])];
    }

    /**
     * Determine the time at which the job should timeout.
     *
     * @return \DateTime
     */
    public function retryUntil(): \DateTime
    {
        return now()->addHours(1);
    }
}
