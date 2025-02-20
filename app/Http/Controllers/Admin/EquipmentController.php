<?php

namespace App\Http\Controllers\Admin;

use App\Models\Equipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreEquipmentRequest;
use App\Http\Requests\UpdateEquipmentRequest;

class EquipmentController extends Controller
{
    /**
     * Display a listing of equipment.
     */
    public function index()
    {
        $equipment = Equipment::with(['maintenanceRecords'])
            ->orderBy('next_maintenance_date')
            ->paginate(10);

        return view('admin.equipment.index', compact('equipment'));
    }

    /**
     * Show the form for creating new equipment.
     */
    public function create()
    {
        return view('admin.equipment.create');
    }

    /**
     * Store newly created equipment.
     */
    public function store(StoreEquipmentRequest $request)
    {
        $equipment = Equipment::create($request->validated());

        return redirect()
            ->route('admin.equipment.index')
            ->with('success', 'Equipo creado exitosamente.');
    }

    /**
     * Display specified equipment.
     */
    public function show(Equipment $equipment)
    {
        $equipment->load(['maintenanceRecords', 'surgeries']);

        return view('admin.equipment.show', compact('equipment'));
    }

    /**
     * Show the form for editing equipment.
     */
    public function edit(Equipment $equipment)
    {
        return view('admin.equipment.edit', compact('equipment'));
    }

    /**
     * Update specified equipment.
     */
    public function update(UpdateEquipmentRequest $request, Equipment $equipment)
    {
        $equipment->update($request->validated());

        return redirect()
            ->route('admin.equipment.index')
            ->with('success', 'Equipo actualizado exitosamente.');
    }

    /**
     * Remove specified equipment.
     */
    public function destroy(Equipment $equipment)
    {
        if ($equipment->surgeries()->exists()) {
            return redirect()
                ->route('admin.equipment.index')
                ->with('error', 'No se puede eliminar el equipo porque tiene cirugías asociadas.');
        }

        $equipment->delete();

        return redirect()
            ->route('admin.equipment.index')
            ->with('success', 'Equipo eliminado exitosamente.');
    }

    /**
     * Record maintenance for equipment.
     */
    public function maintenance(Request $request, Equipment $equipment)
    {
        $validated = $request->validate([
            'maintenance_date' => 'required|date',
            'description' => 'required|string|max:500',
            'next_maintenance_date' => 'required|date|after:maintenance_date',
            'status' => 'required|in:completed,pending,in_progress',
            'technician' => 'required|string|max:100',
            'cost' => 'nullable|numeric|min:0',
        ]);

        $equipment->maintenanceRecords()->create($validated);

        $equipment->update([
            'last_maintenance_date' => $validated['maintenance_date'],
            'next_maintenance_date' => $validated['next_maintenance_date'],
            'status' => $validated['status'] === 'completed' ? 'available' : 'maintenance',
        ]);

        return redirect()
            ->route('admin.equipment.show', $equipment)
            ->with('success', 'Mantenimiento registrado exitosamente.');
    }

    /**
     * Toggle equipment status.
     */
    public function toggleStatus(Equipment $equipment)
    {
        if ($equipment->surgeries()->where('status', 'in_progress')->exists()) {
            return redirect()
                ->route('admin.equipment.index')
                ->with('error', 'No se puede cambiar el estado del equipo porque está en uso.');
        }

        $equipment->update([
            'status' => $equipment->status === 'available' ? 'unavailable' : 'available'
        ]);

        return redirect()
            ->route('admin.equipment.index')
            ->with('success', 'Estado del equipo actualizado exitosamente.');
    }

    /**
     * Export equipment data.
     */
    public function export()
    {
        $equipment = Equipment::with(['maintenanceRecords'])
            ->get()
            ->map(function ($item) {
                return [
                    'ID' => $item->id,
                    'Nombre' => $item->name,
                    'Modelo' => $item->model,
                    'Serial' => $item->serial_number,
                    'Estado' => $item->status,
                    'Último Mantenimiento' => $item->last_maintenance_date,
                    'Próximo Mantenimiento' => $item->next_maintenance_date,
                    'Registros de Mantenimiento' => $item->maintenanceRecords->count(),
                ];
            });

        return response()->json($equipment);
    }
}
