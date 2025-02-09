@extends('layouts.bootstrap')

@section('content')
<header class="text-center mb-4">
    <h1>Bienvenido a GesVitalPro</h1>
    <nav>
        <ul class="nav justify-content-center">
            <li class="nav-item">
                <a class="nav-link" href="{{ url('/auth/login') }}">Iniciar Sesión</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ url('/auth/register') }}">Registrarse</a>
            </li>
        </ul>
    </nav>
</header>
<main class="container">
    <h2 class="mb-4">Gestión Médica en Instituciones Hospitalarias</h2>
    <p>Este sistema está diseñado para facilitar la gestión de visitas médicas, inventario, cirugías y más.</p>

    <h3>Iniciar Sesión</h3>
    <form action="{{ url('/auth/login') }}" method="POST" class="mb-4">
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label">Correo Electrónico:</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Contraseña:</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
    </form>
</main>
@endsection
