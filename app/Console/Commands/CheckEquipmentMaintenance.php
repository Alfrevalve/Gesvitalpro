<?php

namespace App\Console\Commands;

use App\Models\Equipment;
use App\Models\User;
use App\Notifications\EquipmentMaintenanceDue;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckEquipmentMaintenance extends Command
{
    protected $signature = 'equipment:check-maintenance';
    protected $description = 'Verifica equipos que requieren mantenimiento y notifica a los responsables';

    public function handle()
    {
        $this->info('Iniciando verificación de mantenimiento de equipos...');
        
        $warningThreshold = config('surgery.maintenance.warning_threshold');
        $now = Carbon::now();

        // Verificar equipos por fecha de mantenimiento próxima
        $equipmentByDate = Equipment::where('next_maintenance', '<=', $now->copy()->addDays($warningThreshold))
            ->where('status', '!=', 'maintenance')
            ->with('line')
            ->get();

        // Verificar equipos por número de cirugías
        $surgeriesThreshold = config('surgery.maintenance.surgeries_threshold');
        $equipmentByUsage = Equipment::where('surgeries_count', '>=', $surgeriesThreshold)
            ->where('status', '!=', 'maintenance')
            ->with('line')
            ->get();

        $equipmentNeedingMaintenance = $equipmentByDate->merge($equipmentByUsage)->unique('id');

        if ($equipmentNeedingMaintenance->isEmpty()) {
            $this->info('No se encontraron equipos que requieran mantenimiento.');
            return;
        }

        $this->info(sprintf(
            'Se encontraron %d equipos que requieren mantenimiento.',
            $equipmentNeedingMaintenance->count()
        ));

        foreach ($equipmentNeedingMaintenance as $equipment) {
            $this->processEquipment($equipment);
        }

        $this->info('Verificación de mantenimiento completada.');
    }

    protected function processEquipment(Equipment $equipment)
    {
        $this->line(sprintf(
            'Procesando equipo: %s (Línea: %s)',
            $equipment->name,
            $equipment->line->name
        ));

        // Notificar al jefe de línea
        $lineManager = User::where('line_id', $equipment->line_id)
            ->where('role', 'line_manager')
            ->first();

        if ($lineManager) {
            $lineManager->notify(new EquipmentMaintenanceDue($equipment));
            $this->line('- Notificación enviada al jefe de línea: ' . $lineManager->name);
        } else {
            $this->warn('- No se encontró jefe de línea para notificar');
        }

        // Notificar a los administradores
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new EquipmentMaintenanceDue($equipment));
            $this->line('- Notificación enviada al administrador: ' . $admin->name);
        }

        // Registrar en el log
        Log::info('Equipo requiere mantenimiento', [
            'equipment_id' => $equipment->id,
            'name' => $equipment->name,
            'line' => $equipment->line->name,
            'surgeries_count' => $equipment->surgeries_count,
            'last_maintenance' => $equipment->last_maintenance,
            'next_maintenance' => $equipment->next_maintenance,
        ]);
    }
}
