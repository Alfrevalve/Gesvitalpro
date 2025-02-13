<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use App\Models\SystemAlert;
use App\Models\User;
use App\Notifications\SystemAlertNotification;

class SecurityMonitoringSubscriber
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
            'App\Events\SecurityThreatDetected' => [
                [SecurityMonitoringSubscriber::class, 'handleSecurityThreat'],
            ],
            'App\Events\FailedLoginAttempt' => [
                [SecurityMonitoringSubscriber::class, 'handleFailedLogin'],
            ],
            'App\Events\SuspiciousActivityDetected' => [
                [SecurityMonitoringSubscriber::class, 'handleSuspiciousActivity'],
            ],
        ];
    }

    /**
     * Manejar amenazas de seguridad detectadas.
     */
    public function handleSecurityThreat($event)
    {
        $this->createAlert(
            'critical',
            'security_threat',
            "Security threat detected: {$event->message}",
            [
                'details' => $event->details,
            ]
        );

        $this->notifyAdministrators($event);
    }

    /**
     * Manejar intentos de inicio de sesión fallidos.
     */
    public function handleFailedLogin($event)
    {
        $this->createAlert(
            'warning',
            'failed_login',
            "Failed login attempt detected for user: {$event->username}",
            [
                'ip' => $event->ip,
                'user_agent' => $event->userAgent,
            ]
        );

        $this->notifyAdministrators($event);
    }

    /**
     * Manejar actividades sospechosas.
     */
    public function handleSuspiciousActivity($event)
    {
        $this->createAlert(
            'warning',
            'suspicious_activity',
            "Suspicious activity detected: {$event->description}",
            [
                'details' => $event->details,
            ]
        );

        $this->notifyAdministrators($event);
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
    }

    /**
     * Notificar a los administradores.
     */
    protected function notifyAdministrators($event): void
    {
        $admins = User::role('admin')->get();

        Notification::send($admins, new SystemAlertNotification(
            [
                'message' => $event->message,
                'context' => $event->details,
            ],
            ['mail', 'slack']
        ));
    }
}
