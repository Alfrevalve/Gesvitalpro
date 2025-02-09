@extends('layouts.app')

@section('title', 'Detalles de Cirugía')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-procedures me-2"></i>Detalles de Cirugía
        </h1>
        <div>
            <a href="{{ route('cirugias.edit', $cirugia->id) }}" class="btn btn-warning me-2">
                <i class="fas fa-edit me-2"></i>Editar
            </a>
            <a href="{{ route('cirugias.index') }}" class="btn btn-secondary">
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
                        <i class="fas fa-info-circle me-2"></i>Información Principal
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="text-muted">Fecha y Hora</label>
                                <p class="h5">{{ \Carbon\Carbon::parse($cirugia->fecha_cirugia)->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="text-muted">Duración Estimada</label>
                                <p class="h5">{{ $cirugia->duracion_estimada }} minutos</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="text-muted">Estado</label>
                                <p>
                                    @switch($cirugia->estado_cirugia)
                                        @case('programada')
                                            <span class="badge bg-info">
                                                <i class="fas fa-calendar me-1"></i>Programada
                                            </span>
                                            @break
                                        @case('en_proceso')
                                            <span class="badge bg-warning">
                                                <i class="fas fa-clock me-1"></i>En Proceso
                                            </span>
                                            @break
                                        @case('completada')
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>Completada
                                            </span>
                                            @break
                                        @case('cancelada')
                                            <span class="badge bg-danger">
                                                <i class="fas fa-times me-1"></i>Cancelada
                                            </span>
                                            @break
                                    @endswitch
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="text-muted">Especialidad</label>
                                <p class="h5">{{ $cirugia->especialidad }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detalles Médicos -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-md me-2"></i>Detalles Médicos
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="text-muted">Tipo de Anestesia</label>
                                <p class="h5">{{ ucfirst($cirugia->tipo_anestesia) }}</p>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="detail-item">
                                <label class="text-muted">Requisitos Especiales</label>
                                <p class="h5">{{ $cirugia->requisitos_especiales ?: 'No especificados' }}</p>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="detail-item">
                                <label class="text-muted">Notas Adicionales</label>
                                <p class="h5">{{ $cirugia->notas_adicionales ?: 'Sin notas adicionales' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Información del Paciente -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user me-2"></i>Información del Paciente
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="avatar-lg mx-auto mb-3">
                            <i class="fas fa-user-circle fa-4x text-secondary"></i>
                        </div>
                        <h4>{{ $cirugia->paciente->name }}</h4>
                        <p class="text-muted">ID: {{ $cirugia->paciente->id_number }}</p>
                    </div>
                    <hr>
                    <div class="detail-item mb-3">
                        <label class="text-muted">Edad</label>
                        <p class="h5">{{ $cirugia->paciente->edad }} años</p>
                    </div>
                    <div class="detail-item mb-3">
                        <label class="text-muted">Tipo de Sangre</label>
                        <p class="h5">{{ $cirugia->paciente->tipo_sangre }}</p>
                    </div>
                    <div class="detail-item">
                        <label class="text-muted">Alergias</label>
                        <p class="h5">{{ $cirugia->paciente->alergias ?: 'Ninguna registrada' }}</p>
                    </div>
                </div>
            </div>

            <!-- Equipo Médico -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-users me-2"></i>Equipo Médico
                    </h5>
                </div>
                <div class="card-body">
                    <div class="detail-item mb-4">
                        <label class="text-muted">Cirujano Principal</label>
                        <div class="d-flex align-items-center mt-2">
                            <div class="avatar-sm me-3">
                                <i class="fas fa-user-md fa-2x text-primary"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">Dr. {{ $cirugia->cirujano }}</h5>
                                <small class="text-muted">Cirujano Principal</small>
                            </div>
                        </div>
                    </div>
                    <div class="detail-item">
                        <label class="text-muted">Instrumentista</label>
                        <div class="d-flex align-items-center mt-2">
                            <div class="avatar-sm me-3">
                                <i class="fas fa-user-nurse fa-2x text-info"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">{{ $cirugia->instrumentista }}</h5>
                                <small class="text-muted">Instrumentista</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Institución -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-hospital me-2"></i>Institución
                    </h5>
                </div>
                <div class="card-body">
                    <div class="detail-item">
                        <h5>{{ $cirugia->institucion->nombre }}</h5>
                        <p class="text-muted mb-0">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            {{ $cirugia->institucion->direccion }}
                        </p>
                        <p class="text-muted mb-0">
                            <i class="fas fa-phone me-2"></i>
                            {{ $cirugia->institucion->telefono }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
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

    .avatar-lg {
        width: 80px;
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .avatar-sm {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .badge {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
    }

    .card-header {
        padding: 1rem 1.5rem;
    }

    .card-body {
        padding: 1.5rem;
    }
</style>
@endpush
