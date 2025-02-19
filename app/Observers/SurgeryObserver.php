<?php

namespace App\Observers;

use App\Models\Surgery;
use App\Models\Equipment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SurgeryStatusChanged;
use App\Notifications\SurgeryScheduled;

class SurgeryObserver
{
    /**
     * Handle the Surgery "created" event.
     */
    public function created(Surgery $surgery): void
    {
        Log::info("Nueva cirugía programada", [
            'surgery_id' => $surgery->id,
            'institution' => $surgery->institution,
            'date' => $surgery->surgery_date,
        ]);

        // Notificar al personal asignado
        if (config('surgery.notifications.events.surgery_scheduled')) {
            $surgery->staff->each(function ($user) use ($surgery) {
                $user->notify(new SurgeryScheduled($surgery));
            });
        }
    }

    /**
     * Handle the Surgery "updating" event.
     */
    public function updating(Surgery $surgery): void
    {
        // Si el estado está cambiando
        if ($surgery->isDirty('status')) {
            $oldStatus = $surgery->getOriginal('status');
            $newStatus = $surgery->status;

            Log::info("Estado de cirugía actualizado", [
                'surgery_id' => $surgery->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);

            // Notificar cambio de estado
            if (config('surgery.notifications.events.surgery_status_changed')) {
                $surgery->staff->each(function ($user) use ($surgery, $oldStatus, $newStatus) {
                    $user->notify(new SurgeryStatusChanged($surgery, $oldStatus, $newStatus));
                });
            }

            // Si la cirugía se finaliza o cancela, liberar equipos
            if (in_array($newStatus, ['finished', 'cancelled'])) {
                Equipment::whereIn('id', $surgery->equipment->pluck('id'))
                    ->update(['status' => 'available']);
            }
        }
    }

    /**
     * Handle the Surgery "deleted" event.
     */
    public function deleted(Surgery $surgery): void
    {
        Log::info("Cirugía eliminada", [
            'surgery_id' => $surgery->id,
            'institution' => $surgery->institution,
            'date' => $surgery->surgery_date,
        ]);

        // Liberar equipos asociados
        Equipment::whereIn('id', $surgery->equipment->pluck('id'))
            ->update(['status' => 'available']);
    }

    /**
     * Handle the Surgery "restored" event.
     */
    public function restored(Surgery $surgery): void
    {
        Log::info("Cirugía restaurada", [
            'surgery_id' => $surgery->id,
            'institution' => $surgery->institution,
            'date' => $surgery->surgery_date,
        ]);
    }

    /**
     * Handle the Surgery "force deleted" event.
     */
    public function forceDeleted(Surgery $surgery): void
    {
        Log::info("Cirugía eliminada permanentemente", [
            'surgery_id' => $surgery->id,
            'institution' => $surgery->institution,
            'date' => $surgery->surgery_date,
        ]);
    }
}
