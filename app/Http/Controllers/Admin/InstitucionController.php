<?php

namespace App\Http\Controllers\Admin;

use App\Models\Institucion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreInstitucionRequest;
use App\Http\Requests\UpdateInstitucionRequest;

class InstitucionController extends Controller
{
    /**
     * Display a listing of institutions.
     */
    public function index()
    {
        $instituciones = Institucion::with(['medicos'])
            ->orderBy('name')
            ->paginate(10);

        return view('admin.instituciones.index', compact('instituciones'));
    }

    /**
     * Show the form for creating a new institution.
     */
    public function create()
    {
        return view('admin.instituciones.create');
    }

    /**
     * Store a newly created institution.
     */
    public function store(StoreInstitucionRequest $request)
    {
        $institucion = Institucion::create($request->validated());

        return redirect()
            ->route('admin.instituciones.index')
            ->with('success', 'Institución creada exitosamente.');
    }

    /**
     * Display the specified institution.
     */
    public function show(Institucion $institucion)
    {
        $institucion->load(['medicos', 'surgeries']);

        return view('admin.instituciones.show', compact('institucion'));
    }

    /**
     * Show the form for editing the specified institution.
     */
    public function edit(Institucion $institucion)
    {
        return view('admin.instituciones.edit', compact('institucion'));
    }

    /**
     * Update the specified institution.
     */
    public function update(UpdateInstitucionRequest $request, Institucion $institucion)
    {
        $institucion->update($request->validated());

        return redirect()
            ->route('admin.instituciones.index')
            ->with('success', 'Institución actualizada exitosamente.');
    }

    /**
     * Remove the specified institution.
     */
    public function destroy(Institucion $institucion)
    {
        if ($institucion->surgeries()->exists()) {
            return redirect()
                ->route('admin.instituciones.index')
                ->with('error', 'No se puede eliminar la institución porque tiene cirugías asociadas.');
        }

        if ($institucion->medicos()->exists()) {
            return redirect()
                ->route('admin.instituciones.index')
                ->with('error', 'No se puede eliminar la institución porque tiene médicos asociados.');
        }

        $institucion->delete();

        return redirect()
            ->route('admin.instituciones.index')
            ->with('success', 'Institución eliminada exitosamente.');
    }

    /**
     * Display and manage institution staff.
     */
    public function staff(Institucion $institucion)
    {
        $institucion->load(['medicos', 'medicos.specialties']);

        return view('admin.instituciones.staff', compact('institucion'));
    }

    /**
     * Update institution status.
     */
    public function toggleStatus(Institucion $institucion)
    {
        if ($institucion->surgeries()->where('status', 'scheduled')->exists()) {
            return redirect()
                ->route('admin.instituciones.index')
                ->with('error', 'No se puede desactivar la institución porque tiene cirugías programadas.');
        }

        $institucion->update([
            'is_active' => !$institucion->is_active
        ]);

        $status = $institucion->is_active ? 'activada' : 'desactivada';
        return redirect()
            ->route('admin.instituciones.index')
            ->with('success', "Institución {$status} exitosamente.");
    }

    /**
     * Export institution data.
     */
    public function export()
    {
        $instituciones = Institucion::with(['medicos'])
            ->get()
            ->map(function ($item) {
                return [
                    'ID' => $item->id,
                    'Nombre' => $item->name,
                    'Dirección' => $item->address,
                    'Teléfono' => $item->phone,
                    'Email' => $item->email,
                    'Estado' => $item->is_active ? 'Activo' : 'Inactivo',
                    'Médicos' => $item->medicos->count(),
                    'Cirugías' => $item->surgeries->count(),
                ];
            });

        return response()->json($instituciones);
    }

    /**
     * Update institution location data.
     */
    public function updateLocation(Request $request, Institucion $institucion)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
        ]);

        $institucion->update($validated);

        return redirect()
            ->route('admin.instituciones.show', $institucion)
            ->with('success', 'Ubicación actualizada exitosamente.');
    }
}
