<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class BrokenLinksNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $brokenLinks;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $brokenLinks)
    {
        $this->brokenLinks = $brokenLinks;
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
            ->subject('⚠️ Enlaces Rotos Detectados - GesVitalPro')
            ->greeting('¡Atención!')
            ->line('Se han detectado enlaces rotos en el sistema:')
            ->line(new HtmlString('<br>'));

        foreach ($this->brokenLinks as $link) {
            $mailMessage->line(new HtmlString(
                "<strong>URL:</strong> {$link['url']}<br>" .
                "<strong>Origen:</strong> {$link['source']}<br>" .
                "<strong>Estado:</strong> {$link['status']}<br>" .
                "<strong>Detectado:</strong> {$link['checked_at']}<br>" .
                "----------------------------------------<br>"
            ));
        }

        $mailMessage
            ->action('Ver Panel de Enlaces', url('/admin/monitoring/links'))
            ->line('Por favor, revise y corrija estos enlaces lo antes posible.')
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
        return [
            'type' => 'broken_links',
            'total_links' => count($this->brokenLinks),
            'links' => $this->brokenLinks,
            'detected_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Get the notification's database type.
     */
    public function databaseType(): string
    {
        return 'broken_links_alert';
    }

    /**
     * Determine the notification's urgency level.
     */
    public function urgency(): string
    {
        return count($this->brokenLinks) > 10 ? 'high' : 'medium';
    }

    /**
     * Determine if the notification should be sent.
     */
    public function shouldSend(object $notifiable): bool
    {
        // Evitar enviar notificaciones duplicadas en un corto período de tiempo
        $recentNotification = $notifiable->notifications()
            ->where('type', self::class)
            ->where('created_at', '>=', now()->subHours(24))
            ->exists();

        return !$recentNotification;
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags(): array
    {
        return ['broken_links', 'monitoring'];
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware(): array
    {
        return [new WithoutOverlapping($this->notifiable->id)];
    }

    /**
     * Determine the time at which the job should timeout.
     *
     * @return \DateTime
     */
    public function retryUntil(): \DateTime
    {
        return now()->addHours(12);
    }
}
