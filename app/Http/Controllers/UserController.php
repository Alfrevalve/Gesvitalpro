<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Services\ValidationService;

class UserController extends Controller
{
    protected $validationService;

    public function __construct(ValidationService $validationService)
    {
        $this->validationService = $validationService;
    }

    public function index()
    {
        $usuarios = User::all(); // Recuperar todos los usuarios
        return view('user_management', compact('usuarios')); // Pasar usuarios a la vista
    }

    public function create()
    {
        return view('user_management.create'); // Retornar la vista del formulario de creación de usuario
    }

    public function store(Request $request)
    {
        $validatedData = $this->validationService->validate($request->all(), 'user')->validate(); // Usar el servicio para validar

        // Saneamiento adicional
        $validatedData['name'] = filter_var($validatedData['name'], FILTER_SANITIZE_STRING);
        $validatedData['email'] = filter_var($validatedData['email'], FILTER_SANITIZE_EMAIL);

        User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
        ]);

        return redirect()->route('user.management')->with('success', 'Usuario creado con éxito.');
    }

    public function edit($id)
    {
        $usuario = User::findOrFail($id); // Recuperar el usuario
        return view('user_management.edit', compact('usuario')); // Retornar la vista del formulario de edición
    }

    public function update(Request $request, $id)
    {
        $this->validationService->validate($request->all(), 'user')->validate(); // Usar el servicio para validar

        // Saneamiento adicional
        $request->merge([
            'name' => filter_var($request->name, FILTER_SANITIZE_STRING),
            'email' => filter_var($request->email, FILTER_SANITIZE_EMAIL),
        ]);

        $usuario = User::findOrFail($id); // Recuperar el usuario
        $usuario->update($request->all());

        return redirect()->route('user.management')->with('success', 'Usuario actualizado con éxito.');
    }

    public function destroy($id)
    {
        $usuario = User::findOrFail($id); // Recuperar el usuario
        $usuario->delete(); // Eliminar el usuario

        return redirect()->route('user.management')->with('success', 'Usuario eliminado con éxito.');
    }
}
