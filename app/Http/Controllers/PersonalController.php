<?php

namespace App\Http\Controllers;

use App\Models\Personal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\ValidationService;

class PersonalController extends Controller
{
    protected $validationService;

    public function __construct(ValidationService $validationService)
    {
        $this->validationService = $validationService;
    }

    public function index(Request $request)
    {
        $perPage = $request->input('por_pagina', 10); // Permitir personalización del tamaño de la paginación
        $personales = Personal::with('relacion')->paginate($perPage); // Implementar eager loading
        return response()->json($personales); // Estandarizar respuesta a JSON
    }

    public function create()
    {
        return view('personales.crear');
    }

    public function store(Request $request)
    {
        $this->validationService->validate($request->all(), 'personal')->validate(); // Usar el servicio para validar

        // Verificar autorización
        if (!Auth::user()->can('create', Personal::class)) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        Personal::create($request->all());
        return response()->json(['message' => 'Personal creado con éxito.'], 201);
    }

    public function edit(Personal $personal)
    {
        return view('personales.editar', compact('personal'));
    }

    public function update(Request $request, Personal $personal)
    {
        $this->validationService->validate($request->all(), 'personal')->validate(); // Usar el servicio para validar

        // Verificar autorización
        if (!Auth::user()->can('update', $personal)) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $personal->update($request->all());
        return response()->json(['message' => 'Personal actualizado con éxito.'], 200);
    }

    public function destroy(Personal $personal)
    {
        // Verificar autorización
        if (!Auth::user()->can('delete', $personal)) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $personal->delete();
        return response()->json(['message' => 'Personal eliminado con éxito.'], 200);
    }
}
