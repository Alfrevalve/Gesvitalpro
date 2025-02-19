@include('layouts.header')

<aside id="sidebar" class="sidebar">
    <ul class="sidebar-nav" id="sidebar-nav">
        <!-- Dashboard -->
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('dashboard') ? '' : 'collapsed' }}" href="{{ route('dashboard') }}">
                <i class="bi bi-grid"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <!-- Almacén -->
        @if(auth()->check() && auth()->user()->hasRole('storage'))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('storage.*') ? '' : 'collapsed' }}" data-bs-target="#storage-nav" data-bs-toggle="collapse" href="javascript:void(0);">
                <i class="bi bi-box-seam"></i>
                <span>Almacén</span>
                <i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="storage-nav" class="nav-content collapse {{ request()->routeIs('storage.*') ? 'show' : '' }}" data-bs-parent="#sidebar-nav">
                <li>
                    <a href="{{ route('storage.index') }}" class="{{ request()->routeIs('storage.index') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Panel Principal</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('storage.kanban') }}" class="{{ request()->routeIs('storage.kanban') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Vista Kanban</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('storage.report') }}" class="{{ request()->routeIs('storage.report') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Reportes</span>
                    </a>
                </li>
            </ul>
        </li>
        @endif

        <!-- Despacho -->
        @if(auth()->check() && auth()->user()->hasRole('dispatch'))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('dispatch.*') ? '' : 'collapsed' }}" data-bs-target="#dispatch-nav" data-bs-toggle="collapse" href="javascript:void(0);">
                <i class="bi bi-truck"></i>
                <span>Despacho</span>
                <i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="dispatch-nav" class="nav-content collapse {{ request()->routeIs('dispatch.*') ? 'show' : '' }}" data-bs-parent="#sidebar-nav">
                <li>
                    <a href="{{ route('dispatch.index') }}" class="{{ request()->routeIs('dispatch.index') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Panel Principal</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('dispatch.kanban') }}" class="{{ request()->routeIs('dispatch.kanban') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Vista Kanban</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('dispatch.report') }}" class="{{ request()->routeIs('dispatch.report') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Reportes</span>
                    </a>
                </li>
            </ul>
        </li>
        @endif

        <!-- Resto de los menús existentes -->
        @include('layouts.menu_items')
    </ul>
</aside>

<main id="main" class="main">
    {{ $slot }}
</main>

@include('layouts.footer')
