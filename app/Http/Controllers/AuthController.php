<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Services\ValidationService;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $validator = (new ValidationService())->validate($request->all(), 'login');
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            // Autenticación exitosa
            return redirect()->intended('configuraciones')
                           ->with('success', __('auth.login_success'));
        }

        // Si falla, redirigir de nuevo al formulario de inicio de sesión con un mensaje específico
        return back()->withErrors([
            'email' => __('auth.failed'), // Custom message for failed login
        ])->withInput($request->only('email'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('auth/login');
    }

    public function register(Request $request)
    {
        // Validate the request data
        $validator = (new ValidationService())->validate($request->all(), 'user');
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $validatedData = $validator->validated();

        // Create a new user
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
            'settings' => [],
            'date_of_birth' => $validatedData['date_of_birth'] ?? null,
            'gender' => $validatedData['gender'] ?? null,
            'contact_info' => $validatedData['contact_info'] ?? null
        ]);

        // Asignar rol por defecto
        $defaultRole = Role::where('name', 'user')->first();
        if ($defaultRole) {
            $user->assignRole($defaultRole);
        }

        // Log the user in
        Auth::login($user);
        $request->session()->regenerate();

        // Redirect to the intended page
        return redirect()->intended('configuraciones')
                       ->with('success', __('auth.register_success'));
    }
}
