@extends('layouts.app')

@section('content')
    <div class="pagetitle">
        <div class="d-flex justify-content-between align-items-center">
            <h1>Visitas</h1>
            <a href="{{ route('visitas.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Nueva Visita
            </a>
        </div>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <!-- Estadísticas de Visitas -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card info-card sales-card">
                            <div class="card-body">
                                <h6 class="card-title">Total Visitas</h6>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-calendar-check"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h4>{{ $visitas->total() }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card info-card revenue-card">
                            <div class="card-body">
                                <h6 class="card-title">Completadas</h6>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-check-circle"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h4>{{ $visitas->where('estado', 'realizada')->count() }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card info-card customers-card">
                            <div class="card-body">
                                <h6 class="card-title">Pendientes</h6>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-clock"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h4>{{ $visitas->where('estado', 'programada')->count() }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card info-card">
                            <div class="card-body">
                                <h6 class="card-title">Instituciones</h6>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-building"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h4>{{ $instituciones_count }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Lista de Visitas</h5>

                        <div class="table-responsive">
                            <table class="table table-striped datatable">
                                <thead>
                                    <tr>
                                        <th scope="col">ID</th>
                                        <th scope="col">Fecha</th>
                                        <th scope="col">Institución</th>
                                        <th scope="col">Médico</th>
                                        <th scope="col">Asesor</th>
                                        <th scope="col">Estado</th>
                                        <th scope="col">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($visitas as $visita)
                                        <tr>
                                            <td>{{ $visita->id }}</td>
                                            <td>{{ $visita->fecha_hora->format('d/m/Y H:i') }}</td>
                                            <td>{{ $visita->institucion->nombre }}</td>
                                            <td>{{ $visita->medico->nombre }}</td>
                                            <td>{{ $visita->asesor->name }}</td>
                                            <td>
                                                @php
                                                    $statusClass = match($visita->estado) {
                                                        'realizada' => 'success',
                                                        'programada' => 'warning',
                                                        'cancelada' => 'danger',
                                                        default => 'secondary'
                                                    };
                                                    $statusLabel = match($visita->estado) {
                                                        'realizada' => 'Completada',
                                                        'programada' => 'Pendiente',
                                                        'cancelada' => 'Cancelada',
                                                        default => 'Desconocido'
                                                    };
                                                @endphp
                                                <span class="badge bg-{{ $statusClass }}">{{ $statusLabel }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <a href="{{ route('visitas.show', $visita) }}" class="btn btn-info btn-sm">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="{{ route('visitas.edit', $visita) }}" class="btn btn-warning btn-sm">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    @if($visita->estado === 'programada')
                                                        <form action="{{ route('visitas.destroy', $visita) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro de cancelar esta visita?')">
                                                                <i class="bi bi-x-lg"></i>
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
                                                    No hay visitas registradas
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($visitas->hasPages())
                            <div class="d-flex justify-content-end mt-3">
                                {{ $visitas->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

@push('styles')
<style>
    .info-card {
        border-radius: 0.5rem;
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    }
    .info-card .card-icon {
        width: 50px;
        height: 50px;
        font-size: 1.5rem;
    }
    .info-card h6 {
        font-size: 1rem;
        color: #012970;
        margin: 0 0 0.5rem 0;
    }
    .info-card h4 {
        font-size: 2rem;
        color: #012970;
        font-weight: 700;
        margin: 0;
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
    .pagination {
        margin-bottom: 0;
    }
</style>
