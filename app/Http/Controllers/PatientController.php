<?php

namespace App\Http\Controllers;

use App\Models\Paciente; 
use Illuminate\Http\Request;
use App\Services\ValidationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;

class PatientController extends Controller
{
    protected $validationService;

    const HTTP_OK = 200;
    const HTTP_CREATED = 201;
    const HTTP_INTERNAL_SERVER_ERROR = 500;

    public function __construct(ValidationService $validationService)
    {
        $this->validationService = $validationService;
    }

    public function index()
    {
        $pacientes = Paciente::paginate(10);

        return response()->json($pacientes->isEmpty() ? [] : $pacientes);
    }

    public function store(Request $request)
    {
        $validatedData = $this->validateAndSanitize($request);

        try {
            $paciente = Paciente::create($validatedData);
            return response()->json(['message' => 'Paciente creado con éxito.'], self::HTTP_CREATED);
        } catch (QueryException $e) {
            return $this->logError($e, 'Error de base de datos al crear el paciente', $request);
        } catch (\Exception $e) {
            return $this->logError($e, 'Error al crear el paciente', $request);
        }
    }

    public function update(Request $request, $id)
    {
        $validatedData = $this->validateAndSanitize($request);

        try {
            $paciente = Paciente::findOrFail($id);
            $paciente->update($validatedData);
            return response()->json(['message' => 'Paciente actualizado con éxito.'], self::HTTP_OK);
        } catch (QueryException $e) {
            return $this->logError($e, 'Error de base de datos al actualizar el paciente', $request);
        } catch (\Exception $e) {
            return $this->logError($e, 'Error al actualizar el paciente', $request);
        }
    }

    public function create()
    {
        return view('pacientes.create');
    }

    public function edit($id)
    {
        $paciente = Paciente::findOrFail($id);
        return response()->json($paciente);
    }

    public function destroy($id)
    {
        try {
            $paciente = Paciente::findOrFail($id);
            $paciente->delete();
            return response()->json(['message' => 'Paciente eliminado con éxito.'], self::HTTP_OK);
        } catch (QueryException $e) {
            return $this->logError($e, 'Error de base de datos al eliminar el paciente');
        } catch (\Exception $e) {
            return $this->logError($e, 'Error al eliminar el paciente');
        }
    }

    private function logError($exception, $message, Request $request = null)
    {
        Log::error($message . ': ' . $exception->getMessage(), [
            'request' => $request ? $request->all() : [],
            'user_id' => Auth::id(),
        ]);
        return response()->json(['message' => $message], self::HTTP_INTERNAL_SERVER_ERROR);
    }

    private function sanitizeInput(array $data)
    {
        $data['name'] = filter_var($data['name'], FILTER_SANITIZE_STRING);
        $data['email'] = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
        return $data;
    }
}
