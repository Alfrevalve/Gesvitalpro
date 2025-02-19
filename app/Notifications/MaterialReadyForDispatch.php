<?php

namespace App\Notifications;

use App\Models\SurgeryRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaterialReadyForDispatch extends Notification implements ShouldQueue
{
    use Queueable;

    protected $surgeryRequest;

    public function __construct(SurgeryRequest $surgeryRequest)
    {
        $this->surgeryRequest = $surgeryRequest;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Material Listo para Despacho - ' . $this->surgeryRequest->code)
            ->line('El material para la solicitud ' . $this->surgeryRequest->code . ' está listo para ser despachado.')
            ->line('Detalles de la solicitud:')
            ->line('- Tipo de línea: ' . $this->surgeryRequest->line_type)
            ->line('- Preparado por: ' . $this->surgeryRequest->storageProcess->preparedByUser->name)
            ->line('- Fecha de preparación: ' . $this->surgeryRequest->storageProcess->prepared_at->format('d/m/Y H:i'))
            ->action('Ver Detalles', route('dispatch.show', $this->surgeryRequest->id));
    }

    public function toArray($notifiable): array
    {
        return [
            'surgery_request_id' => $this->surgeryRequest->id,
            'code' => $this->surgeryRequest->code,
            'status' => $this->surgeryRequest->status,
            'prepared_by' => $this->surgeryRequest->storageProcess->preparedByUser->name,
            'prepared_at' => $this->surgeryRequest->storageProcess->prepared_at->toDateTimeString(),
        ];
    }
}
