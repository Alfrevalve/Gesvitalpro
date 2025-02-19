<?php

namespace App\Observers;

use App\Models\Equipment;
use Illuminate\Support\Facades\Log;

class EquipmentObserver
{
    /**
     * Handle the Equipment "created" event.
     */
    public function created(Equipment $equipment): void
    {
        Log::info("Nuevo equipo registrado", [
            'equipment_id' => $equipment->id,
            'name' => $equipment->name,
            'serial_number' => $equipment->serial_number,
            'line' => $equipment->line ? $equipment->line->name : 'Sin línea asignada'
        ]);
    }

    /**
     * Handle the Equipment "updated" event.
     */
    public function updated(Equipment $equipment): void
    {
        Log::info("Equipo actualizado", [
            'equipment_id' => $equipment->id,
            'name' => $equipment->name,
            'serial_number' => $equipment->serial_number,
            'line' => $equipment->line ? $equipment->line->name : 'Sin línea asignada',
            'changes' => $equipment->getDirty()
        ]);
    }

    /**
     * Handle the Equipment "deleted" event.
     */
    public function deleted(Equipment $equipment): void
    {
        Log::info("Equipo eliminado", [
            'equipment_id' => $equipment->id,
            'name' => $equipment->name,
            'serial_number' => $equipment->serial_number
        ]);
    }

    /**
     * Handle the Equipment "restored" event.
     */
    public function restored(Equipment $equipment): void
    {
        Log::info("Equipo restaurado", [
            'equipment_id' => $equipment->id,
            'name' => $equipment->name,
            'serial_number' => $equipment->serial_number
        ]);
    }

    /**
     * Handle the Equipment "force deleted" event.
     */
    public function forceDeleted(Equipment $equipment): void
    {
        Log::info("Equipo eliminado permanentemente", [
            'equipment_id' => $equipment->id,
            'name' => $equipment->name,
            'serial_number' => $equipment->serial_number
        ]);
    }
}
