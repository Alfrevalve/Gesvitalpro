<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('dashboard') }}">
            {{ config('app.name', 'Laravel') }}
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            @auth
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                       href="{{ route('dashboard') }}">
                       <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>

                @can('view_lines')
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('lines.*') ? 'active' : '' }}"
                       href="#" role="button" data-bs-toggle="dropdown">
                       <i class="bi bi-diagram-3"></i> Líneas
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('lines.index') }}">Ver Todas</a></li>
                        @can('create', App\Models\Line::class)
                        <li><a class="dropdown-item" href="{{ route('lines.create') }}">Nueva Línea</a></li>
                        @endcan
                    </ul>
                </li>
                @endcan

                @can('view_surgeries')
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('surgeries.*') ? 'active' : '' }}"
                       href="#" role="button" data-bs-toggle="dropdown">
                       <i class="bi bi-hospital"></i> Cirugías
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('surgeries.index') }}">Ver Todas</a></li>
                        @can('create', App\Models\Surgery::class)
                        <li><a class="dropdown-item" href="{{ route('surgeries.create') }}">Nueva Cirugía</a></li>
                        @endcan
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ route('surgeries.status') }}">Estado</a></li>
                    </ul>
                </li>
                @endcan

                @can('view_equipment')
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('equipment.*') ? 'active' : '' }}"
                       href="#" role="button" data-bs-toggle="dropdown">
                       <i class="bi bi-tools"></i> Equipos
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('equipment.index') }}">Ver Todos</a></li>
                        @can('create', App\Models\Equipment::class)
                        <li><a class="dropdown-item" href="{{ route('equipment.create') }}">Nuevo Equipo</a></li>
                        @endcan
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ route('equipment.maintenance') }}">Mantenimiento</a></li>
                    </ul>
                </li>
                @endcan

                @can('view_visits')
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('visitas.*') ? 'active' : '' }}"
                       href="#" role="button" data-bs-toggle="dropdown">
                       <i class="bi bi-calendar-check"></i> Visitas
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('visitas.index') }}">Ver Todas</a></li>
                        @can('create', App\Models\Visita::class)
                        <li><a class="dropdown-item" href="{{ route('visitas.create') }}">Nueva Visita</a></li>
                        @endcan
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ route('visitas.reporte-frecuencia') }}">Reporte</a></li>
                    </ul>
                </li>
                @endcan

                @can('view_storage')
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('storage.*') ? 'active' : '' }}"
                       href="#" role="button" data-bs-toggle="dropdown">
                       <i class="bi bi-box-seam"></i> Almacén
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('storage.index') }}">Ver Todo</a></li>
                        <li><a class="dropdown-item" href="{{ route('storage.kanban') }}">Kanban</a></li>
                        <li><a class="dropdown-item" href="{{ route('storage.report') }}">Reporte</a></li>
                    </ul>
                </li>
                @endcan

                @can('view_dispatch')
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('dispatch.*') ? 'active' : '' }}"
                       href="#" role="button" data-bs-toggle="dropdown">
                       <i class="bi bi-truck"></i> Despacho
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('dispatch.index') }}">Ver Todo</a></li>
                        <li><a class="dropdown-item" href="{{ route('dispatch.kanban') }}">Kanban</a></li>
                        <li><a class="dropdown-item" href="{{ route('dispatch.report') }}">Reporte</a></li>
                    </ul>
                </li>
                @endcan

                @if(auth()->user()->isAdmin())
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('admin.*') ? 'active' : '' }}"
                       href="#" role="button" data-bs-toggle="dropdown">
                       <i class="bi bi-gear"></i> Administración
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('staff.index') }}">Personal</a></li>
                        <li><a class="dropdown-item" href="{{ route('externos.index') }}">Externos</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ route('geolocalizacion.mapa') }}">Geolocalización</a></li>
                    </ul>
                </li>
                @endif
            </ul>

            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> {{ Auth::user()->name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        @if(Auth::user()->canAccessFilament())
                        <li>
                            <a class="dropdown-item" href="{{ route('filament.admin.pages.dashboard') }}">
                                <i class="bi bi-speedometer"></i> Panel Admin
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        @endif
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
            @endauth
        </div>
    </div>
</nav>
