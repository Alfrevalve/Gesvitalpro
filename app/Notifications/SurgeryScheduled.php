<?php

namespace App\Notifications;

use App\Models\Surgery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SurgeryScheduled extends Notification implements ShouldQueue
{
    use Queueable;

    protected $surgery;

    public function __construct(Surgery $surgery)
    {
        $this->surgery = $surgery;
    }

    public function via($notifiable): array
    {
        return config('surgery.notifications.channels');
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nueva Cirugía Programada')
            ->greeting('Hola ' . $notifiable->name)
            ->line('Has sido asignado/a a una nueva cirugía.')
            ->line('Detalles de la cirugía:')
            ->line('- Institución: ' . $this->surgery->institution)
            ->line('- Fecha: ' . $this->surgery->surgery_date->format('d/m/Y H:i'))
            ->line('- Tipo: ' . $this->surgery->surgery_type)
            ->line('- Doctor: ' . $this->surgery->doctor)
            ->action('Ver Detalles', route('surgeries.show', $this->surgery))
            ->line('Por favor, revisa los detalles y confirma tu disponibilidad.');
    }

    public function toArray($notifiable): array
    {
        return [
            'surgery_id' => $this->surgery->id,
            'message' => 'Has sido asignado/a a una nueva cirugía',
            'institution' => $this->surgery->institution,
            'surgery_date' => $this->surgery->surgery_date->format('Y-m-d H:i:s'),
            'surgery_type' => $this->surgery->surgery_type,
            'doctor' => $this->surgery->doctor,
        ];
    }
}
