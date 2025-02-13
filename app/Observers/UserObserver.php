<?php

namespace App\Observers;

use App\Models\User;
use App\Models\SystemAlert;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SystemAlertNotification;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user)
    {
        $this->createAlert('info', 'user_created', "User created: {$user->name}", [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user)
    {
        $this->createAlert('info', 'user_updated', "User updated: {$user->name}", [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user)
    {
        $this->createAlert('warning', 'user_deleted', "User deleted: {$user->name}", [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Crear una alerta del sistema.
     */
    protected function createAlert(string $type, string $category, string $message, array $metadata = []): void
    {
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
