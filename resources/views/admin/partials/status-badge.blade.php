@php
    $statusClasses = [
        'active' => 'success',
        'inactive' => 'danger',
        'pending' => 'warning',
        'completed' => 'success',
        'cancelled' => 'danger',
        'in_progress' => 'info',
        'scheduled' => 'primary',
        'maintenance' => 'warning',
        'available' => 'success',
        'unavailable' => 'danger',
    ];

    $statusLabels = [
        'active' => 'Activo',
        'inactive' => 'Inactivo',
        'pending' => 'Pendiente',
        'completed' => 'Completado',
        'cancelled' => 'Cancelado',
        'in_progress' => 'En Progreso',
        'scheduled' => 'Programado',
        'maintenance' => 'En Mantenimiento',
        'available' => 'Disponible',
        'unavailable' => 'No Disponible',
    ];

    $class = $statusClasses[$status] ?? 'secondary';
    $label = $statusLabels[$status] ?? ucfirst($status);
@endphp

<span class="badge badge-{{ $class }}">{{ $label }}</span>
