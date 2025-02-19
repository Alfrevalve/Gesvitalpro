<x-app-layout>
    <div class="pagetitle">
        <h1>Mantenimiento de Equipos</h1>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Equipos que Requieren Mantenimiento</h5>

                        <!-- Estadísticas -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card info-card sales-card">
                                    <div class="card-body">
                                        <h5 class="card-title">Total Equipos</h5>
                                        <div class="d-flex align-items-center">
                                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                <i class="bi bi-tools"></i>
                                            </div>
                                            <div class="ps-3">
                                                <h6>{{ $equipment->total() }}</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card info-card customers-card">
                                    <div class="card-body">
                                        <h5 class="card-title">Próximo Mantenimiento</h5>
                                        <div class="d-flex align-items-center">
                                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                <i class="bi bi-calendar-check"></i>
                                            </div>
                                            <div class="ps-3">
                                                <h6>{{ $equipment->where('next_maintenance', '<=', now()->addDays(7))->count() }}</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card info-card revenue-card">
                                    <div class="card-body">
                                        <h5 class="card-title">Por Uso Excesivo</h5>
                                        <div class="d-flex align-items-center">
                                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                <i class="bi bi-activity"></i>
                                            </div>
                                            <div class="ps-3">
                                                <h6>{{ $equipment->where('surgeries_count', '>=', config('surgery.maintenance.surgeries_threshold', 50))->count() }}</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Lista de Equipos -->
                        <div class="table-responsive">
                            <table class="table table-striped datatable">
                                <thead>
                                    <tr>
                                        <th scope="col">ID</th>
                                        <th scope="col">Nombre</th>
                                        <th scope="col">Línea</th>
                                        <th scope="col">Último Mantenimiento</th>
                                        <th scope="col">Próximo Mantenimiento</th>
                                        <th scope="col">Cirugías Realizadas</th>
                                        <th scope="col">Estado</th>
                                        <th scope="col">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($equipment as $item)
                                        <tr>
                                            <td>{{ $item->id }}</td>
                                            <td>{{ $item->name }}</td>
                                            <td>{{ $item->line->name }}</td>
                                            <td>{{ $item->last_maintenance ? $item->last_maintenance->format('d/m/Y') : 'N/A' }}</td>
                                            <td>
                                                <span class="{{ $item->next_maintenance && $item->next_maintenance->isPast() ? 'text-danger' : '' }}">
                                                    {{ $item->next_maintenance ? $item->next_maintenance->format('d/m/Y') : 'N/A' }}
                                                </span>
                                            </td>
                                            <td>{{ $item->surgeries_count }}</td>
                                            <td>
                                                @php
                                                    $statusClass = match($item->status) {
                                                        'available' => 'success',
                                                        'in_use' => 'primary',
                                                        'maintenance' => 'warning',
                                                        default => 'secondary'
                                                    };
                                                    $statusLabel = match($item->status) {
                                                        'available' => 'Disponible',
                                                        'in_use' => 'En Uso',
                                                        'maintenance' => 'En Mantenimiento',
                                                        default => 'Desconocido'
                                                    };
                                                @endphp
                                                <span class="badge bg-{{ $statusClass }}">{{ $statusLabel }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <a href="{{ route('equipment.show', $item) }}" class="btn btn-info btn-sm">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    @if($item->status !== 'maintenance')
                                                        <form action="{{ route('equipment.update-status', $item) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="status" value="maintenance">
                                                            <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('¿Está seguro de marcar este equipo para mantenimiento?')">
                                                                <i class="bi bi-wrench"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">
                                                <div class="alert alert-info mb-0">
                                                    <i class="bi bi-info-circle me-2"></i>
                                                    No hay equipos que requieran mantenimiento
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($equipment->hasPages())
                            <div class="d-flex justify-content-end mt-3">
                                {{ $equipment->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        .info-card {
            padding-bottom: 10px;
        }
        .card-icon {
            width: 50px;
            height: 50px;
            font-size: 1.5rem;
        }
        .info-card h6 {
            font-size: 28px;
            color: #012970;
            font-weight: 700;
            margin: 0;
            padding: 0;
        }
        .card-title {
            padding: 20px 0 15px 0;
            font-size: 18px;
            font-weight: 500;
            color: #012970;
            font-family: "Poppins", sans-serif;
        }
        .sales-card .card-icon {
            color: #4154f1;
            background: #f6f6fe;
        }
        .revenue-card .card-icon {
            color: #2eca6a;
            background: #e0f8e9;
        }
        .customers-card .card-icon {
            color: #ff771d;
            background: #ffecdf;
        }
        .table th {
            color: #012970;
            font-weight: 600;
        }
        .table td {
            vertical-align: middle;
        }
        .btn-sm {
            padding: 0.25rem 0.5rem;
        }
        .badge {
            font-weight: 600;
            padding: 0.35em 0.65em;
        }
        .alert {
            display: flex;
            align-items: center;
        }
        .alert i {
            font-size: 1.2rem;
        }
    </style>
</x-app-layout>
