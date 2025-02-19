@extends('layouts.app')

@section('title', 'Reportes de Almacén')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Reportes de Almacén</h1>
        <div>
            <a href="{{ route('storage.index') }}" class="btn btn-secondary">
                <i class="bi bi-list"></i> Ver Lista
            </a>
            <a href="{{ route('storage.kanban') }}" class="btn btn-primary ms-2">
                <i class="bi bi-kanban"></i> Ver Kanban
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Tarjetas de Estadísticas -->
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Total Solicitudes</h6>
                            <h2 class="mb-0">{{ $stats['total_requests'] }}</h2>
                        </div>
                        <i class="bi bi-clipboard-data display-6"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-dark mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Pendientes</h6>
                            <h2 class="mb-0">{{ $stats['pending_requests'] }}</h2>
                        </div>
                        <i class="bi bi-clock display-6"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">En Proceso</h6>
                            <h2 class="mb-0">{{ $stats['in_progress_requests'] }}</h2>
                        </div>
                        <i class="bi bi-gear display-6"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Completadas</h6>
                            <h2 class="mb-0">{{ $stats['completed_requests'] }}</h2>
                        </div>
                        <i class="bi bi-check-circle display-6"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Gráfico de Estado de Solicitudes -->
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Estado de Solicitudes</h5>
                </div>
                <div class="card-body">
                    <canvas id="requestsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Porcentajes -->
        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Distribución de Estados</h5>
                </div>
                <div class="card-body">
                    <canvas id="statusPieChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}
.display-6 {
    opacity: 0.8;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Datos para el gráfico de barras
    const barChart = new Chart(document.getElementById('requestsChart'), {
        type: 'bar',
        data: {
            labels: ['Pendientes', 'En Proceso', 'Completadas'],
            datasets: [{
                label: 'Cantidad de Solicitudes',
                data: [
                    {{ $stats['pending_requests'] }},
                    {{ $stats['in_progress_requests'] }},
                    {{ $stats['completed_requests'] }}
                ],
                backgroundColor: [
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(23, 162, 184, 0.8)',
                    'rgba(40, 167, 69, 0.8)'
                ],
                borderColor: [
                    'rgb(255, 193, 7)',
                    'rgb(23, 162, 184)',
                    'rgb(40, 167, 69)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // Datos para el gráfico circular
    const pieChart = new Chart(document.getElementById('statusPieChart'), {
        type: 'pie',
        data: {
            labels: ['Pendientes', 'En Proceso', 'Completadas'],
            datasets: [{
                data: [
                    {{ $stats['pending_requests'] }},
                    {{ $stats['in_progress_requests'] }},
                    {{ $stats['completed_requests'] }}
                ],
                backgroundColor: [
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(23, 162, 184, 0.8)',
                    'rgba(40, 167, 69, 0.8)'
                ],
                borderColor: [
                    'rgb(255, 193, 7)',
                    'rgb(23, 162, 184)',
                    'rgb(40, 167, 69)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});
</script>
@endpush
