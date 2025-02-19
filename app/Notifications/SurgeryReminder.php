<?php

namespace App\Notifications;

use App\Models\Surgery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;

class SurgeryReminder extends Notification implements ShouldQueue
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
        $timeUntilSurgery = Carbon::now()->diffForHumans($this->surgery->surgery_date, [
            'parts' => 2,
            'join' => true,
        ]);

        $mail = (new MailMessage)
            ->subject('Recordatorio de Cirugía Programada')
            ->greeting('Hola ' . $notifiable->name)
            ->line('Te recordamos que tienes una cirugía programada para ' . $timeUntilSurgery . '.')
            ->line('Detalles de la cirugía:')
            ->line('- Institución: ' . $this->surgery->institution)
            ->line('- Fecha y hora: ' . $this->surgery->surgery_date->format('d/m/Y H:i'))
            ->line('- Tipo de cirugía: ' . $this->surgery->surgery_type)
            ->line('- Doctor: ' . $this->surgery->doctor)
            ->line('- Paciente: ' . $this->surgery->patient_name);

        // Agregar información del equipo asignado
        if ($this->surgery->equipment->isNotEmpty()) {
            $mail->line('Equipo asignado:');
            foreach ($this->surgery->equipment as $equipment) {
                $mail->line('- ' . $equipment->name . ' (' . $equipment->type . ')');
                
                // Advertir si el equipo no está disponible
                if ($equipment->status !== 'available') {
                    $mail->line('  ⚠️ ATENCIÓN: Este equipo está marcado como ' . $equipment->status)
                         ->line('  Por favor, verifica su disponibilidad antes de la cirugía.');
                }
            }
        }

        // Agregar información del personal asignado
        if ($this->surgery->staff->isNotEmpty()) {
            $mail->line('Personal asignado:');
            foreach ($this->surgery->staff as $staff) {
                $mail->line('- ' . $staff->name);
            }
        }

        $mail->action('Ver Detalles de la Cirugía', route('surgeries.show', $this->surgery))
             ->line('Por favor, asegúrate de estar presente con suficiente anticipación.')
             ->line('Si existe algún inconveniente, comunícalo inmediatamente a tu supervisor.');

        return $mail;
    }

    public function toArray($notifiable): array
    {
        return [
            'surgery_id' => $this->surgery->id,
            'message' => 'Recordatorio de cirugía programada',
            'institution' => $this->surgery->institution,
            'surgery_date' => $this->surgery->surgery_date->format('Y-m-d H:i:s'),
            'surgery_type' => $this->surgery->surgery_type,
            'doctor' => $this->surgery->doctor,
            'patient_name' => $this->surgery->patient_name,
            'equipment' => $this->surgery->equipment->map(function ($equipment) {
                return [
                    'id' => $equipment->id,
                    'name' => $equipment->name,
                    'type' => $equipment->type,
                    'status' => $equipment->status,
                ];
            }),
            'staff' => $this->surgery->staff->map(function ($staff) {
                return [
                    'id' => $staff->id,
                    'name' => $staff->name,
                ];
            }),
        ];
    }
}
