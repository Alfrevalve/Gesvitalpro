<x-app-layout>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="text-primary">
                    <i class="fas fa-project-diagram"></i> {{ $line->name }}
                </h2>
                <div>
                    <a href="{{ route('lines.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                    @if(Auth::user()->isAdmin() || Auth::user()->isGerente())
                        <a href="{{ route('lines.edit', $line) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Información General -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Información General</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Nombre:</dt>
                        <dd class="col-sm-8">{{ $line->name }}</dd>

                        <dt class="col-sm-4">Descripción:</dt>
                        <dd class="col-sm-8">{{ $line->description ?? 'No disponible' }}</dd>

                        <dt class="col-sm-4">Fecha de Creación:</dt>
                        <dd class="col-sm-8">{{ $line->created_at->format('d/m/Y H:i') }}</dd>

                        <dt class="col-sm-4">Última Actualización:</dt>
                        <dd class="col-sm-8">{{ $line->updated_at->format('d/m/Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Estadísticas</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Equipos -->
                        <div class="col-md-6 mb-3">
                            <div class="stats-card bg-light p-3 rounded">
                                <h6 class="text-primary"><i class="fas fa-tools"></i> Equipos</h6>
                                <div class="stats-grid">
                                    <div>
                                        <small class="text-muted">Total</small>
                                        <h4>{{ $equipmentStats['total'] }}</h4>
                                    </div>
                                    <div>
                                        <small class="text-muted">Disponibles</small>
                                        <h4>{{ $equipmentStats['available'] }}</h4>
                                    </div>
                                    <div>
                                        <small class="text-muted">En Uso</small>
                                        <h4>{{ $equipmentStats['in_use'] }}</h4>
                                    </div>
                                    <div>
                                        <small class="text-muted">Mantenimiento</small>
                                        <h4>{{ $equipmentStats['maintenance'] }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cirugías -->
                        <div class="col-md-6 mb-3">
                            <div class="stats-card bg-light p-3 rounded">
                                <h6 class="text-success"><i class="fas fa-procedures"></i> Cirugías</h6>
                                <div class="stats-grid">
                                    <div>
                                        <small class="text-muted">Total</small>
                                        <h4>{{ $surgeryStats['total'] }}</h4>
                                    </div>
                                    <div>
                                        <small class="text-muted">Pendientes</small>
                                        <h4>{{ $surgeryStats['pending'] }}</h4>
                                    </div>
                                    <div>
                                        <small class="text-muted">Completadas</small>
                                        <h4>{{ $surgeryStats['completed'] }}</h4>
                                    </div>
                                    <div>
                                        <small class="text-muted">Canceladas</small>
                                        <h4>{{ $surgeryStats['cancelled'] }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Personal -->
                        <div class="col-12">
                            <div class="stats-card bg-light p-3 rounded">
                                <h6 class="text-warning"><i class="fas fa-users"></i> Personal</h6>
                                <div class="stats-grid">
                                    <div>
                                        <small class="text-muted">Total</small>
                                        <h4>{{ $staffStats['total'] }}</h4>
                                    </div>
                                    <div>
                                        <small class="text-muted">Jefes</small>
                                        <h4>{{ $staffStats['jefes'] }}</h4>
                                    </div>
                                    <div>
                                        <small class="text-muted">Instrumentistas</small>
                                        <h4>{{ $staffStats['instrumentistas'] }}</h4>
                                    </div>
                                    <div>
                                        <small class="text-muted">Vendedores</small>
                                        <h4>{{ $staffStats['vendedores'] }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Personal Asignado -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="fas fa-users"></i> Personal Asignado</h5>
                </div>
                <div class="card-body">
                    @if($line->staff->count() > 0)
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Rol</th>
                                        <th>Email</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($line->staff as $staff)
                                        <tr>
                                            <td>{{ $staff->name }}</td>
                                            <td>{{ $staff->role->name }}</td>
                                            <td>{{ $staff->email }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">No hay personal asignado a esta línea.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Equipos -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-tools"></i> Equipos</h5>
                </div>
                <div class="card-body">
                    @if($line->equipment->count() > 0)
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Estado</th>
                                        <th>Último Mantenimiento</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($line->equipment as $equipment)
                                        <tr>
                                            <td>{{ $equipment->name }}</td>
                                            <td>
                                                <span class="badge bg-{{ $equipment->status === 'available' ? 'success' : ($equipment->status === 'in_use' ? 'warning' : 'danger') }}">
                                                    {{ $equipment->status }}
                                                </span>
                                            </td>
                                            <td>{{ optional($equipment->last_maintenance)->format('d/m/Y') ?? 'No registrado' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">No hay equipos asignados a esta línea.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
</x-app-layout>

<style>
<style>
    .stats-card {
        transition: transform 0.2s;
    }
    .stats-card:hover {
        transform: translateY(-5px);
    }
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
    .stats-grid h4 {
        margin: 0;
        font-size: 1.5rem;
    }
    .card {
        border: none;
        border-radius: 1rem;
    }
    .table th {
        border-top: none;
    }
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
</style>
