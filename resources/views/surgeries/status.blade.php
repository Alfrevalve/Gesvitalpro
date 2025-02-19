<x-app-layout>
    <div class="pagetitle">
        <h1>Estado de Cirugías</h1>
    </div>

    <section class="section">
        <div class="row">
            <!-- Estadísticas -->
            <div class="col-lg-12">
                <div class="row">
                    <div class="col-xxl-4 col-md-4">
                        <div class="card info-card sales-card">
                            <div class="card-body">
                                <h5 class="card-title">Total Cirugías</h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-hospital"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6>{{ $stats['total'] }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-4 col-md-4">
                        <div class="card info-card customers-card">
                            <div class="card-body">
                                <h5 class="card-title">Pendientes</h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-clock"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6>{{ $stats['pending'] }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-4 col-md-4">
                        <div class="card info-card revenue-card">
                            <div class="card-body">
                                <h5 class="card-title">Completadas</h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-check-circle"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6>{{ $stats['completed'] }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista de Cirugías -->
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Lista de Cirugías</h5>

                        <div class="table-responsive">
                            <table class="table table-striped datatable">
                                <thead>
                                    <tr>
                                        <th scope="col">ID</th>
                                        <th scope="col">Línea</th>
                                        <th scope="col">Descripción</th>
                                        <th scope="col">Equipos</th>
                                        <th scope="col">Personal</th>
                                        <th scope="col">Estado</th>
                                        <th scope="col">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($surgeries as $surgery)
                                        <tr>
                                            <td>{{ $surgery->id }}</td>
                                            <td>{{ $surgery->line->name }}</td>
                                            <td>{{ Str::limit($surgery->description, 50) }}</td>
                                            <td>{{ $surgery->equipment->count() }}</td>
                                            <td>{{ $surgery->staff->count() }}</td>
                                            <td>
                                                @php
                                                    $statusClass = match($surgery->status) {
                                                        'completed' => 'success',
                                                        'pending' => 'warning',
                                                        'in_progress' => 'primary',
                                                        'cancelled' => 'danger',
                                                        default => 'secondary'
                                                    };
                                                    $statusLabel = match($surgery->status) {
                                                        'completed' => 'Completada',
                                                        'pending' => 'Pendiente',
                                                        'in_progress' => 'En Progreso',
                                                        'cancelled' => 'Cancelada',
                                                        default => 'Desconocido'
                                                    };
                                                @endphp
                                                <span class="badge bg-{{ $statusClass }}">{{ $statusLabel }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <a href="{{ route('surgeries.show', $surgery) }}" class="btn btn-info btn-sm">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    @if($surgery->status === 'pending')
                                                        <form action="{{ route('surgeries.update-status', $surgery) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="status" value="in_progress">
                                                            <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('¿Iniciar esta cirugía?')">
                                                                <i class="bi bi-play"></i>
                                                            </button>
                                                        </form>
                                                    @elseif($surgery->status === 'in_progress')
                                                        <form action="{{ route('surgeries.update-status', $surgery) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="status" value="completed">
                                                            <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('¿Marcar esta cirugía como completada?')">
                                                                <i class="bi bi-check-lg"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">
                                                <div class="alert alert-info mb-0">
                                                    <i class="bi bi-info-circle me-2"></i>
                                                    No hay cirugías registradas
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($surgeries->hasPages())
                            <div class="d-flex justify-content-end mt-3">
                                {{ $surgeries->links() }}
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
