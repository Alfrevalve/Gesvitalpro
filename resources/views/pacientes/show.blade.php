@extends('layouts.app')

@section('title', 'Detalles del Paciente')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user me-2"></i>Detalles del Paciente
        </h1>
        <div>
            <a href="{{ route('pacientes.edit', $paciente->id) }}" class="btn btn-warning me-2">
                <i class="fas fa-edit me-2"></i>Editar
            </a>
            <a href="{{ route('pacientes.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Información Principal -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Información Personal
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="text-muted">Nombre Completo</label>
                                <p class="h5">{{ $paciente->nombre }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="text-muted">Identificación</label>
                                <p class="h5">{{ $paciente->id_number }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="text-muted">Correo Electrónico</label>
                                <p class="h5">{{ $paciente->email }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="text-muted">Teléfono</label>
                                <p class="h5">{{ $paciente->telefono }}</p>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="detail-item">
                                <label class="text-muted">Dirección</label>
                                <p class="h5">{{ $paciente->direccion }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Historial Médico -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-notes-medical me-2"></i>Historial Médico
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-12">
                            <div class="detail-item">
                                <label class="text-muted">Alergias</label>
                                <p class="h5">{{ $paciente->alergias ?: 'No registradas' }}</p>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="detail-item">
                                <label class="text-muted">Antecedentes Médicos</label>
                                <p class="h5">{{ $paciente->antecedentes ?: 'No registrados' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cirugías -->
            <div class="card mb-4">
                <div class="card-header bg-warning">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-procedures me-2"></i>Cirugías
                    </h5>
                </div>
                <div class="card-body">
                    @if($paciente->cirugias && $paciente->cirugias->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Tipo</th>
                                        <th>Estado</th>
                                        <th>Cirujano</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($paciente->cirugias as $cirugia)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($cirugia->fecha_cirugia)->format('d/m/Y') }}</td>
                                            <td>{{ $cirugia->tipo_cirugia }}</td>
                                            <td>
                                                @switch($cirugia->estado_cirugia)
                                                    @case('programada')
                                                        <span class="badge bg-info">Programada</span>
                                                        @break
                                                    @case('en_proceso')
                                                        <span class="badge bg-warning">En Proceso</span>
                                                        @break
                                                    @case('completada')
                                                        <span class="badge bg-success">Completada</span>
                                                        @break
                                                    @case('cancelada')
                                                        <span class="badge bg-danger">Cancelada</span>
                                                        @break
                                                @endswitch
                                            </td>
                                            <td>{{ $cirugia->cirujano }}</td>
                                            <td>
                                                <a href="{{ route('cirugias.show', $cirugia->id) }}" 
                                                   class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-info-circle fa-2x text-muted mb-3"></i>
                            <p class="text-muted">No hay cirugías registradas</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Perfil -->
            <div class="card mb-4">
                <div class="card-body text-center">
                    <div class="avatar-xl mx-auto mb-3">
                        @if($paciente->foto)
                            <img src="{{ asset('storage/' . $paciente->foto) }}" 
                                 class="rounded-circle" 
                                 alt="Foto de {{ $paciente->nombre }}">
                        @else
                            <i class="fas fa-user-circle fa-5x text-secondary"></i>
                        @endif
                    </div>
                    <h4 class="mb-1">{{ $paciente->nombre }}</h4>
                    <p class="text-muted mb-3">ID: {{ $paciente->id_number }}</p>
                    <div class="mb-3">
                        @if($paciente->estado == 'activo')
                            <span class="badge bg-success">
                                <i class="fas fa-check me-1"></i>Activo
                            </span>
                        @else
                            <span class="badge bg-danger">
                                <i class="fas fa-times me-1"></i>Inactivo
                            </span>
                        @endif
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <div class="row text-center">
                        <div class="col">
                            <div class="text-muted small">Edad</div>
                            <strong>{{ $paciente->edad }} años</strong>
                        </div>
                        <div class="col">
                            <div class="text-muted small">Tipo Sangre</div>
                            <strong>{{ $paciente->tipo_sangre }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Estadísticas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="stat-item mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Cirugías Totales</span>
                            <span class="badge bg-primary">{{ $paciente->cirugias->count() }}</span>
                        </div>
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar" role="progressbar" style="width: 100%"></div>
                        </div>
                    </div>
                    <div class="stat-item mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Cirugías Completadas</span>
                            <span class="badge bg-success">
                                {{ $paciente->cirugias->where('estado_cirugia', 'completada')->count() }}
                            </span>
                        </div>
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: {{ $paciente->cirugias->count() > 0 ? 
                                 ($paciente->cirugias->where('estado_cirugia', 'completada')->count() / $paciente->cirugias->count() * 100) : 0 }}%">
                            </div>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Cirugías Pendientes</span>
                            <span class="badge bg-warning">
                                {{ $paciente->cirugias->whereIn('estado_cirugia', ['programada', 'en_proceso'])->count() }}
                            </span>
                        </div>
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar bg-warning" role="progressbar" 
                                 style="width: {{ $paciente->cirugias->count() > 0 ? 
                                 ($paciente->cirugias->whereIn('estado_cirugia', ['programada', 'en_proceso'])->count() / $paciente->cirugias->count() * 100) : 0 }}%">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Última Actividad -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>Última Actividad
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @if($paciente->ultima_visita)
                            <div class="timeline-item">
                                <i class="fas fa-circle text-primary"></i>
                                <div class="timeline-content">
                                    <div class="text-muted small">
                                        {{ \Carbon\Carbon::parse($paciente->ultima_visita)->format('d/m/Y H:i') }}
                                    </div>
                                    <div>Última visita registrada</div>
                                </div>
                            </div>
                        @endif
                        @if($paciente->updated_at)
                            <div class="timeline-item">
                                <i class="fas fa-circle text-info"></i>
                                <div class="timeline-content">
                                    <div class="text-muted small">
                                        {{ $paciente->updated_at->format('d/m/Y H:i') }}
                                    </div>
                                    <div>Última actualización de datos</div>
                                </div>
                            </div>
                        @endif
                        @if($paciente->created_at)
                            <div class="timeline-item">
                                <i class="fas fa-circle text-success"></i>
                                <div class="timeline-content">
                                    <div class="text-muted small">
                                        {{ $paciente->created_at->format('d/m/Y H:i') }}
                                    </div>
                                    <div>Registro inicial del paciente</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .avatar-xl {
        width: 120px;
        height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .avatar-xl img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .detail-item {
        margin-bottom: 1.5rem;
    }

    .detail-item:last-child {
        margin-bottom: 0;
    }

    .detail-item label {
        display: block;
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
    }

    .detail-item p {
        margin-bottom: 0;
    }

    .timeline {
        position: relative;
        padding-left: 1.5rem;
    }

    .timeline-item {
        position: relative;
        padding-bottom: 1.5rem;
    }

    .timeline-item:last-child {
        padding-bottom: 0;
    }

    .timeline-item i {
        position: absolute;
        left: -1.5rem;
        top: 0.25rem;
        font-size: 0.5rem;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -1.3rem;
        top: 0.5rem;
        bottom: -0.5rem;
        width: 1px;
        background-color: #e9ecef;
    }

    .timeline-item:last-child::before {
        display: none;
    }

    .stat-item {
        padding: 0.5rem 0;
    }

    .progress {
        background-color: #e9ecef;
    }

    .badge {
        padding: 0.5rem 1rem;
        font-weight: 500;
    }
</style>
@endpush
