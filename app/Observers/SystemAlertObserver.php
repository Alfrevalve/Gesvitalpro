<?php

namespace App\Observers;

use App\Models\User;
use App\Models\SystemAlert;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SystemAlertNotification;

class SystemAlertObserver
{
    /**
     * Handle the SystemAlert "created" event.
     */
    public function created(SystemAlert $alert)
    {
        $this->notifyAdministrators($alert);
    }

    /**
     * Handle the SystemAlert "updated" event.
     */
    public function updated(SystemAlert $alert)
    {
        $this->notifyAdministrators($alert);
    }

    /**
     * Handle the SystemAlert "deleted" event.
     */
    public function deleted(SystemAlert $alert)
    {
        $this->notifyAdministrators($alert);
    }

    /**
     * Notificar a los administradores.
     */
    protected function notifyAdministrators(SystemAlert $alert): void
    {
        $admins = User::role('admin')->get();

        Notification::send($admins, new SystemAlertNotification(
            [
                'message' => $alert->message,
                'context' => $alert->metadata,
            ],
            ['mail', 'slack']
        ));
    }
}
