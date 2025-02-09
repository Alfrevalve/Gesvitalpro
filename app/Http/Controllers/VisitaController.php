<?php

namespace App\Http\Controllers;

use App\Services\ValidationService; // Importar el nuevo servicio
use App\Models\Visita;
use App\Models\Paciente; // Ensure the model is updated to Paciente
use Illuminate\Http\Request;

class VisitaController extends Controller
{
    protected $validationService;

    public function __construct(ValidationService $validationService)
    {
        $this->validationService = $validationService; // Inyectar el servicio
    }

    public function index()
    {
        $visitas = Visita::with('paciente')->paginate(10); // Eager load paciente
        return response()->json($visitas);
    }

    public function store(Request $request)
    {
        $this->validationService->validate($request->all(), 'visita')->validate(); // Usar el servicio para validar

        $visita = Visita::create([
            'patient_id' => $request->patient_id,
            'date' => $request->date,
        ]);

        return response()->json(['message' => 'Visita creada con éxito.'], 201);
    }

    public function edit($id)
    {
        $visita = Visita::findOrFail($id);
        return response()->json($visita);
    }

    public function update(Request $request, $id)
    {
        $this->validationService->validate($request->all(), 'visita')->validate(); // Usar el servicio para validar

        $visita = Visita::findOrFail($id);
        $visita->update($request->all());

        return response()->json(['message' => 'Visita actualizada con éxito.'], 200);
    }

    public function destroy($id)
    {
        $visita = Visita::findOrFail($id);
        $visita->delete();

        return response()->json(['message' => 'Visita eliminada con éxito.'], 200);
    }
}
