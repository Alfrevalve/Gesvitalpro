<x-app-layout>
<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary">
            <i class="fas fa-chart-pie"></i> Reporte de Cobertura - Instituciones
        </h2>
        <a href="{{ route('instituciones.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <!-- Filtros -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('instituciones.reporte-cobertura') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                    <input type="date" 
                           class="form-control" 
                           id="fecha_inicio" 
                           name="fecha_inicio" 
                           value="{{ $fechaInicio->format('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <label for="fecha_fin" class="form-label">Fecha Fin</label>
                    <input type="date" 
                           class="form-control" 
                           id="fecha_fin" 
                           name="fecha_fin" 
                           value="{{ $fechaFin->format('Y-m-d') }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Resumen de Cobertura -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50">Total Instituciones</h6>
                            <h2 class="mb-0">{{ $estadisticas['total_instituciones'] }}</h2>
                        </div>
                        <i class="fas fa-hospital fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50">Instituciones Visitadas</h6>
                            <h2 class="mb-0">{{ $estadisticas['instituciones_visitadas'] }}</h2>
                        </div>
                        <i class="fas fa-check-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50">Porcentaje de Cobertura</h6>
                            <h2 class="mb-0">{{ number_format($estadisticas['porcentaje_cobertura'], 1) }}%</h2>
                        </div>
                        <i class="fas fa-percentage fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Instituciones -->
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-table"></i> Detalle por Institución</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Institución</th>
                            <th>Tipo</th>
                            <th>Dirección</th>
                            <th class="text-center">Visitas en el Periodo</th>
                            <th class="text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($instituciones as $institucion)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="institution-icon me-2 {{ strtolower($institucion->tipo) }}">
                                            @if($institucion->tipo === 'hospital')
                                                <i class="fas fa-hospital"></i>
                                            @elseif($institucion->tipo === 'clinica')
                                                <i class="fas fa-clinic-medical"></i>
                                            @else
                                                <i class="fas fa-house-medical"></i>
                                            @endif
                                        </div>
                                        {{ $institucion->nombre }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge tipo-{{ strtolower($institucion->tipo) }}">
                                        {{ ucfirst($institucion->tipo) }}
                                    </span>
                                </td>
                                <td>
                                    <i class="fas fa-map-marker-alt text-danger me-2"></i>
                                    {{ $institucion->direccion }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary">{{ $institucion->visitas_count }}</span>
                                </td>
                                <td class="text-center">
                                    @if($institucion->visitas_count > 0)
                                        <span class="badge bg-success">Visitada</span>
                                    @else
                                        <span class="badge bg-danger">Sin Visitas</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</x-app-layout>

<style>
<style>
    .institution-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
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
    .badge {
        font-weight: 500;
        padding: 0.5em 0.75em;
    }
    .card {
        border: none;
        border-radius: 0.5rem;
    }
    .table td {
        vertical-align: middle;
    }
</style>
</style>
