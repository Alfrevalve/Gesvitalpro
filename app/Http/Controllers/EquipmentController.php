<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    public function index()
    {
        $equipments = Equipment::all();
        return view('equipments.index', compact('equipments'));
    }

    public function create()
    {
        return view('equipments.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'type' => 'required|max:255',
            'serial_number' => 'required|unique:equipments|max:255',
            'line_id' => 'required',
            'status' => 'required',
        ]);

        Equipment::create($request->all());
        return redirect()->route('equipments.index')->with('success', 'Equipo creado exitosamente.');
    }

    public function edit(Equipment $equipment)
    {
        return view('equipments.edit', compact('equipment'));
    }

    public function update(Request $request, Equipment $equipment)
    {
        $request->validate([
            'name' => 'required|max:255',
            'type' => 'required|max:255',
            'serial_number' => 'required|max:255|unique:equipments,serial_number,' . $equipment->id,
            'line_id' => 'required',
            'status' => 'required',
        ]);

        $equipment->update($request->all());
        return redirect()->route('equipments.index')->with('success', 'Equipo actualizado exitosamente.');
    }

    public function destroy(Equipment $equipment)
    {
        $equipment->delete();
        return redirect()->route('equipments.index')->with('success', 'Equipo eliminado exitosamente.');
    }
}
