<x-app-layout>
<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary">
            <i class="fas fa-hospital"></i> Detalles de la Institución
        </h2>
        <div>
            <a href="{{ route('instituciones.edit', $institucion) }}" class="btn btn-warning me-2">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="{{ route('instituciones.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Información Principal -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="institution-icon mx-auto mb-3 {{ strtolower($institucion->tipo) }}">
                            @if($institucion->tipo === 'hospital')
                                <i class="fas fa-hospital"></i>
                            @elseif($institucion->tipo === 'clinica')
                                <i class="fas fa-clinic-medical"></i>
                            @else
                                <i class="fas fa-house-medical"></i>
                            @endif
                        </div>
                        <h3 class="mb-1">{{ $institucion->nombre }}</h3>
                        <span class="badge tipo-{{ strtolower($institucion->tipo) }} mb-2">
                            {{ ucfirst($institucion->tipo) }}
                        </span>
                    </div>

                    <hr>

                    <div class="info-list">
                        <div class="info-item">
                            <i class="fas fa-map-marker-alt text-danger"></i>
                            <div>
                                <small class="text-muted">Dirección</small>
                                <p class="mb-0">{{ $institucion->direccion }}</p>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-phone text-primary"></i>
                            <div>
                                <small class="text-muted">Teléfono</small>
                                <p class="mb-0">{{ $institucion->telefono }}</p>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-user-md text-success"></i>
                            <div>
                                <small class="text-muted">Médicos Asociados</small>
                                <p class="mb-0">{{ $estadisticas['medicos_count'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="col-md-8 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Estadísticas de Visitas</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="stat-card bg-primary text-white">
                                <div class="stat-icon">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <div class="stat-details">
                                    <h3 class="stat-value">{{ $estadisticas['total_visitas'] }}</h3>
                                    <p class="stat-label mb-0">Total Visitas</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card bg-success text-white">
                                <div class="stat-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="stat-details">
                                    <h3 class="stat-value">{{ $estadisticas['visitas_mes'] }}</h3>
                                    <p class="stat-label mb-0">Visitas este Mes</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card bg-info text-white">
                                <div class="stat-icon">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div class="stat-details">
                                    <h3 class="stat-value">{{ $estadisticas['ultima_visita'] ? $estadisticas['ultima_visita']->format('d/m/Y') : 'N/A' }}</h3>
                                    <p class="stat-label mb-0">Última Visita</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Historial de Visitas -->
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Historial de Visitas</h5>
                </div>
                <div class="card-body">
                    @if($visitas->isEmpty())
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No hay visitas registradas</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Médico</th>
                                        <th>Asesor</th>
                                        <th class="text-center">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($visitas as $visita)
                                        <tr>
                                            <td>
                                                <i class="far fa-calendar-alt text-primary me-2"></i>
                                                {{ $visita->fecha_hora->format('d/m/Y H:i') }}
                                            </td>
                                            <td>{{ $visita->medico->nombre }}</td>
                                            <td>{{ $visita->asesor->name }}</td>
                                            <td class="text-center">
                                                @if($visita->estado === 'realizada')
                                                    <span class="badge bg-success">Realizada</span>
                                                @elseif($visita->estado === 'pendiente')
                                                    <span class="badge bg-warning text-dark">Pendiente</span>
                                                @else
                                                    <span class="badge bg-danger">Cancelada</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <div class="d-flex justify-content-end mt-3">
                            {{ $visitas->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
</x-app-layout>

<style>
<style>
    .institution-icon {
        width: 80px;
        height: 80px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
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
    .stat-card {
        padding: 1.5rem;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .stat-icon {
        font-size: 2rem;
        opacity: 0.8;
    }
    .stat-value {
        font-size: 1.5rem;
        font-weight: bold;
        margin-bottom: 0.25rem;
    }
    .stat-label {
        font-size: 0.875rem;
        opacity: 0.9;
    }
    .badge {
        font-weight: 500;
        padding: 0.5em 0.75em;
    }
</style>
</style>
