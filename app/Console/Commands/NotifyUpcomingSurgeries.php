<?php

namespace App\Console\Commands;

use App\Models\Surgery;
use App\Models\User;
use App\Notifications\SurgeryReminder;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class NotifyUpcomingSurgeries extends Command
{
    protected $signature = 'surgeries:notify-upcoming';
    protected $description = 'Envía notificaciones sobre las cirugías programadas para las próximas 24 horas';

    public function handle()
    {
        $this->info('Iniciando notificación de cirugías próximas...');

        $tomorrow = Carbon::tomorrow();
        $upcomingSurgeries = Surgery::where('status', 'programmed')
            ->whereBetween('surgery_date', [
                Carbon::now(),
                $tomorrow->copy()->endOfDay()
            ])
            ->with(['staff', 'line', 'equipment'])
            ->get();

        if ($upcomingSurgeries->isEmpty()) {
            $this->info('No hay cirugías programadas para las próximas 24 horas.');
            return;
        }

        $this->info(sprintf(
            'Se encontraron %d cirugías programadas para las próximas 24 horas.',
            $upcomingSurgeries->count()
        ));

        foreach ($upcomingSurgeries as $surgery) {
            $this->processSurgery($surgery);
        }

        $this->info('Proceso de notificación completado.');
    }

    protected function processSurgery(Surgery $surgery)
    {
        $this->line(sprintf(
            'Procesando cirugía: %s en %s (Fecha: %s)',
            $surgery->surgery_type,
            $surgery->institution,
            $surgery->surgery_date->format('d/m/Y H:i')
        ));

        // Notificar al personal asignado
        foreach ($surgery->staff as $staff) {
            $this->notifyStaffMember($staff, $surgery);
        }

        // Notificar al jefe de línea
        $lineManager = User::where('line_id', $surgery->line_id)
            ->where('role', 'line_manager')
            ->first();

        if ($lineManager) {
            $this->notifyStaffMember($lineManager, $surgery);
        } else {
            $this->warn('- No se encontró jefe de línea para notificar');
        }

        // Verificar disponibilidad de equipo
        $unavailableEquipment = $surgery->equipment()
            ->where('status', '!=', 'available')
            ->get();

        if ($unavailableEquipment->isNotEmpty()) {
            $this->warn('- Se detectó equipo no disponible:');
            foreach ($unavailableEquipment as $equipment) {
                $this->warn(sprintf(
                    '  * %s (Estado: %s)',
                    $equipment->name,
                    $equipment->status
                ));
            }

            // Notificar al jefe de línea sobre equipo no disponible
            if ($lineManager) {
                $lineManager->notify(new EquipmentNotAvailable($surgery, $unavailableEquipment));
            }
        }

        // Registrar en el log
        Log::info('Notificación de cirugía próxima enviada', [
            'surgery_id' => $surgery->id,
            'type' => $surgery->surgery_type,
            'institution' => $surgery->institution,
            'date' => $surgery->surgery_date,
            'staff_notified' => $surgery->staff->pluck('id')->toArray(),
            'unavailable_equipment' => $unavailableEquipment->pluck('id')->toArray(),
        ]);
    }

    protected function notifyStaffMember(User $user, Surgery $surgery)
    {
        $user->notify(new SurgeryReminder($surgery));
        $this->line('- Notificación enviada a: ' . $user->name . ' (' . $user->role . ')');
    }
}
