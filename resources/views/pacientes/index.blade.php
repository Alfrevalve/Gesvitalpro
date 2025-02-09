@extends('layouts.app')

@section('title', 'Gestión de Pacientes')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-users me-2"></i>Gestión de Pacientes
        </h1>
        <a href="{{ route('pacientes.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nuevo Paciente
        </a>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('pacientes.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control" value="{{ request('nombre') }}" placeholder="Buscar por nombre">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Identificación</label>
                    <input type="text" name="id_number" class="form-control" value="{{ request('id_number') }}" placeholder="DNI/Pasaporte">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="activo" {{ request('estado') == 'activo' ? 'selected' : '' }}>Activo</option>
                        <option value="inactivo" {{ request('estado') == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-2"></i>Filtrar
                    </button>
                    <a href="{{ route('pacientes.index') }}" class="btn btn-secondary">
                        <i class="fas fa-undo me-2"></i>Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Pacientes -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Paciente</th>
                            <th>Identificación</th>
                            <th>Edad</th>
                            <th>Tipo Sangre</th>
                            <th>Estado</th>
                            <th>Última Visita</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pacientes as $paciente)
                            <tr>
                                <td>{{ $paciente->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-2">
                                            @if($paciente->foto)
                                                <img src="{{ asset('storage/' . $paciente->foto) }}" 
                                                     class="rounded-circle" 
                                                     alt="Foto de {{ $paciente->nombre }}">
                                            @else
                                                <i class="fas fa-user-circle fa-2x text-secondary"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $paciente->nombre }}</div>
                                            <small class="text-muted">{{ $paciente->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $paciente->id_number }}</td>
                                <td>{{ $paciente->edad }} años</td>
                                <td>{{ $paciente->tipo_sangre }}</td>
                                <td>
                                    @if($paciente->estado == 'activo')
                                        <span class="badge bg-success">
                                            <i class="fas fa-check me-1"></i>Activo
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="fas fa-times me-1"></i>Inactivo
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($paciente->ultima_visita)
                                        <span class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            {{ \Carbon\Carbon::parse($paciente->ultima_visita)->format('d/m/Y') }}
                                        </span>
                                    @else
                                        <span class="text-muted">Sin visitas</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('pacientes.show', $paciente->id) }}" 
                                           class="btn btn-sm btn-info me-2"
                                           data-bs-toggle="tooltip"
                                           title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('pacientes.edit', $paciente->id) }}" 
                                           class="btn btn-sm btn-warning me-2"
                                           data-bs-toggle="tooltip"
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deletePacienteModal{{ $paciente->id }}"
                                                title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>

                                    <!-- Modal de Eliminación -->
                                    <div class="modal fade" id="deletePacienteModal{{ $paciente->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Confirmar Eliminación</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    ¿Está seguro que desea eliminar al paciente {{ $paciente->nombre }}?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                    <form action="{{ route('pacientes.destroy', $paciente->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">
                                                            <i class="fas fa-trash me-2"></i>Eliminar
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-info-circle me-2"></i>No hay pacientes registrados
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div>
                    Mostrando {{ $pacientes->firstItem() ?? 0 }} - {{ $pacientes->lastItem() ?? 0 }} de {{ $pacientes->total() }} pacientes
                </div>
                {{ $pacientes->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .avatar-sm {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .avatar-sm img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .table td {
        vertical-align: middle;
    }

    .badge {
        font-size: 0.85em;
    }
</style>
@endpush

@push('scripts')
<script>
    // Activar tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
</script>
@endpush
