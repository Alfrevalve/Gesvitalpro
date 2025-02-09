<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GesVitalPro - Sistema de Gestión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/">GesVitalPro</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="/">Inicio</a>
                    </li>
                    @if(auth()->user() && auth()->user()->hasRole('admin'))
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('configuraciones') }}">Configuraciones</a>
                    </li>
                    @endif
                </ul>
                <ul class="navbar-nav">
                    @auth
                    <li class="nav-item">
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-link nav-link">Cerrar Sesión</button>
                        </form>
                    </li>
                    @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Iniciar Sesión</a>
                    </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <h1 class="display-4 mb-4">Bienvenido a GesVitalPro</h1>
                <p class="lead">Sistema de Gestión Integral para Profesionales de la Salud</p>
                
                @auth
                    <div class="mt-4">
                        <h2>Panel de Control</h2>
                        <div class="row mt-4">
                            @if(auth()->user()->hasRole('admin'))
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <h5 class="card-title">Administración</h5>
                                        <p class="card-text">Accede al panel de administración del sistema.</p>
                                        <a href="/admin" class="btn btn-primary">Panel de Admin</a>
                                    </div>
                                </div>
                            </div>
                            @endif
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <h5 class="card-title">Mi Perfil</h5>
                                        <p class="card-text">Gestiona tu información personal y preferencias.</p>
                                        <a href="/admin/profile" class="btn btn-primary">Ver Perfil</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="mt-4">
                        <p>Para acceder a todas las funcionalidades, por favor inicia sesión.</p>
                        <a href="{{ route('login') }}" class="btn btn-primary btn-lg">Iniciar Sesión</a>
                    </div>
                @endauth
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
