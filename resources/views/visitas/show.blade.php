<x-app-layout>
<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary">
            <i class="fas fa-calendar-alt"></i> Detalles de la Visita
        </h2>
        <div>
            @if($visita->estado === 'programada')
                <form action="{{ route('visitas.marcar-realizada', $visita) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success me-2">
                        <i class="fas fa-check"></i> Marcar como Realizada
                    </button>
                </form>
                <form action="{{ route('visitas.marcar-cancelada', $visita) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" 
                            class="btn btn-danger me-2"
                            onclick="return confirm('¿Está seguro de cancelar esta visita?')">
                        <i class="fas fa-times"></i> Cancelar Visita
                    </button>
                </form>
            @endif
            <a href="{{ route('visitas.edit', $visita) }}" class="btn btn-warning me-2">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="{{ route('visitas.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Estado y Fecha -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="calendar-icon mx-auto mb-3 {{ $visita->estado }}">
                            <div class="month">{{ $visita->fecha_hora->format('M') }}</div>
                            <div class="day">{{ $visita->fecha_hora->format('d') }}</div>
                        </div>
                        <h3 class="mb-2">{{ $visita->fecha_hora->format('d/m/Y') }}</h3>
                        <h4 class="text-muted mb-2">{{ $visita->fecha_hora->format('H:i') }} hrs</h4>
                        <span class="badge estado-{{ $visita->estado }} fs-6">
                            {{ ucfirst($visita->estado) }}
                        </span>
                    </div>

                    <hr>

                    <div class="info-list">
                        <div class="info-item">
                            <i class="fas fa-user text-primary"></i>
                            <div>
                                <small class="text-muted">Asesor</small>
                                <p class="mb-0">{{ $visita->asesor->name }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información de la Institución -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-hospital"></i> Información de la Institución</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="institution-icon me-3 {{ strtolower($visita->institucion->tipo) }}">
                            @if($visita->institucion->tipo === 'hospital')
                                <i class="fas fa-hospital"></i>
                            @elseif($visita->institucion->tipo === 'clinica')
                                <i class="fas fa-clinic-medical"></i>
                            @else
                                <i class="fas fa-house-medical"></i>
                            @endif
                        </div>
                        <div>
                            <h4 class="mb-1">{{ $visita->institucion->nombre }}</h4>
                            <span class="badge tipo-{{ strtolower($visita->institucion->tipo) }}">
                                {{ ucfirst($visita->institucion->tipo) }}
                            </span>
                        </div>
                    </div>

                    <div class="info-list">
                        <div class="info-item">
                            <i class="fas fa-map-marker-alt text-danger"></i>
                            <div>
                                <small class="text-muted">Dirección</small>
                                <p class="mb-0">{{ $visita->institucion->direccion }}</p>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-phone text-primary"></i>
                            <div>
                                <small class="text-muted">Teléfono</small>
                                <p class="mb-0">{{ $visita->institucion->telefono }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información del Médico -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-user-md"></i> Información del Médico</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar-circle me-3">
                            {{ strtoupper(substr($visita->medico->nombre, 0, 1)) }}
                        </div>
                        <div>
                            <h4 class="mb-1">{{ $visita->medico->nombre }}</h4>
                            <p class="text-muted mb-0">{{ $visita->medico->especialidad }}</p>
                        </div>
                    </div>

                    <div class="info-list">
                        <div class="info-item">
                            <i class="fas fa-phone text-primary"></i>
                            <div>
                                <small class="text-muted">Teléfono</small>
                                <p class="mb-0">{{ $visita->medico->telefono }}</p>
                            </div>
                        </div>
                        @if($visita->medico->email)
                            <div class="info-item">
                                <i class="fas fa-envelope text-primary"></i>
                                <div>
                                    <small class="text-muted">Correo Electrónico</small>
                                    <p class="mb-0">{{ $visita->medico->email }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Detalles de la Visita -->
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-clipboard-list"></i> Detalles de la Visita</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">Motivo de la Visita</h6>
                            <p>{{ $visita->motivo }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">Observaciones</h6>
                            <p class="mb-0">{{ $visita->observaciones ?: 'Sin observaciones registradas' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</x-app-layout>

<style>
<style>
    .calendar-icon {
        width: 80px;
        height: 90px;
        border-radius: 12px;
        overflow: hidden;
        text-align: center;
        background: #fff;
        border: 2px solid #ddd;
    }
    .calendar-icon .month {
        background: var(--primary-color);
        color: white;
        padding: 4px;
        font-size: 1rem;
        text-transform: uppercase;
    }
    .calendar-icon .day {
        font-size: 2rem;
        padding: 8px;
        font-weight: bold;
    }
    .calendar-icon.realizada {
        border-color: #28a745;
    }
    .calendar-icon.realizada .month {
        background: #28a745;
    }
    .calendar-icon.cancelada {
        border-color: #dc3545;
    }
    .calendar-icon.cancelada .month {
        background: #dc3545;
    }
    .institution-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    .institution-icon.hospital {
        background-color: #e3f2fd;
        color: #1976d2;
    }
    .institution-icon.clinica {
        background-color: #f3e5f5;
        color: #7b1fa2;
    }
    .institution-icon.consultorio {
        background-color: #e8f5e9;
        color: #388e3c;
    }
    .avatar-circle {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background-color: var(--primary-color);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.2rem;
    }
    .badge.estado-programada {
        background-color: #ffc107;
        color: #000;
    }
    .badge.estado-realizada {
        background-color: #28a745;
    }
    .badge.estado-cancelada {
        background-color: #dc3545;
    }
    .badge.tipo-hospital {
        background-color: #1976d2;
    }
    .badge.tipo-clinica {
        background-color: #7b1fa2;
    }
    .badge.tipo-consultorio {
        background-color: #388e3c;
    }
    .info-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .info-item {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }
    .info-item i {
        margin-top: 0.25rem;
    }
</style>
</style>
