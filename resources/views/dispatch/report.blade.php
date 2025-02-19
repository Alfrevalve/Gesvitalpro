@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3">Despacho - Reportes</h1>
        </div>
        <div class="col text-end">
            <a href="{{ route('dispatch.index') }}" class="btn btn-outline-primary me-2">
                <i class="bi bi-list"></i> Ver Lista
            </a>
            <a href="{{ route('dispatch.kanban') }}" class="btn btn-outline-secondary">
                <i class="bi bi-kanban"></i> Ver Kanban
            </a>
        </div>
    </div>

    <!-- Resumen General -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Total Entregas</h6>
                            <h2 class="mt-2 mb-0">{{ $stats['total_deliveries'] }}</h2>
                        </div>
                        <i class="bi bi-truck h1 mb-0"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Pendientes</h6>
                            <h2 class="mt-2 mb-0">{{ $stats['pending_deliveries'] }}</h2>
                        </div>
                        <i class="bi bi-clock h1 mb-0"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">En Tránsito</h6>
                            <h2 class="mt-2 mb-0">{{ $stats['in_transit_deliveries'] }}</h2>
                        </div>
                        <i class="bi bi-arrow-right-circle h1 mb-0"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Entregadas</h6>
                            <h2 class="mt-2 mb-0">{{ $stats['delivered_requests'] }}</h2>
                        </div>
                        <i class="bi bi-check-circle h1 mb-0"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Estado de Entregas</h5>
                </div>
                <div class="card-body">
                    <canvas id="deliveryStatusChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Tiempo Promedio de Entrega</h5>
                </div>
                <div class="card-body">
                    <canvas id="deliveryTimeChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Mapa de Entregas -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Mapa de Entregas del Día</h5>
                </div>
                <div class="card-body">
                    <div id="deliveryMap" style="height: 400px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // Gráfico de Estado de Entregas
    new Chart(document.getElementById('deliveryStatusChart'), {
        type: 'pie',
        data: {
            labels: ['Pendientes', 'En Tránsito', 'Entregadas'],
            datasets: [{
                data: [
                    {{ $stats['pending_deliveries'] }},
                    {{ $stats['in_transit_deliveries'] }},
                    {{ $stats['delivered_requests'] }}
                ],
                backgroundColor: [
                    '#ffc107',
                    '#17a2b8',
                    '#28a745'
                ]
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

    // Datos de ejemplo para el gráfico de tiempo promedio
    const deliveryTimeData = {
        labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie'],
        datasets: [{
            label: 'Tiempo Promedio (horas)',
            data: [1.5, 2.1, 1.8, 2.4, 1.9],
            borderColor: '#007bff',
            tension: 0.1
        }]
    };

    new Chart(document.getElementById('deliveryTimeChart'), {
        type: 'line',
        data: deliveryTimeData,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Inicializar mapa
    const map = L.map('deliveryMap').setView([-34.6037, -58.3816], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Ejemplo de marcadores de entrega
    const deliveries = [
        {lat: -34.6037, lng: -58.3816, status: 'delivered', time: '10:30'},
        {lat: -34.6137, lng: -58.3916, status: 'in_transit', time: '11:15'},
        {lat: -34.5937, lng: -58.3716, status: 'pending', time: '12:00'}
    ];

    deliveries.forEach(delivery => {
        const icon = L.divIcon({
            className: 'delivery-marker',
            html: `<i class="bi bi-${delivery.status === 'delivered' ? 'check-circle-fill text-success' :
                (delivery.status === 'in_transit' ? 'truck text-info' : 'clock text-warning')}"></i>`,
            iconSize: [25, 25]
        });

        L.marker([delivery.lat, delivery.lng], {icon})
            .bindPopup(`Entrega ${delivery.status}<br>Hora: ${delivery.time}`)
            .addTo(map);
    });
</script>
@endpush

@push('styles')
<style>
    .delivery-marker {
        font-size: 1.5rem;
        text-align: center;
        line-height: 25px;
    }
</style>
@endpush
@endsection
