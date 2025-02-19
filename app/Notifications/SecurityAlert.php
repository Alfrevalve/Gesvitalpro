<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\SlackMessage;

class SecurityAlert extends Notification
{
    use Queueable;

    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function via($notifiable): array
    {
        return ['slack'];
    }

    public function toSlack($notifiable): SlackMessage
    {
        $message = (new SlackMessage)
            ->error()
            ->content('🚨 Alerta de Seguridad Detectada')
            ->attachment(function ($attachment) {
                $attachment
                    ->title('Detalles de la Alerta')
                    ->fields([
                        'Intentos Denegados' => $this->data['denied_attempts'] ?? 0,
                        'Inicios de Sesión Fallidos' => $this->data['failed_logins'] ?? 0,
                        'Actividades Sospechosas' => $this->data['suspicious_activities'] ?? 0,
                        'Timestamp' => $this->data['timestamp']->format('Y-m-d H:i:s'),
                        'IP' => request()->ip(),
                        'User Agent' => request()->userAgent()
                    ]);
            });

        if (isset($this->data['additional_info'])) {
            $message->attachment(function ($attachment) {
                $attachment
                    ->title('Información Adicional')
                    ->content($this->data['additional_info']);
            });
        }

        return $message;
    }
}
