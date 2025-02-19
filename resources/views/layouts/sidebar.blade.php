<div class="sidebar-wrapper">
    <!-- Sidebar -->
    <nav id="sidebar" class="bg-dark">
        <div class="sidebar-header">
            <a href="{{ url('/') }}" class="d-flex align-items-center py-3 px-4 text-white text-decoration-none">
                <img src="{{ asset('img/logo.png') }}" alt="Logo" height="40" class="me-2">
                <span class="fs-5">GesBio</span>
            </a>
        </div>

        <div class="sidebar-menu">
            @auth
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('dashboard*') ? 'active' : '' }}" href="{{ url('/dashboard') }}">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('surgeries*') ? 'active' : '' }} dropdown-toggle" href="#surgeriesSubmenu" data-bs-toggle="collapse" role="button" aria-expanded="false">
                            <i class="bi bi-clipboard2-pulse"></i>
                            <span>Cirugías</span>
                        </a>
                        <div class="collapse {{ request()->is('surgeries*') ? 'show' : '' }}" id="surgeriesSubmenu">
                            <ul class="nav flex-column ms-3">
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('surgeries.index') }}">
                                        <i class="bi bi-list-check"></i>
                                        <span>Lista</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('surgeries.kanban') }}">
                                        <i class="bi bi-kanban"></i>
                                        <span>Tablero Kanban</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('surgeries.create') }}">
                                        <i class="bi bi-plus-circle"></i>
                                        <span>Nueva Cirugía</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('equipment*') ? 'active' : '' }}" href="{{ url('/equipment') }}">
                            <i class="bi bi-tools"></i>
                            <span>Equipamiento</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('instituciones*') ? 'active' : '' }}" href="{{ url('/instituciones') }}">
                            <i class="bi bi-building"></i>
                            <span>Instituciones</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('medicos*') ? 'active' : '' }}" href="{{ url('/medicos') }}">
                            <i class="bi bi-person-vcard"></i>
                            <span>Médicos</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('visitas*') ? 'active' : '' }}" href="{{ url('/visitas') }}">
                            <i class="bi bi-calendar-check"></i>
                            <span>Visitas</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('storage*') ? 'active' : '' }} dropdown-toggle" href="#storageSubmenu" data-bs-toggle="collapse" role="button" aria-expanded="false">
                            <i class="bi bi-box-seam"></i>
                            <span>Almacén</span>
                        </a>
                        <div class="collapse {{ request()->is('storage*') ? 'show' : '' }}" id="storageSubmenu">
                            <ul class="nav flex-column ms-3">
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('storage.index') }}">
                                        <i class="bi bi-list-check"></i>
                                        <span>Solicitudes</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('storage.kanban') }}">
                                        <i class="bi bi-kanban"></i>
                                        <span>Tablero Kanban</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('storage.report') }}">
                                        <i class="bi bi-file-earmark-text"></i>
                                        <span>Reportes</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('dispatch*') ? 'active' : '' }} dropdown-toggle" href="#dispatchSubmenu" data-bs-toggle="collapse" role="button" aria-expanded="false">
                            <i class="bi bi-truck"></i>
                            <span>Despacho</span>
                        </a>
                        <div class="collapse {{ request()->is('dispatch*') ? 'show' : '' }}" id="dispatchSubmenu">
                            <ul class="nav flex-column ms-3">
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('dispatch.index') }}">
                                        <i class="bi bi-list-check"></i>
                                        <span>Entregas</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('dispatch.kanban') }}">
                                        <i class="bi bi-kanban"></i>
                                        <span>Tablero Kanban</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('dispatch.report') }}">
                                        <i class="bi bi-file-earmark-text"></i>
                                        <span>Reportes</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('geolocalizacion*') ? 'active' : '' }} dropdown-toggle" href="#geoSubmenu" data-bs-toggle="collapse" role="button" aria-expanded="false">
                            <i class="bi bi-geo-alt"></i>
                            <span>Geolocalización</span>
                        </a>
                        <div class="collapse {{ request()->is('geolocalizacion*') ? 'show' : '' }}" id="geoSubmenu">
                            <ul class="nav flex-column ms-3">
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('geolocalizacion.mapa') }}">
                                        <i class="bi bi-map"></i>
                                        <span>Mapa</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('geolocalizacion.rutas') }}">
                                        <i class="bi bi-signpost-split"></i>
                                        <span>Rutas</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('geolocalizacion.zonas') }}">
                                        <i class="bi bi-geo"></i>
                                        <span>Zonas</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item mt-4">
                        <hr class="dropdown-divider bg-light">
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i>
                            <span>{{ Auth::user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="userMenu">
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
                </ul>
            @else
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">
                            <i class="bi bi-box-arrow-in-right"></i>
                            <span>Iniciar sesión</span>
                        </a>
                    </li>
                </ul>
            @endauth
        </div>
    </nav>

    <!-- Overlay -->
    <div class="sidebar-overlay"></div>
</div>

<style>
/* Sidebar Styles */
.sidebar-wrapper {
    display: flex;
    min-height: 100vh;
}

#sidebar {
    min-width: 250px;
    max-width: 250px;
    min-height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1000;
    transition: all 0.3s;
    box-shadow: 2px 0 5px rgba(0,0,0,0.1);
}

#sidebar.active {
    margin-left: -250px;
}

