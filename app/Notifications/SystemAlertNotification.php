<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class SystemAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $data;
    protected $channels;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $data, array $channels = ['mail'])
    {
        $this->data = $data;
        $this->channels = $channels;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $this->channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Alerta del Sistema - GesVitalPro')
            ->greeting('¡Atención!')
            ->line($this->data['message'])
            ->line(isset($this->data['context']) ? json_encode($this->data['context']) : '')
            ->action('Ver Detalles', url('/admin/alerts'))
            ->line('Gracias por su atención.');
    }

    /**
     * Get the Slack representation of the notification.
     */
    public function toSlack(object $notifiable): SlackMessage
    {
        return (new SlackMessage)
            ->warning()
            ->content('¡Alerta del Sistema!')
            ->attachment(function ($attachment) {
                $attachment->title('Detalles de la Alerta')
                    ->content($this->data['message'])
                    ->fields([
                        'Contexto' => isset($this->data['context']) ? json_encode($this->data['context']) : 'N/A',
                        'Fecha' => now()->format('Y-m-d H:i:s'),
                    ]);
            });
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => $this->data['message'],
            'context' => $this->data['context'] ?? null,
        ];
    }
}
