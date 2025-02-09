@extends('layouts.app')

@section('title', 'Gestión de Cirugías')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-procedures me-2"></i>Gestión de Cirugías
        </h1>
        <a href="{{ route('cirugias.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nueva Cirugía
        </a>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('cirugias.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Fecha</label>
                    <input type="date" name="fecha" class="form-control" value="{{ request('fecha') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="programada" {{ request('estado') == 'programada' ? 'selected' : '' }}>Programada</option>
                        <option value="en_proceso" {{ request('estado') == 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
                        <option value="completada" {{ request('estado') == 'completada' ? 'selected' : '' }}>Completada</option>
                        <option value="cancelada" {{ request('estado') == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Cirujano</label>
                    <input type="text" name="cirujano" class="form-control" value="{{ request('cirujano') }}" placeholder="Nombre del cirujano">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-2"></i>Filtrar
                    </button>
                    <a href="{{ route('cirugias.index') }}" class="btn btn-secondary">
                        <i class="fas fa-undo me-2"></i>Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Cirugías -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Paciente</th>
                            <th>Cirujano</th>
                            <th>Especialidad</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cirugias as $cirugia)
                            <tr>
                                <td>{{ $cirugia->id }}</td>
                                <td>{{ \Carbon\Carbon::parse($cirugia->fecha_cirugia)->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-2">
                                            <i class="fas fa-user-circle fa-2x text-secondary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $cirugia->paciente->name }}</div>
                                            <small class="text-muted">{{ $cirugia->paciente->id_number }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $cirugia->cirujano }}</td>
                                <td>{{ $cirugia->especialidad }}</td>
                                <td>
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
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('cirugias.show', $cirugia->id) }}" 
                                           class="btn btn-sm btn-info me-2"
                                           data-bs-toggle="tooltip"
                                           title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('cirugias.edit', $cirugia->id) }}" 
                                           class="btn btn-sm btn-warning me-2"
                                           data-bs-toggle="tooltip"
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteCirugiaModal{{ $cirugia->id }}"
                                                title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>

                                    <!-- Modal de Eliminación -->
                                    <div class="modal fade" id="deleteCirugiaModal{{ $cirugia->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Confirmar Eliminación</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    ¿Está seguro que desea eliminar esta cirugía?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                    <form action="{{ route('cirugias.destroy', $cirugia->id) }}" method="POST" class="d-inline">
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
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-info-circle me-2"></i>No hay cirugías registradas
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
                    Mostrando {{ $cirugias->firstItem() ?? 0 }} - {{ $cirugias->lastItem() ?? 0 }} de {{ $cirugias->total() }} cirugías
                </div>
                {{ $cirugias->links() }}
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
    // Activar todos los tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Confirmar eliminación
    $('.delete-cirugia').click(function(e) {
        e.preventDefault();
        if (confirm('¿Está seguro que desea eliminar esta cirugía?')) {
            $(this).closest('form').submit();
        }
    });
</script>
@endpush
