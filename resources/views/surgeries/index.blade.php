@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3">Cirugías</h1>
        </div>
        <div class="col text-end">
            <a href="{{ route('surgeries.kanban') }}" class="btn btn-outline-secondary me-2">
                <i class="bi bi-kanban"></i> Vista Kanban
            </a>
            <a href="{{ route('surgeries.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nueva Cirugía
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($surgeries->isEmpty())
                <div class="text-center py-4">
                    <i class="bi bi-inbox display-4 text-muted"></i>
                    <p class="mt-3">No hay cirugías registradas</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Línea</th>
                                <th>Descripción</th>
                                <th>Estado</th>
                                <th>Equipamiento</th>
                                <th>Personal</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($surgeries as $surgery)
                                <tr>
                                    <td>{{ $surgery->id }}</td>
                                    <td>{{ $surgery->line->name }}</td>
                                    <td>{{ $surgery->description }}</td>
                                    <td>
                                        <span class="badge bg-{{ $surgery->status_color }}">
                                            {{ $surgery->status_text }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $surgery->equipment->pluck('name')->implode(', ') }}
                                    </td>
                                    <td>
                                        {{ $surgery->staff->pluck('name')->implode(', ') }}
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('surgeries.show', $surgery) }}" class="btn btn-sm btn-info" title="Ver detalles">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('surgeries.edit', $surgery) }}" class="btn btn-sm btn-warning" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('surgeries.destroy', $surgery) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar"
                                                        onclick="return confirm('¿Está seguro de eliminar esta cirugía?')">
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
                <div class="mt-4">
                    {{ $surgeries->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
