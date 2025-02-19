<nav class="navbar navbar-expand-lg navbar-light bg-white">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ url('/') }}">
            <img src="{{ asset('img/logo.png') }}" alt="Logo" height="40" class="d-inline-block align-text-top">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                @auth
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/dashboard') }}">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="surgeriesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-clipboard2-pulse"></i> Cirugías
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="surgeriesDropdown">
                            <li>
                                <a class="dropdown-item" href="{{ route('surgeries.index') }}">
                                    <i class="bi bi-list-check"></i> Lista
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('surgeries.kanban') }}">
                                    <i class="bi bi-kanban"></i> Tablero Kanban
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('surgeries.create') }}">
                                    <i class="bi bi-plus-circle"></i> Nueva Cirugía
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/equipment') }}">
                            <i class="bi bi-tools"></i> Equipamiento
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/instituciones') }}">
                            <i class="bi bi-building"></i> Instituciones
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/medicos') }}">
                            <i class="bi bi-person-vcard"></i> Médicos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/visitas') }}">
                            <i class="bi bi-calendar-check"></i> Visitas
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="storageDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-box-seam"></i> Almacén
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="storageDropdown">
                            <li>
                                <a class="dropdown-item" href="{{ route('storage.index') }}">
                                    <i class="bi bi-list-check"></i> Solicitudes
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('storage.kanban') }}">
                                    <i class="bi bi-kanban"></i> Tablero Kanban
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('storage.report') }}">
                                    <i class="bi bi-file-earmark-text"></i> Reportes
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="dispatchDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-truck"></i> Despacho
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="dispatchDropdown">
                            <li>
                                <a class="dropdown-item" href="{{ route('dispatch.index') }}">
                                    <i class="bi bi-list-check"></i> Entregas
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('dispatch.kanban') }}">
                                    <i class="bi bi-kanban"></i> Tablero Kanban
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('dispatch.report') }}">
                                    <i class="bi bi-file-earmark-text"></i> Reportes
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="geoDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-geo-alt"></i> Geolocalización
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="geoDropdown">
                            <li>
                                <a class="dropdown-item" href="{{ route('geolocalizacion.mapa') }}">
                                    <i class="bi bi-map"></i> Mapa
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('geolocalizacion.rutas') }}">
                                    <i class="bi bi-signpost-split"></i> Rutas
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('geolocalizacion.zonas') }}">
                                    <i class="bi bi-geo"></i> Zonas
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="staffDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-people"></i> Personal
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="staffDropdown">
                            <li>
                                <a class="dropdown-item" href="{{ route('staff.index') }}">
                                    <i class="bi bi-person-badge"></i> Staff
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('externos.index') }}">
                                    <i class="bi bi-person-lines-fill"></i> Externos
                                </a>
                            </li>
                        </ul>
                    </li>
                @endauth
            </ul>
            <ul class="navbar-nav ms-auto">
                @auth
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i> {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li>
                                <a class="dropdown-item" href="{{ url('/user/profile') }}">
                                    <i class="bi bi-person"></i> Mi Cuenta
                                </a>
                            </li>
                            @if(Auth::user()->hasRole('admin'))
                            <li>
                                <a class="dropdown-item" href="{{ url('/admin/users') }}">
                                    <i class="bi bi-people"></i> Gestión de Usuarios
                                </a>
                            </li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">
                            <i class="bi bi-box-arrow-in-right"></i> Iniciar sesión
                        </a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>
