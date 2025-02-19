<x-app-layout>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2 class="mb-0">Estadísticas de Geolocalización</h2>
                        <div class="d-flex gap-2">
                            <a href="{{ route('geolocalizacion.mapa') }}" class="btn btn-outline-primary">
                                <i class="fas fa-map-marker-alt"></i> Ver Mapa
                            </a>
                            <button class="btn btn-outline-secondary" onclick="exportarEstadisticas()">
                                <i class="fas fa-download"></i> Exportar
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Filtros -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="region">Región:</label>
                                <select id="region" class="form-select" onchange="actualizarEstadisticas()">
                                    <option value="">Todas las regiones</option>
                                    @foreach($estadisticas['por_region'] ?? [] as $region => $datos)
                                        <option value="{{ $region }}" @selected($region === request('region'))>
                                            {{ $region }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Tarjetas de Resumen -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white stats-card">
                                <div class="card-body">
                                    <h5 class="card-title">Total Instituciones</h5>
                                    <h2 class="mb-0">{{ $estadisticas['total_instituciones'] }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white stats-card">
                                <div class="card-body">
                                    <h5 class="card-title">Con Ubicación</h5>
                                    <h2 class="mb-0">{{ $estadisticas['con_ubicacion'] }}</h2>
                                    <small>
                                        ({{ number_format(($estadisticas['con_ubicacion'] / $estadisticas['total_instituciones']) * 100, 1) }}%)
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white stats-card">
                                <div class="card-body">
                                    <h5 class="card-title">Sin Ubicación</h5>
                                    <h2 class="mb-0">{{ $estadisticas['sin_ubicacion'] }}</h2>
                                    <small>
                                        ({{ number_format(($estadisticas['sin_ubicacion'] / $estadisticas['total_instituciones']) * 100, 1) }}%)
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white stats-card">
                                <div class="card-body">
                                    <h5 class="card-title">Regiones Cubiertas</h5>
                                    <h2 class="mb-0">{{ count($estadisticas['por_region'] ?? []) }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gráficos -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Distribución por Tipo</h5>
                                    <div class="chart-container">
                                        <canvas id="tiposChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Distribución por Categoría</h5>
                                    <div class="chart-container">
                                        <canvas id="categoriasChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla Detallada -->
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title">Detalle por Región</h5>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Región</th>
                                            <th>Total</th>
                                            <th>Con Ubicación</th>
                                            <th>Sin Ubicación</th>
                                            <th>% Cobertura</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($estadisticas['por_region'] ?? [] as $region => $datos)
                                            <tr>
                                                <td>{{ $region }}</td>
                                                <td>{{ $datos['total'] }}</td>
                                                <td>{{ $datos['con_ubicacion'] }}</td>
                                                <td>{{ $datos['sin_ubicacion'] }}</td>
                                                <td>
                                                    {{ number_format(($datos['con_ubicacion'] / $datos['total']) * 100, 1) }}%
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary" 
                                                            onclick="verDetalleRegion('{{ $region }}')">
                                                        Ver Detalle
                                                    </button>
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
        </div>
    </div>
</div>

<!-- Modal de Detalle por Región -->
<div class="modal fade" id="modalDetalleRegion" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle de Región: <span id="region-nombre"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="detalle-region-content"></div>
            </div>
        </div>
    </div>
</div>
</x-app-layout>

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let tiposChart;
let categoriasChart;

document.addEventListener('DOMContentLoaded', function() {
    inicializarGraficos();
});

function inicializarGraficos() {
    // Gráfico de tipos
    const tiposCtx = document.getElementById('tiposChart').getContext('2d');
    tiposChart = new Chart(tiposCtx, {
        type: 'pie',
        data: {
            labels: @json(array_keys($estadisticas['por_tipo'] ?? [])),
            datasets: [{
                data: @json(array_values($estadisticas['por_tipo'] ?? [])),
                backgroundColor: [
                    '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
                    '#858796', '#5a5c69', '#2c9faf', '#3c5a9a', '#e83e8c'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Gráfico de categorías
    const categoriasCtx = document.getElementById('categoriasChart').getContext('2d');
    categoriasChart = new Chart(categoriasCtx, {
        type: 'doughnut',
        data: {
            labels: @json(array_keys($estadisticas['por_categoria'] ?? [])),
            datasets: [{
                data: @json(array_values($estadisticas['por_categoria'] ?? [])),
                backgroundColor: [
                    '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
                    '#858796', '#5a5c69', '#2c9faf', '#3c5a9a', '#e83e8c'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

function actualizarEstadisticas() {
    const region = document.getElementById('region').value;
    window.location.href = `${window.location.pathname}?region=${region}`;
}

function verDetalleRegion(region) {
    document.getElementById('region-nombre').textContent = region;
    
    fetch(`/geolocalizacion/estadisticas?region=${region}`)
        .then(response => response.json())
        .then(data => {
            const detalle = data.estadisticas;
            let content = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Distribución por Tipo</h6>
                        <ul class="list-group">
                            ${Object.entries(detalle.por_tipo).map(([tipo, cantidad]) => `
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    ${tipo}
                                    <span class="badge bg-primary rounded-pill">${cantidad}</span>
                                </li>
                            `).join('')}
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Distribución por Categoría</h6>
                        <ul class="list-group">
                            ${Object.entries(detalle.por_categoria).map(([categoria, cantidad]) => `
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    ${categoria}
                                    <span class="badge bg-primary rounded-pill">${cantidad}</span>
                                </li>
                            `).join('')}
                        </ul>
                    </div>
                </div>
            `;
            
            document.getElementById('detalle-region-content').innerHTML = content;
            new bootstrap.Modal(document.getElementById('modalDetalleRegion')).show();
        })
        .catch(error => {
            console.error('Error al cargar detalle de región:', error);
            alert('Error al cargar el detalle de la región');
        });
}

function exportarEstadisticas() {
    const region = document.getElementById('region').value;
    window.location.href = `/geolocalizacion/estadisticas/exportar?region=${region}`;
}
</script>
</x-app-layout>
