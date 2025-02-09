<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Services\ValidationService;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $validatedData = (new ValidationService())->validate($request->all(), 'login');

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            // Autenticación exitosa
            return redirect()->intended('configuraciones')->with('success', __('auth.login_success'));
        }

        // Si falla, redirigir de nuevo al formulario de inicio de sesión
        return back()->withErrors($validatedData->errors());
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }

    public function register(Request $request)
    {
        // Validate the request data
        $validatedData = (new ValidationService())->validate($request->all(), 'user');

        // Create a new user
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
            'role' => $validatedData['role'],
        ]);

        // Log the user in
        Auth::login($user);

        // Redirect to the intended page
        return redirect()->intended('configuraciones')->with('success', __('auth.register_success'));
    }
}
