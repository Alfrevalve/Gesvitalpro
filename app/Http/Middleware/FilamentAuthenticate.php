<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FilamentAuthenticate
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Verificar si el usuario tiene acceso a Filament
        if (!$user->canAccessFilament()) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'No tienes permiso para acceder al panel de administración.');
        }

        return $next($request);
    }
}
