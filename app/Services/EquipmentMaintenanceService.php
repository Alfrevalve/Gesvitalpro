<?php

namespace App\Services;

use App\Models\Equipment;
use App\Models\Surgery;
use App\Models\User;
use App\Notifications\EquipmentMaintenanceDue;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EquipmentMaintenanceService
{
    /**
     * Obtiene los equipos que requieren mantenimiento
     */
    public function getEquipmentNeedingMaintenance(): Collection
    {
        $warningThreshold = config('surgery.maintenance.warning_threshold');
        $surgeriesThreshold = config('surgery.maintenance.surgeries_threshold');

        return Equipment::where(function ($query) use ($warningThreshold, $surgeriesThreshold) {
            $query->where('next_maintenance', '<=', now()->addDays($warningThreshold))
                  ->orWhere('surgeries_count', '>=', $surgeriesThreshold);
        })
        ->where('status', '!=', 'maintenance')
        ->with('line')
        ->get();
    }

    /**
     * Programa el mantenimiento de un equipo
     */
    public function scheduleMaintenance(Equipment $equipment, Carbon $maintenanceDate): void
    {
        DB::transaction(function () use ($equipment, $maintenanceDate) {
            // Verificar cirugías programadas
            $conflictingSurgeries = Surgery::whereHas('equipment', function ($query) use ($equipment) {
                $query->where('equipment.id', $equipment->id);
            })
            ->where('surgery_date', '>=', now())
            ->where('status', 'programmed')
            ->get();

            if ($conflictingSurgeries->isNotEmpty()) {
                $this->handleConflictingSurgeries($equipment, $conflictingSurgeries);
            }

            // Actualizar el equipo
            $equipment->update([
                'status' => 'maintenance',
                'last_maintenance' => now(),
                'next_maintenance' => $this->calculateNextMaintenance($equipment),
            ]);

            // Registrar el mantenimiento
            Log::info('Mantenimiento programado', [
                'equipment_id' => $equipment->id,
                'name' => $equipment->name,
                'maintenance_date' => $maintenanceDate,
                'next_maintenance' => $equipment->next_maintenance,
            ]);
        });
    }

    /**
     * Maneja las cirugías que entran en conflicto con el mantenimiento
     */
    protected function handleConflictingSurgeries(Equipment $equipment, Collection $surgeries): void
    {
        foreach ($surgeries as $surgery) {
            // Buscar equipo alternativo disponible
            $alternativeEquipment = Equipment::where('line_id', $equipment->line_id)
                ->where('id', '!=', $equipment->id)
                ->where('type', $equipment->type)
                ->where('status', 'available')
                ->first();

            if ($alternativeEquipment) {
                // Reemplazar el equipo en la cirugía
                $surgery->equipment()->detach($equipment->id);
                $surgery->equipment()->attach($alternativeEquipment->id);

                Log::info('Equipo reemplazado en cirugía', [
                    'surgery_id' => $surgery->id,
                    'old_equipment' => $equipment->id,
                    'new_equipment' => $alternativeEquipment->id,
                ]);
            } else {
                // Notificar que no hay equipo alternativo disponible
                $this->notifyNoAlternativeEquipment($surgery, $equipment);
            }
        }
    }

    /**
     * Calcula la próxima fecha de mantenimiento
     */
    protected function calculateNextMaintenance(Equipment $equipment): Carbon
    {
        $maintenanceConfig = config('surgery.maintenance.interval');
        
        // Si el equipo tiene uso frecuente, usar el intervalo para uso frecuente
        if ($equipment->surgeries_count >= config('surgery.maintenance.surgeries_threshold')) {
            $interval = $maintenanceConfig['high_usage'];
        } else {
            $interval = $maintenanceConfig['default'];
        }

        return now()->addDays($interval);
    }

    /**
     * Notifica sobre la falta de equipo alternativo
     */
    protected function notifyNoAlternativeEquipment(Surgery $surgery, Equipment $equipment): void
    {
        // Notificar al jefe de línea
        $lineManager = User::where('line_id', $equipment->line_id)
            ->where('role', 'line_manager')
            ->first();

        if ($lineManager) {
            $lineManager->notify(new EquipmentMaintenanceDue($equipment));
        }

        // Registrar en el log
        Log::warning('No hay equipo alternativo disponible', [
            'surgery_id' => $surgery->id,
            'equipment_id' => $equipment->id,
            'line_id' => $equipment->line_id,
        ]);
    }

    /**
     * Completa el mantenimiento de un equipo
     */
    public function completeMaintenance(Equipment $equipment, array $data = []): void
    {
        DB::transaction(function () use ($equipment, $data) {
            $equipment->update([
                'status' => 'available',
                'last_maintenance' => now(),
                'next_maintenance' => $this->calculateNextMaintenance($equipment),
                'surgeries_count' => 0, // Reiniciar contador de cirugías
            ]);

            // Registrar notas de mantenimiento si se proporcionan
            if (!empty($data['notes'])) {
                Log::info('Mantenimiento completado', [
                    'equipment_id' => $equipment->id,
                    'name' => $equipment->name,
                    'notes' => $data['notes'],
                    'next_maintenance' => $equipment->next_maintenance,
                ]);
            }
        });
    }

    /**
     * Verifica si un equipo necesita mantenimiento
     */
    public function needsMaintenance(Equipment $equipment): bool
    {
        $warningThreshold = config('surgery.maintenance.warning_threshold');
        $surgeriesThreshold = config('surgery.maintenance.surgeries_threshold');

        return $equipment->next_maintenance <= now()->addDays($warningThreshold) ||
               $equipment->surgeries_count >= $surgeriesThreshold;
    }
}
