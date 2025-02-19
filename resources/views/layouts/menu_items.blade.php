<!-- Cirugías -->
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('surgeries.*') ? '' : 'collapsed' }}" data-bs-target="#surgeries-nav" data-bs-toggle="collapse" href="javascript:void(0);">
        <i class="bi bi-hospital"></i>
        <span>Cirugías</span>
        <i class="bi bi-chevron-down ms-auto"></i>
    </a>
    <ul id="surgeries-nav" class="nav-content collapse {{ request()->routeIs('surgeries.*') ? 'show' : '' }}" data-bs-parent="#sidebar-nav">
        <li>
            <a href="{{ route('surgeries.index') }}" class="{{ request()->routeIs('surgeries.index') ? 'active' : '' }}">
                <i class="bi bi-circle"></i><span>Lista de Cirugías</span>
            </a>
        </li>
        <li>
            <a href="{{ route('surgeries.create') }}" class="{{ request()->routeIs('surgeries.create') ? 'active' : '' }}">
                <i class="bi bi-circle"></i><span>Nueva Cirugía</span>
            </a>
        </li>
        <li>
            <a href="{{ route('surgeries.status') }}" class="{{ request()->routeIs('surgeries.status') ? 'active' : '' }}">
                <i class="bi bi-circle"></i><span>Estado de Cirugías</span>
            </a>
        </li>
    </ul>
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

<!-- Equipamiento -->
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('equipment.*') ? '' : 'collapsed' }}" data-bs-target="#equipment-nav" data-bs-toggle="collapse" href="javascript:void(0);">
        <i class="bi bi-tools"></i>
        <span>Equipamiento</span>
        <i class="bi bi-chevron-down ms-auto"></i>
    </a>
    <ul id="equipment-nav" class="nav-content collapse {{ request()->routeIs('equipment.*') ? 'show' : '' }}" data-bs-parent="#sidebar-nav">
        <li>
            <a href="{{ route('equipment.index') }}" class="{{ request()->routeIs('equipment.index') ? 'active' : '' }}">
                <i class="bi bi-circle"></i><span>Lista de Equipos</span>
            </a>
        </li>
        <li>
            <a href="{{ route('equipment.create') }}" class="{{ request()->routeIs('equipment.create') ? 'active' : '' }}">
                <i class="bi bi-circle"></i><span>Nuevo Equipo</span>
            </a>
        </li>
        <li>
            <a href="{{ route('equipment.maintenance') }}" class="{{ request()->routeIs('equipment.maintenance') ? 'active' : '' }}">
                <i class="bi bi-circle"></i><span>Mantenimiento</span>
            </a>
        </li>
    </ul>
</li>

<!-- Visitas -->
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('visitas.*') ? '' : 'collapsed' }}" data-bs-target="#visitas-nav" data-bs-toggle="collapse" href="javascript:void(0);">
        <i class="bi bi-calendar-check"></i>
        <span>Visitas</span>
        <i class="bi bi-chevron-down ms-auto"></i>
    </a>
    <ul id="visitas-nav" class="nav-content collapse {{ request()->routeIs('visitas.*') ? 'show' : '' }}" data-bs-parent="#sidebar-nav">
        <li>
            <a href="{{ route('visitas.index') }}" class="{{ request()->routeIs('visitas.index') ? 'active' : '' }}">
                <i class="bi bi-circle"></i><span>Lista de Visitas</span>
            </a>
        </li>
        <li>
            <a href="{{ route('visitas.create') }}" class="{{ request()->routeIs('visitas.create') ? 'active' : '' }}">
                <i class="bi bi-circle"></i><span>Nueva Visita</span>
            </a>
        </li>
        <li>
            <a href="{{ route('visitas.reporte-frecuencia') }}" class="{{ request()->routeIs('visitas.reporte-frecuencia') ? 'active' : '' }}">
                <i class="bi bi-circle"></i><span>Reporte de Frecuencia</span>
            </a>
        </li>
    </ul>
</li>
