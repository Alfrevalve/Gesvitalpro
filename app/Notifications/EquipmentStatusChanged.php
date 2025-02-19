<?php

namespace App\Notifications;

use App\Models\Equipment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EquipmentStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    protected $equipment;
    protected $oldStatus;
    protected $newStatus;

    public function __construct(Equipment $equipment, string $oldStatus, string $newStatus)
    {
        $this->equipment = $equipment;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    public function via($notifiable): array
    {
        return config('surgery.notifications.channels');
    }

    public function toMail($notifiable): MailMessage
    {
        $statusTranslations = [
            'available' => 'Disponible',
            'in_use' => 'En Uso',
            'maintenance' => 'En Mantenimiento'
        ];

        $mail = (new MailMessage)
            ->subject('Estado de Equipo Actualizado')
            ->greeting('Hola ' . $notifiable->name)
            ->line('El estado de un equipo ha sido actualizado.')
            ->line('Detalles del equipo:')
            ->line('- Nombre: ' . $this->equipment->name)
            ->line('- Tipo: ' . $this->equipment->type)
            ->line('- Línea: ' . $this->equipment->line->name)
            ->line('- Número de serie: ' . $this->equipment->serial_number)
            ->line('- Estado anterior: ' . $statusTranslations[$this->oldStatus])
            ->line('- Nuevo estado: ' . $statusTranslations[$this->newStatus])
            ->action('Ver Detalles', route('equipment.show', $this->equipment));

        if ($this->newStatus === 'maintenance') {
            $mail->line('El equipo ha sido marcado para mantenimiento. Por favor, actualice la programación de cirugías según sea necesario.');
        } elseif ($this->newStatus === 'available') {
            $mail->line('El equipo está nuevamente disponible para su uso en cirugías.');
        }

        return $mail;
    }

    public function toArray($notifiable): array
    {
        return [
            'equipment_id' => $this->equipment->id,
            'message' => 'El estado del equipo ha sido actualizado',
            'name' => $this->equipment->name,
            'type' => $this->equipment->type,
            'line' => $this->equipment->line->name,
            'serial_number' => $this->equipment->serial_number,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'last_maintenance' => $this->equipment->last_maintenance?->format('Y-m-d'),
            'next_maintenance' => $this->equipment->next_maintenance?->format('Y-m-d'),
        ];
    }
}
