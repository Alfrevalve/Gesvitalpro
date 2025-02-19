<?php

namespace App\Http\Controllers;

use App\Models\Externo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExternoController extends Controller
{
    public function index()
    {
        $externos = Externo::with(['institucion'])
            ->orderBy('nombre')
            ->paginate(10);

        return view('externos.index', compact('externos'));
    }

    public function create()
    {
        return view('externos.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:externos',
            'telefono' => 'required|string|max:20',
            'especialidad' => 'required|string|max:100',
            'institucion_id' => 'required|exists:instituciones,id',
            'notas' => 'nullable|string',
        ]);

        $externo = Externo::create($validated);

        return redirect()->route('externos.index')
            ->with('success', 'Personal externo registrado exitosamente.');
    }

    public function edit(Externo $externo)
    {
        return view('externos.edit', compact('externo'));
    }

    public function update(Request $request, Externo $externo)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:externos,email,' . $externo->id,
            'telefono' => 'required|string|max:20',
            'especialidad' => 'required|string|max:100',
            'institucion_id' => 'required|exists:instituciones,id',
            'notas' => 'nullable|string',
        ]);

        $externo->update($validated);

        return redirect()->route('externos.index')
            ->with('success', 'Información del personal externo actualizada exitosamente.');
    }

    public function destroy(Externo $externo)
    {
        // Verificar si hay dependencias antes de eliminar
        if ($externo->visitas()->exists() || $externo->surgeries()->exists()) {
            return redirect()->route('externos.index')
                ->with('error', 'No se puede eliminar el personal externo porque tiene registros asociados.');
        }

        $externo->delete();

        return redirect()->route('externos.index')
            ->with('success', 'Personal externo eliminado exitosamente.');
    }

    public function getByInstitucion($institucionId)
    {
        $externos = Externo::where('institucion_id', $institucionId)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'especialidad']);

        return response()->json($externos);
    }

    public function search(Request $request)
    {
        $query = $request->get('q');

        $externos = Externo::where('nombre', 'like', "%{$query}%")
            ->orWhere('especialidad', 'like', "%{$query}%")
            ->with('institucion')
            ->limit(10)
            ->get(['id', 'nombre', 'especialidad', 'institucion_id']);

        return response()->json($externos);
    }
}
