<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuraciones - GesVitalPro</title>
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
                        <a class="nav-link" href="/">Inicio</a>
                    </li>
                    @if(auth()->user() && auth()->user()->hasRole('admin'))
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('configuraciones') }}">Configuraciones</a>
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

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">Configuraciones del Sistema</h1>
                
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Configuración General</h5>
                        <p class="card-text">Aquí puedes gestionar las configuraciones generales del sistema.</p>
                        <a href="/admin" class="btn btn-primary">Ir al Panel de Administración</a>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title">Gestión de Usuarios</h5>
                        <p class="card-text">Administra los usuarios y sus roles en el sistema.</p>
                        <a href="/admin/users" class="btn btn-primary">Gestionar Usuarios</a>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title">Configuración de Roles y Permisos</h5>
                        <p class="card-text">Configura los roles y permisos para los usuarios del sistema.</p>
                        <a href="/admin/roles" class="btn btn-primary">Gestionar Roles</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