.sidebar-header {
    padding: 1rem;
    background: rgba(0,0,0,0.1);
}

.sidebar-menu {
    padding: 1rem 0;
    overflow-y: auto;
    max-height: calc(100vh - 80px);
}

#sidebar .nav-link {
    color: rgba(255,255,255,0.8);
    padding: 0.75rem 1.5rem;
    display: flex;
    align-items: center;
    transition: all 0.3s;
}

#sidebar .nav-link:hover {
    color: #fff;
    background: rgba(255,255,255,0.1);
}

#sidebar .nav-link.active {
    color: #fff;
    background: rgba(255,255,255,0.2);
}

#sidebar .nav-link i {
    margin-right: 10px;
    width: 20px;
    text-align: center;
}

#sidebar .dropdown-menu {
    background-color: #343a40;
    border: none;
    margin-top: 0;
}

#sidebar .dropdown-item {
    color: rgba(255,255,255,0.8);
    padding: 0.5rem 1.5rem;
}

#sidebar .dropdown-item:hover {
    color: #fff;
    background: rgba(255,255,255,0.1);
}

.sidebar-overlay {
    display: none;
    position: fixed;
    width: 100vw;
    height: 100vh;
    background: rgba(0,0,0,0.5);
    z-index: 999;
    opacity: 0;
    transition: all 0.5s ease-in-out;
}

.sidebar-overlay.active {
    display: block;
    opacity: 1;
}

/* Responsive Styles */
@media (max-width: 768px) {
    #sidebar {
        margin-left: -250px;
    }
    #sidebar.active {
        margin-left: 0;
    }
    .sidebar-overlay.active {
        display: block;
    }
    #content {
        width: 100%;
    }
    #content.active {
        margin-left: 250px;
    }
}

/* Content Styles */
#content {
    width: calc(100% - 250px);
    min-height: 100vh;
    transition: all 0.3s;
    position: absolute;
    top: 0;
    right: 0;
}

#content.active {
    width: 100%;
}

/* Toggle Button Styles */
#sidebarCollapse {
    position: fixed;
    top: 1rem;
    left: 1rem;
    z-index: 1001;
    display: none;
    background: #343a40;
    border: none;
    color: #fff;
    padding: 0.5rem;
    border-radius: 4px;
}

@media (max-width: 768px) {
    #sidebarCollapse {
        display: block;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle Sidebar
    const sidebarCollapse = document.getElementById('sidebarCollapse');
    const sidebar = document.getElementById('sidebar');
    const content = document.getElementById('content');
    const overlay = document.querySelector('.sidebar-overlay');

    function toggleSidebar() {
        sidebar.classList.toggle('active');
        content.classList.toggle('active');
        overlay.classList.toggle('active');
    }

    if (sidebarCollapse) {
        sidebarCollapse.addEventListener('click', toggleSidebar);
    }

    if (overlay) {
        overlay.addEventListener('click', toggleSidebar);
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        const isClickInside = sidebar.contains(event.target) ||
                            (sidebarCollapse && sidebarCollapse.contains(event.target));

        if (!isClickInside && window.innerWidth <= 768 && !sidebar.classList.contains('active')) {
            sidebar.classList.add('active');
            content.classList.add('active');
            overlay.classList.remove('active');
        }
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('active');
            content.classList.remove('active');
            overlay.classList.remove('active');
        }
    });
});
</script>
