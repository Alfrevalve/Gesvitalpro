<?php

namespace App\Notifications;

use App\Models\Equipment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EquipmentMaintenanceDue extends Notification implements ShouldQueue
{
    use Queueable;

    protected $equipment;

    public function __construct(Equipment $equipment)
    {
        $this->equipment = $equipment;
    }

    public function via($notifiable): array
    {
        return config('surgery.notifications.channels');
    }

    public function toMail($notifiable): MailMessage
    {
        $maintenanceReason = $this->equipment->needsMaintenance() 
            ? 'fecha programada de mantenimiento'
            : 'número de cirugías realizadas';

        return (new MailMessage)
            ->subject('Mantenimiento de Equipo Requerido')
            ->greeting('Hola ' . $notifiable->name)
            ->line('Un equipo requiere mantenimiento debido a ' . $maintenanceReason . '.')
            ->line('Detalles del equipo:')
            ->line('- Nombre: ' . $this->equipment->name)
            ->line('- Tipo: ' . $this->equipment->type)
            ->line('- Línea: ' . $this->equipment->line->name)
            ->line('- Número de serie: ' . $this->equipment->serial_number)
            ->line('- Cirugías realizadas: ' . $this->equipment->surgeries_count)
            ->line('- Último mantenimiento: ' . ($this->equipment->last_maintenance 
                ? $this->equipment->last_maintenance->format('d/m/Y')
                : 'No registrado'))
            ->action('Ver Detalles', route('equipment.show', $this->equipment))
            ->line('Por favor, programe el mantenimiento lo antes posible para garantizar el funcionamiento óptimo del equipo.');
    }

    public function toArray($notifiable): array
    {
        return [
            'equipment_id' => $this->equipment->id,
            'message' => 'Equipo requiere mantenimiento',
            'name' => $this->equipment->name,
            'type' => $this->equipment->type,
            'line' => $this->equipment->line->name,
            'serial_number' => $this->equipment->serial_number,
            'surgeries_count' => $this->equipment->surgeries_count,
            'last_maintenance' => $this->equipment->last_maintenance?->format('Y-m-d'),
            'next_maintenance' => $this->equipment->next_maintenance?->format('Y-m-d'),
        ];
    }
}
