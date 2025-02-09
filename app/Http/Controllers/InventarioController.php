<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\ValidationService;

class InventarioController
{
    protected $validationService;

    public function __construct(ValidationService $validationService)
    {
        $this->validationService = $validationService;
    }

    public function index(Request $request)
    {
        $perPage = $request->input('por_pagina', 10); // Permitir personalización del tamaño de la paginación
        $inventarios = Inventario::with('relacion')->paginate($perPage); // Implementar eager loading
        return view('inventarios.index', compact('inventarios')); // Cambiar a vista en lugar de JSON
    }

    public function create()
    {
        return view('inventarios.crear');
    }

    public function store(Request $request)
    {
        $validatedData = $this->validationService->validate($request->all(), 'inventario')->validate(); // Usar el servicio para validar

        // Verificar autorización
        if (!Auth::user()->can('create', Inventario::class)) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $inventario = Inventario::create($validatedData);

        // Verificar stock mínimo
        if ($inventario->quantity <= $inventario->nivel_minimo) {
            // Aquí se puede implementar la lógica de notificación
            session()->flash('alert', 'Alerta: La cantidad está por debajo del stock mínimo.');
        }
        return response()->json(['message' => 'Inventario creado con éxito.'], 201);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        // Lógica para importar el archivo Excel
        Excel::import(new InventarioImport, $request->file('file'));

        return redirect()->route('inventarios.index')->with('alert', 'Inventarios importados con éxito.');
    }

    public function edit(Inventario $inventario)
    {
        return view('inventarios.editar', compact('inventario'));
    }

    public function update(Request $request, Inventario $inventario)
    {
        $validatedData = $this->validationService->validate($request->all(), 'inventario')->validate(); // Usar el servicio para validar

        // Verificar autorización
        if (!Auth::user()->can('update', $inventario)) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $inventario->update($validatedData);

        // Verificar stock mínimo
        if ($inventario->quantity <= $inventario->nivel_minimo) {
            // Aquí se puede implementar la lógica de notificación
            session()->flash('alert', 'Alerta: La cantidad está por debajo del stock mínimo.');
        }
        return response()->json(['message' => 'Inventario actualizado con éxito.'], 200);
    }

    public function destroy(Inventario $inventario)
    {
        // Verificar autorización
        if (!Auth::user()->can('delete', $inventario)) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $inventario->delete();
        return response()->json(['message' => 'Inventario eliminado con éxito.'], 200);
    }
}
