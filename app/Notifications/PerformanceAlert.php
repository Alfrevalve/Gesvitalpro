<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Messages\MailMessage;

class PerformanceAlert extends Notification
{
    use Queueable;

    protected $message;
    protected $report;

    public function __construct(string $message, array $report)
    {
        $this->message = $message;
        $this->report = $report;
    }

    public function via($notifiable): array
    {
        $channels = ['database'];

        if (config('performance.alerts.channels.slack')) {
            $channels[] = 'slack';
        }

        if (config('performance.alerts.channels.email')) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toSlack($notifiable): SlackMessage
    {
        $message = (new SlackMessage)
            ->warning()
            ->content('⚠️ Alerta de Rendimiento')
            ->attachment(function ($attachment) {
                $attachment
                    ->title($this->message)
                    ->fields([
                        'Consultas Lentas' => $this->report['slow_queries'] ?? 0,
                        'Uso de Memoria' => round($this->report['memory_usage']['current'] ?? 0, 2) . 'MB',
                        'Ratio de Cache' => round(($this->report['cache']['hit_ratio'] ?? 0) * 100, 2) . '%',
                        'Tamaño de BD' => round($this->report['database_size'] ?? 0, 2) . 'MB',
                        'Timestamp' => now()->format('Y-m-d H:i:s')
                    ]);

                if (!empty($this->report['suggested_indexes'])) {
                    $attachment->field('Índices Sugeridos',
                        collect($this->report['suggested_indexes'])
                            ->pluck('columns')
                            ->map(fn($cols) => implode(', ', $cols))
                            ->implode("\n")
                    );
                }
            });

        return $message;
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Alerta de Rendimiento del Sistema')
            ->markdown('emails.performance.alert', [
                'message' => $this->message,
                'report' => $this->report,
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => $this->message,
            'report' => $this->report,
            'timestamp' => now()->toIso8601String(),
            'severity' => 'warning',
            'type' => 'performance_alert'
        ];
    }
}
