<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use App\Models\SystemAlert;
use App\Models\User;
use App\Notifications\SystemAlertNotification;

class AuditLogSubscriber
{
    /**
     * Registrar los listeners para el subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     * @return array
     */
    public function subscribe($events)
    {
        return [
            'App\Events\AuditLogCreated' => [
                [AuditLogSubscriber::class, 'handleAuditLogCreated'],
            ],
            'App\Events\AuditLogUpdated' => [
                [AuditLogSubscriber::class, 'handleAuditLogUpdated'],
            ],
            'App\Events\AuditLogDeleted' => [
                [AuditLogSubscriber::class, 'handleAuditLogDeleted'],
            ],
        ];
    }

    /**
     * Manejar la creación de un log de auditoría.
     */
    public function handleAuditLogCreated($event)
    {
        $this->createAlert(
            'info',
            'audit_log',
            "Audit log created: {$event->log->description}",
            [
                'log_id' => $event->log->id,
                'user_id' => $event->log->user_id,
            ]
        );
    }

    /**
     * Manejar la actualización de un log de auditoría.
     */
    public function handleAuditLogUpdated($event)
    {
        $this->createAlert(
            'info',
            'audit_log',
            "Audit log updated: {$event->log->description}",
            [
                'log_id' => $event->log->id,
                'user_id' => $event->log->user_id,
            ]
        );
    }

    /**
     * Manejar la eliminación de un log de auditoría.
     */
    public function handleAuditLogDeleted($event)
    {
        $this->createAlert(
            'warning',
            'audit_log',
            "Audit log deleted: {$event->log->description}",
            [
                'log_id' => $event->log->id,
                'user_id' => $event->log->user_id,
            ]
        );
    }

    /**
     * Crear una alerta del sistema.
     */
    protected function createAlert(
        string $type,
        string $category,
        string $message,
        array $metadata = []
    ): void {
        SystemAlert::create([
            'type' => $type,
            'category' => $category,
            'message' => $message,
            'metadata' => $metadata,
        ]);

        $this->notifyAdministrators($message);
    }

    /**
     * Notificar a los administradores.
     */
    protected function notifyAdministrators(string $message): void
    {
        $admins = User::role('admin')->get();

        Notification::send($admins, new SystemAlertNotification(
            [
                'message' => $message,
            ],
            ['mail', 'slack']
        ));
    }
}
