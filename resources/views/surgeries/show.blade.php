<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Detalles de Cirugía</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075);
        }
        .detail-label {
            font-weight: 600;
            color: #6c757d;
        }
        .badge {
            font-weight: 500;
            padding: 0.5em 0.75em;
        }
    </style>
</head>
<body>
    @include('layouts.navbar')

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Detalles de Cirugía</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('surgeries.index') }}">Cirugías</a></li>
                        <li class="breadcrumb-item active">Detalles</li>
                    </ol>
                </nav>
            </div>
            <div class="btn-group">
                <a href="{{ route('surgeries.edit', $surgery) }}" class="btn btn-warning">
                    <i class="bi bi-pencil me-1"></i> Editar
                </a>
                <a href="{{ route('surgeries.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Volver
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Información Principal -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Información de la Cirugía</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="detail-label">Estado</div>
                                <span class="badge bg-{{ $surgery->status_color }}">
                                    {{ $surgery->status_text }}
                                </span>
                            </div>
                            <div class="col-md-6">
                                <div class="detail-label">Línea</div>
                                <div>{{ $surgery->line->name }}</div>
                            </div>
                            @if($surgery->patient_name)
                            <div class="col-md-6">
                                <div class="detail-label">Paciente</div>
                                <div>{{ $surgery->patient_name }}</div>
                            </div>
                            @endif
                            @if($surgery->surgery_date)
                            <div class="col-md-6">
                                <div class="detail-label">Fecha de Cirugía</div>
                                <div>{{ $surgery->surgery_date->format('d/m/Y H:i') }}</div>
                            </div>
                            @endif
                            @if($surgery->admission_date)
                            <div class="col-md-6">
                                <div class="detail-label">Fecha de Admisión</div>
                                <div>{{ $surgery->admission_date->format('d/m/Y H:i') }}</div>
                            </div>
                            @endif
                            @if($surgery->description)
                            <div class="col-12">
                                <div class="detail-label">Descripción</div>
                                <div>{{ $surgery->description }}</div>
                            </div>
                            @endif
                            @if($surgery->notes)
                            <div class="col-12">
                                <div class="detail-label">Notas</div>
                                <div>{{ $surgery->notes }}</div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información Lateral -->
            <div class="col-md-4">
                <!-- Institución y Médico -->
                @if($surgery->institucion || $surgery->medico)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Institución y Médico</h5>
                    </div>
                    <div class="card-body">
                        @if($surgery->institucion)
                        <div class="mb-3">
                            <div class="detail-label">Institución</div>
                            <div>{{ $surgery->institucion->nombre }}</div>
                        </div>
                        @endif
                        @if($surgery->medico)
                        <div>
                            <div class="detail-label">Médico</div>
                            <div>{{ $surgery->medico->nombre }}</div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Equipamiento -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Equipamiento</h5>
                    </div>
                    <div class="card-body">
                        @if($surgery->equipment->isNotEmpty())
                            <ul class="list-group list-group-flush">
                                @foreach($surgery->equipment as $equipment)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        {{ $equipment->name }}
                                        <span class="badge bg-{{ $equipment->status === 'available' ? 'success' : 'warning' }}">
                                            {{ $equipment->status }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted mb-0">No hay equipamiento asignado</p>
                        @endif
                    </div>
                </div>

                <!-- Personal -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Personal Asignado</h5>
                    </div>
                    <div class="card-body">
                        @if($surgery->staff->isNotEmpty())
                            <ul class="list-group list-group-flush">
                                @foreach($surgery->staff as $staff)
                                    <li class="list-group-item">{{ $staff->name }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted mb-0">No hay personal asignado</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer mt-5 py-3 bg-white border-top">
        <div class="container text-center">
            <span class="text-muted">© {{ date('Y') }} GesBio. Todos los derechos reservados.</span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
</body>
</html>
