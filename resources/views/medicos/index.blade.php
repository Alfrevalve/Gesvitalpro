@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary">
            <i class="bi bi-person-vcard"></i> Gestión de Médicos
        </h2>
        <a href="{{ route('medicos.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nuevo Médico
        </a>
    </div>

    <!-- Filtros -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('medicos.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="institucion_id" class="form-label">Filtrar por Institución</label>
                    <select name="institucion_id" id="institucion_id" class="form-select">
                        <option value="">Todas las instituciones</option>
                        @foreach($instituciones as $id => $nombre)
                            <option value="{{ $id }}" {{ request('institucion_id') == $id ? 'selected' : '' }}>
                                {{ $nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel"></i> Filtrar
                    </button>
                    @if(request()->has('institucion_id'))
                        <a href="{{ route('medicos.index') }}" class="btn btn-secondary ms-2">
                            <i class="bi bi-x-circle"></i> Limpiar
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de Médicos -->
    <div class="card shadow-sm">
        <div class="card-body">
            @if($medicos->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-person-vcard fs-1 text-muted mb-3"></i>
                    <p class="h5 text-muted">No se encontraron médicos registrados</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Especialidad</th>
                                <th>Institución</th>
                                <th>Contacto</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($medicos as $medico)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle bg-primary text-white me-2">
                                                {{ strtoupper(substr($medico->nombre, 0, 1)) }}
                                            </div>
                                            {{ $medico->nombre }}
                                        </div>
                                    </td>
                                    <td>{{ $medico->especialidad }}</td>
                                    <td>{{ $medico->institucion->nombre }}</td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span><i class="bi bi-telephone text-muted me-2"></i>{{ $medico->telefono }}</span>
                                            @if($medico->email)
                                                <span><i class="bi bi-envelope text-muted me-2"></i>{{ $medico->email }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="{{ route('medicos.show', $medico) }}"
                                               class="btn btn-sm btn-info"
                                               title="Ver detalles">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('medicos.edit', $medico) }}"
                                               class="btn btn-sm btn-warning"
                                               title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('medicos.destroy', $medico) }}"
                                                  method="POST"
                                                  class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="btn btn-sm btn-danger"
                                                        title="Eliminar"
                                                        onclick="return confirm('¿Está seguro de eliminar este médico?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="d-flex justify-content-end mt-3">
                    {{ $medicos->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
    .avatar-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }
    .table td {
        vertical-align: middle;
    }
    .btn-group-sm > .btn, .btn-sm {
        padding: 0.25rem 0.5rem;
    }
</style>
</style>
