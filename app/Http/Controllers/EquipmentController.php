<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Line;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class EquipmentController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $equipment = Equipment::with('line')
            ->when(!$user->isAdmin(), function($query) use ($user) {
                $query->where('line_id', $user->line_id);
            })
            ->latest()
            ->paginate(10);

        return view('equipment.index', compact('equipment'));
    }

    public function create()
    {
        $lines = Line::all();
        return view('equipment.create', compact('lines'));
    }

    public function store(StoreEquipmentRequest $request)
    {
        try {
            $validated = $request->validated();
            $validated['status'] = 'available';
            Equipment::create($validated);

            return redirect()
                ->route('equipment.index')
                ->with('success', 'Equipo registrado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al registrar el equipo: ' . $e->getMessage());
        }
    }

    public function show(Equipment $equipment)
    {
        $equipment->load(['line', 'surgeries' => function($query) {
            $query->latest()->paginate(5);
        }]);
        
        return view('equipment.show', compact('equipment'));
    }

    public function edit(Equipment $equipment)
    {
        $lines = Line::all();
        return view('equipment.edit', compact('equipment', 'lines'));
    }

    public function update(UpdateEquipmentRequest $request, Equipment $equipment)
    {
        try {
            $validated = $request->validated();
            $equipment->update($validated);

            return redirect()
                ->route('equipment.show', $equipment)
                ->with('success', 'Equipo actualizado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al actualizar el equipo: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, Equipment $equipment)
    {
        $validated = $request->validate([
            'status' => 'required|in:available,in_use,maintenance'
        ]);

        $equipment->update(['status' => $validated['status']]);

        return redirect()
            ->route('equipment.show', $equipment)
            ->with('success', 'Estado del equipo actualizado exitosamente.');
    }

    public function maintenance()
    {
        $user = Auth::user();
        $equipment = Equipment::where(function($query) {
            $query->where('next_maintenance', '<=', now()->addDays(7))
                  ->orWhere('surgeries_count', '>=', config('surgery.maintenance.surgeries_threshold', 50));
        })
        ->when(!$user->isAdmin(), function($query) use ($user) {
            $query->where('line_id', $user->line_id);
        })
        ->with('line')
        ->latest()
        ->paginate(10);

        return view('equipment.maintenance', compact('equipment'));
    }

    public function destroy(Equipment $equipment)
    {
        if ($equipment->status !== 'available') {
            return redirect()
                ->route('equipment.show', $equipment)
                ->with('error', 'No se puede eliminar un equipo que está en uso o en mantenimiento.');
        }

        $equipment->delete();

        return redirect()
            ->route('equipment.index')
            ->with('success', 'Equipo eliminado exitosamente.');
    }
}
