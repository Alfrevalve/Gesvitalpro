@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Resumen General -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Cirugías este mes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $estadisticas['cirugias']['mes_actual'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Pacientes Nuevos</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $estadisticas['pacientes']['nuevos_mes'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Items en Stock Bajo</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $estadisticas['inventario']['stock_bajo'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Valor del Inventario</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${{ number_format($estadisticas['inventario']['valor_total'], 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row">
        <!-- Gráfico de Cirugías -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Cirugías por Estado</h6>
                    <div class="dropdown no-arrow">
                        <select class="form-control" id="periodoCircugias">
                            <option value="semana">Esta Semana</option>
                            <option value="mes" selected>Este Mes</option>
                            <option value="año">Este Año</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="cirugiasPorEstadoChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico de Inventario -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Distribución de Inventario</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4">
                        <canvas id="inventarioChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Productos Críticos -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Productos en Estado Crítico</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Stock Actual</th>
                                    <th>Nivel Mínimo</th>
                                    <th>Consumo Mensual</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($inventarioCritico as $producto)
                                <tr>
                                    <td>{{ $producto['nombre'] }}</td>
                                    <td>{{ $producto['stock_actual'] }}</td>
                                    <td>{{ $producto['nivel_minimo'] }}</td>
                                    <td>{{ $producto['consumo_mensual'] }}</td>
                                    <td>
                                        <span class="badge badge-{{ $producto['stock_actual'] == 0 ? 'danger' : 'warning' }}">
                                            {{ $producto['stock_actual'] == 0 ? 'Sin Stock' : 'Stock Bajo' }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Predicciones -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Predicciones de Demanda</h6>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-primary" onclick="exportarDashboard('pdf')">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                        <button type="button" class="btn btn-sm btn-success" onclick="exportarDashboard('excel')">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Consumo Promedio</th>
                                    <th>Stock de Seguridad</th>
                                    <th>Punto de Reorden</th>
                                    <th>Predicción Próximo Mes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($predicciones as $prediccion)
                                <tr>
                                    <td>{{ $prediccion['nombre'] }}</td>
                                    <td>{{ round($prediccion['consumo_promedio'], 2) }}</td>
                                    <td>{{ $prediccion['stock_seguridad'] }}</td>
                                    <td>{{ $prediccion['punto_reorden'] }}</td>
                                    <td>{{ $prediccion['prediccion_siguiente_mes'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar gráficos
    initCirugiasChart();
    initInventarioChart();

    // Event listeners
    document.getElementById('periodoCircugias').addEventListener('change', function() {
        updateCirugiasChart(this.value);
    });
});

function initCirugiasChart() {
    fetch('/dashboard/chart-data?tipo=cirugias&periodo=mes')
        .then(response => response.json())
        .then(data => {
            const ctx = document.getElementById('cirugiasPorEstadoChart');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: data.datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                }
            });
        });
}

function initInventarioChart() {
    fetch('/dashboard/chart-data?tipo=inventario')
        .then(response => response.json())
        .then(data => {
            const ctx = document.getElementById('inventarioChart');
            new Chart(ctx, {
                type: 'doughnut',
                data: data.valorPorCategoria,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                }
            });
        });
}

function updateCirugiasChart(periodo) {
    fetch(`/dashboard/chart-data?tipo=cirugias&periodo=${periodo}`)
        .then(response => response.json())
        .then(data => {
            const chart = Chart.getChart('cirugiasPorEstadoChart');
            chart.data.labels = data.labels;
            chart.data.datasets = data.datasets;
            chart.update();
        });
}

function exportarDashboard(formato) {
    window.location.href = `/dashboard/exportar?formato=${formato}`;
}
</script>
@endpush

@push('styles')
<style>
.chart-area {
    position: relative;
    height: 300px;
    width: 100%;
}

.chart-pie {
    position: relative;
    height: 250px;
    width: 100%;
}

.border-left-primary {
    border-left: .25rem solid #4e73df!important;
}

.border-left-success {
    border-left: .25rem solid #1cc88a!important;
}

.border-left-warning {
    border-left: .25rem solid #f6c23e!important;
}

.border-left-info {
    border-left: .25rem solid #36b9cc!important;
}
</style>
@endpush
