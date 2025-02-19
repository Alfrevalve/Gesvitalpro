<?php

namespace App\Notifications;

use App\Models\Surgery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;

class EquipmentNotAvailable extends Notification implements ShouldQueue
{
    use Queueable;

    protected $surgery;
    protected $unavailableEquipment;

    public function __construct(Surgery $surgery, Collection $unavailableEquipment)
    {
        $this->surgery = $surgery;
        $this->unavailableEquipment = $unavailableEquipment;
    }

    public function via($notifiable): array
    {
        return config('surgery.notifications.channels');
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('⚠️ Alerta: Equipo No Disponible para Cirugía Programada')
            ->greeting('Hola ' . $notifiable->name)
            ->line('Se ha detectado que hay equipo no disponible para una cirugía programada.')
            ->line('Detalles de la cirugía:')
            ->line('- Institución: ' . $this->surgery->institution)
            ->line('- Fecha y hora: ' . $this->surgery->surgery_date->format('d/m/Y H:i'))
            ->line('- Tipo de cirugía: ' . $this->surgery->surgery_type)
            ->line('- Doctor: ' . $this->surgery->doctor)
            ->line('- Paciente: ' . $this->surgery->patient_name)
            ->line('Equipo no disponible:');

        foreach ($this->unavailableEquipment as $equipment) {
            $mail->line(sprintf(
                '- %s (%s) - Estado actual: %s',
                $equipment->name,
                $equipment->type,
                $equipment->status
            ));

            if ($equipment->status === 'maintenance') {
                $mail->line(sprintf(
                    '  * En mantenimiento hasta: %s',
                    $equipment->next_maintenance?->format('d/m/Y') ?? 'No especificado'
                ));
            }
        }

        $mail->action('Ver Detalles de la Cirugía', route('surgeries.show', $this->surgery))
             ->line('Se requiere acción inmediata:')
             ->line('1. Verificar la disponibilidad de equipo alternativo')
             ->line('2. Considerar la reprogramación de la cirugía si es necesario')
             ->line('3. Coordinar con el personal y la institución cualquier cambio necesario');

        if ($this->surgery->surgery_date->diffInHours(now()) <= 24) {
            $mail->line('⚠️ URGENTE: La cirugía está programada para las próximas 24 horas.');
        }

        return $mail;
    }

    public function toArray($notifiable): array
    {
        return [
            'surgery_id' => $this->surgery->id,
            'message' => 'Equipo no disponible para cirugía programada',
            'institution' => $this->surgery->institution,
            'surgery_date' => $this->surgery->surgery_date->format('Y-m-d H:i:s'),
            'surgery_type' => $this->surgery->surgery_type,
            'doctor' => $this->surgery->doctor,
            'patient_name' => $this->surgery->patient_name,
            'unavailable_equipment' => $this->unavailableEquipment->map(function ($equipment) {
                return [
                    'id' => $equipment->id,
                    'name' => $equipment->name,
                    'type' => $equipment->type,
                    'status' => $equipment->status,
                    'next_maintenance' => $equipment->next_maintenance?->format('Y-m-d'),
                ];
            }),
            'urgency_level' => $this->surgery->surgery_date->diffInHours(now()) <= 24 ? 'high' : 'medium',
        ];
    }
}
