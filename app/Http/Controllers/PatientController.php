<?php

namespace App\Http\Controllers;

use App\Models\Paciente; 
use Illuminate\Http\Request;
use App\Services\ValidationService;

class PatientController extends Controller
{
    protected $validationService;

    public function __construct(ValidationService $validationService)
    {
        $this->validationService = $validationService;
    }

    public function index()
    {
        $pacientes = Paciente::paginate(10);

        if ($pacientes->isEmpty()) {
            return view('pacientes.index'); // Cambiar a vista en lugar de JSON
        }

        return response()->json($pacientes);
    }

    public function store(Request $request)
    {
        $validatedData = $this->validationService->validate($request->all(), 'paciente')->validate(); // Usar el servicio para validar

        // Saneamiento adicional
        $validatedData['name'] = filter_var($validatedData['name'], FILTER_SANITIZE_STRING);
        $validatedData['email'] = filter_var($validatedData['email'], FILTER_SANITIZE_EMAIL);

        try {
            $paciente = Paciente::create($validatedData);
            return response()->json(['message' => 'Paciente creado con éxito.'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al crear el paciente.'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validatedData = $this->validationService->validate($request->all(), 'paciente')->validate(); // Usar el servicio para validar

        // Saneamiento adicional
        $validatedData['name'] = filter_var($validatedData['name'], FILTER_SANITIZE_STRING);
        $validatedData['email'] = filter_var($validatedData['email'], FILTER_SANITIZE_EMAIL);

        try {
            $paciente = Paciente::findOrFail($id);
            $paciente->update($validatedData);
            return response()->json(['message' => 'Paciente actualizado con éxito.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al actualizar el paciente.'], 500);
        }
    }

    public function create()
    {
        return view('pacientes.create'); // Retornar la vista de creación
    }

    public function edit($id)
    {
        $paciente = Paciente::findOrFail($id);
        return response()->json($paciente);
    }

    public function destroy($id)
    {
        $paciente = Paciente::findOrFail($id);
        $paciente->delete();

        return response()->json(['message' => 'Paciente eliminado con éxito.'], 200);
    }
}
