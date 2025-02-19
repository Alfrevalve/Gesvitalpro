<x-app-layout>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="text-primary">
                    <i class="fas fa-chart-line"></i> Dashboard - {{ $line->name }}
                </h2>
                <div>
                    <a href="{{ route('lines.show', $line) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                    <a href="{{ route('lines.schedule', $line) }}" class="btn btn-success">
                        <i class="fas fa-calendar-alt"></i> Agenda
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Próximas Cirugías -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="fas fa-procedures"></i> Próximas Cirugías</h5>
                </div>
                <div class="card-body">
                    @if($upcomingSurgeries->count() > 0)
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Descripción</th>
                                        <th>Equipos</th>
                                        <th>Personal</th>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($upcomingSurgeries as $surgery)
                                        <tr>
                                            <td>{{ $surgery->id }}</td>
                                            <td>{{ Str::limit($surgery->description, 50) }}</td>
                                            <td>{{ $surgery->equipment->count() }} equipos</td>
                                            <td>{{ $surgery->staff->count() }} personas</td>
                                            <td>{{ $surgery->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <a href="{{ route('surgeries.show', $surgery) }}" 
                                                   class="btn btn-sm btn-info"
                                                   title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">No hay cirugías programadas próximamente.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Equipos en Mantenimiento -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-wrench"></i> Equipos en Mantenimiento</h5>
                </div>
                <div class="card-body">
                    @if($equipmentMaintenance->count() > 0)
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Equipo</th>
                                        <th>Última Actualización</th>
                                        <th>Próximo Mantenimiento</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($equipmentMaintenance as $equipment)
                                        <tr>
                                            <td>{{ $equipment->name }}</td>
                                            <td>{{ $equipment->updated_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                @if($equipment->next_maintenance)
                                                    <span class="badge bg-{{ Carbon\Carbon::parse($equipment->next_maintenance)->isPast() ? 'danger' : 'warning' }}">
                                                        {{ Carbon\Carbon::parse($equipment->next_maintenance)->format('d/m/Y') }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">No programado</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('equipment.show', $equipment) }}" 
                                                   class="btn btn-sm btn-info"
                                                   title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">No hay equipos en mantenimiento actualmente.</p>
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
        margin-bottom: 1rem;
    }
    .table th {
        border-top: none;
    }
    .badge {
        font-size: 0.875rem;
    }
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
</style>
</style>
