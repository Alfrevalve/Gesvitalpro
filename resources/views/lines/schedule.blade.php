<x-app-layout>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="text-primary">
                    <i class="fas fa-calendar-alt"></i> Agenda - {{ $line->name }}
                </h2>
                <div>
                    <a href="{{ route('lines.show', $line) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                    <a href="{{ route('lines.dashboard', $line) }}" class="btn btn-info">
                        <i class="fas fa-chart-line"></i> Dashboard
                    </a>
                    @if(Auth::user()->isAdmin() || Auth::user()->isGerente() || Auth::user()->isJefeLinea())
                    <a href="{{ route('surgeries.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nueva Cirugía
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Cirugías Programadas</h5>
                </div>
                <div class="card-body">
                    @if($surgeries->count() > 0)
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Descripción</th>
                                        <th>Equipos</th>
                                        <th>Personal</th>
                                        <th>Estado</th>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($surgeries as $surgery)
                                        <tr>
                                            <td>{{ $surgery->id }}</td>
                                            <td>{{ Str::limit($surgery->description, 50) }}</td>
                                            <td>
                                                <span class="badge bg-primary">
                                                    {{ $surgery->equipment->count() }} equipos
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    {{ $surgery->staff->count() }} personas
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $surgery->status_color }}">
                                                    {{ $surgery->status_text }}
                                                </span>
                                            </td>
                                            <td>{{ $surgery->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('surgeries.show', $surgery) }}" 
                                                       class="btn btn-sm btn-info"
                                                       title="Ver detalles">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if(Auth::user()->isAdmin() || Auth::user()->isGerente() || 
                                                        (Auth::user()->isJefeLinea() && Auth::user()->lines->contains($line)))
                                                        <a href="{{ route('surgeries.edit', $surgery) }}" 
                                                           class="btn btn-sm btn-warning"
                                                           title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form action="{{ route('surgeries.update-status', $surgery) }}" 
                                                              method="POST" 
                                                              class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="status" value="completed">
                                                            <button type="submit" 
                                                                    class="btn btn-sm btn-success"
                                                                    title="Marcar como completada"
                                                                    onclick="return confirm('¿Está seguro de marcar esta cirugía como completada?')">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('surgeries.update-status', $surgery) }}" 
                                                              method="POST" 
                                                              class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="status" value="cancelled">
                                                            <button type="submit" 
                                                                    class="btn btn-sm btn-danger"
                                                                    title="Cancelar cirugía"
                                                                    onclick="return confirm('¿Está seguro de cancelar esta cirugía?')">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end mt-3">
                            {{ $surgeries->links() }}
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>No hay cirugías programadas.
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
    .card {
        border: none;
        border-radius: 1rem;
    }
    .btn-group {
        gap: 0.25rem;
    }
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    .badge {
        font-size: 0.875rem;
    }
    .table th {
        background-color: #f8f9fa;
    }
</style>
</style>
