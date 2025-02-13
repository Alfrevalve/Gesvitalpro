<?php

namespace App\Http\Controllers;

use App\Models\Cirugia;
use App\Models\Paciente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use App\Services\ValidationService;

class CirugiaController
{
    protected $validationService;

    public function __construct(ValidationService $validationService)
    {
        $this->validationService = $validationService;
    }

    public function index(Request $request)
    {
        $perPage = $request->input('por_pagina', 10);
        $cirugias = Cirugia::with(['inventario', 'paciente'])
            ->latest()
            ->paginate($perPage);
        return view('cirugias.index', compact('cirugias'));
    }

    public function create()
    {
        $pacientes = Paciente::select('id', 'name')->get();
        return view('cirugias.crear', compact('pacientes'));
    }

    public function edit($id)
    {
        $cirugia = Cirugia::findOrFail($id);
        $pacientes = Paciente::all(); // Obtener todos los pacientes
        return view('cirugias.editar', compact('cirugia', 'pacientes'));
    }

    public function store(Request $request)
    {
        $this->validationService->validate($request->all(), 'cirugia')->validate();

        // Saneamiento adicional
        $request->merge([
            'empresa_nombre' => filter_var($request->empresa_nombre, FILTER_SANITIZE_STRING),
            'empresa_rif' => filter_var($request->empresa_rif, FILTER_SANITIZE_STRING),
            'empresa_email' => filter_var($request->empresa_email, FILTER_SANITIZE_EMAIL),
        ]);

        try {
            $cirugia = Cirugia::create($request->validated());
            return redirect()->route('cirugias.index')
                ->with('success', 'Cirugía creada con éxito.');
        } catch (QueryException $e) {
            Log::error('Error de base de datos en creación de cirugía: ' . $e->getMessage(), [
                'request' => $request->all(),
                'user_id' => Auth::id(),
            ]);
            return back()->with('error', 'Error al crear la cirugía. Por favor, intente nuevamente.');
        } catch (\Exception $e) {
            Log::error('Error general en creación de cirugía: ' . $e->getMessage());
            return back()->with('error', 'Ha ocurrido un error inesperado.');
        }
    }

    public function update(Request $request, $id)
    {
        $this->validationService->validate($request->all(), 'cirugia')->validate();

        // Saneamiento adicional
        $request->merge([
            'empresa_nombre' => filter_var($request->empresa_nombre, FILTER_SANITIZE_STRING),
            'empresa_rif' => filter_var($request->empresa_rif, FILTER_SANITIZE_STRING),
            'empresa_email' => filter_var($request->empresa_email, FILTER_SANITIZE_EMAIL),
        ]);

        try {
            $cirugia = Cirugia::findOrFail($id);
            $cirugia->update($request->validated());
            return redirect()->route('cirugias.index')
                ->with('success', 'Cirugía actualizada con éxito.');
        } catch (QueryException $e) {
            Log::error('Error de base de datos en actualización de cirugía: ' . $e->getMessage());
            return back()->with('error', 'Error al actualizar la cirugía. Por favor, intente nuevamente.');
        } catch (\Exception $e) {
            Log::error('Error general en actualización de cirugía: ' . $e->getMessage());
            return back()->with('error', 'Ha ocurrido un error inesperado.');
        }
    }

    // Resto del código...
}
