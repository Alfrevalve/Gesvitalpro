<?php

namespace App\Notifications;

use App\Models\SurgeryRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaterialDelivered extends Notification implements ShouldQueue
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
        $deliveryTime = $this->surgeryRequest->dispatchProcess->delivery_time;
        $timeMessage = $deliveryTime ? " (Tiempo total: {$deliveryTime} minutos)" : '';

        return (new MailMessage)
            ->subject('Material Entregado - ' . $this->surgeryRequest->code)
            ->line('El material para la solicitud ' . $this->surgeryRequest->code . ' ha sido entregado.')
            ->line('Detalles de la entrega:')
            ->line('- Tipo de línea: ' . $this->surgeryRequest->line_type)
            ->line('- Entregado por: ' . $this->surgeryRequest->dispatchProcess->deliveredByUser->name)
            ->line('- Fecha de entrega: ' . $this->surgeryRequest->dispatchProcess->delivered_at->format('d/m/Y H:i') . $timeMessage)
            ->action('Ver Detalles', route('storage.show', $this->surgeryRequest->id));
    }

    public function toArray($notifiable): array
    {
        return [
            'surgery_request_id' => $this->surgeryRequest->id,
            'code' => $this->surgeryRequest->code,
            'status' => $this->surgeryRequest->status,
            'delivered_by' => $this->surgeryRequest->dispatchProcess->deliveredByUser->name,
            'delivered_at' => $this->surgeryRequest->dispatchProcess->delivered_at->toDateTimeString(),
            'delivery_time' => $this->surgeryRequest->dispatchProcess->delivery_time,
        ];
    }
}
