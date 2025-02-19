<?php

namespace App\Notifications;

use App\Models\Surgery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SurgeryStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    protected $surgery;
    protected $oldStatus;
    protected $newStatus;

    public function __construct(Surgery $surgery, string $oldStatus, string $newStatus)
    {
        $this->surgery = $surgery;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    public function via($notifiable): array
    {
        return config('surgery.notifications.channels');
    }

    public function toMail($notifiable): MailMessage
    {
        $statusConfig = config('surgery.status');
        $newStatusName = $statusConfig[$this->newStatus]['name'];
        $oldStatusName = $statusConfig[$this->oldStatus]['name'];

        return (new MailMessage)
            ->subject('Estado de Cirugía Actualizado')
            ->greeting('Hola ' . $notifiable->name)
            ->line('El estado de una cirugía ha sido actualizado.')
            ->line('Detalles de la cirugía:')
            ->line('- Institución: ' . $this->surgery->institution)
            ->line('- Fecha: ' . $this->surgery->surgery_date->format('d/m/Y H:i'))
            ->line('- Estado anterior: ' . $oldStatusName)
            ->line('- Nuevo estado: ' . $newStatusName)
            ->action('Ver Detalles', route('surgeries.show', $this->surgery));

        if ($this->newStatus === 'cancelled') {
            $mail->line('Si la cirugía ha sido cancelada, por favor, actualiza tu disponibilidad.');
        } elseif ($this->newStatus === 'rescheduled') {
            $mail->line('Por favor, verifica la nueva fecha programada y confirma tu disponibilidad.');
        }

        return $mail;
    }

    public function toArray($notifiable): array
    {
        $statusConfig = config('surgery.status');
        
        return [
            'surgery_id' => $this->surgery->id,
            'message' => 'El estado de la cirugía ha sido actualizado',
            'old_status' => [
                'code' => $this->oldStatus,
                'name' => $statusConfig[$this->oldStatus]['name']
            ],
            'new_status' => [
                'code' => $this->newStatus,
                'name' => $statusConfig[$this->newStatus]['name']
            ],
            'institution' => $this->surgery->institution,
            'surgery_date' => $this->surgery->surgery_date->format('Y-m-d H:i:s'),
            'surgery_type' => $this->surgery->surgery_type,
            'doctor' => $this->surgery->doctor,
        ];
    }
}
