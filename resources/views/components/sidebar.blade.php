<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ route('dashboard') }}" class="app-brand-link">
            <span class="app-brand-logo demo">
                <!-- Your logo here -->
            </span>
            <span class="app-brand-text demo menu-text fw-bolder ms-2">GesBio</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <!-- Dashboard -->
        <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <a href="{{ route('dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div data-i18n="Analytics">Dashboard</div>
            </a>
        </li>

        <!-- Equipos -->
        <li class="menu-item {{ request()->routeIs('equipment.*') ? 'active' : '' }}">
            <a href="{{ route('equipment.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-cube-alt"></i>
                <div data-i18n="Equipment">Equipos</div>
            </a>
        </li>

        <!-- Cirugías -->
        <li class="menu-item {{ request()->routeIs('surgeries.*') ? 'active' : '' }}">
            <a href="{{ route('surgeries.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-plus-medical"></i>
                <div data-i18n="Surgeries">Cirugías</div>
            </a>
        </li>

        <!-- Instituciones -->
        <li class="menu-item {{ request()->routeIs('instituciones.*') ? 'active' : '' }}">
            <a href="{{ route('instituciones.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-building"></i>
                <div data-i18n="Instituciones">Instituciones</div>
            </a>
        </li>

        <!-- Médicos -->
        <li class="menu-item {{ request()->routeIs('medicos.*') ? 'active' : '' }}">
            <a href="{{ route('medicos.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-user-pin"></i>
                <div data-i18n="Medicos">Médicos</div>
            </a>
        </li>

        <!-- Usuarios -->
        @can('manage users')
        <li class="menu-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
            <a href="{{ route('users.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-user"></i>
                <div data-i18n="Users">Usuarios</div>
            </a>
        </li>
        @endcan
    </ul>
</aside>
