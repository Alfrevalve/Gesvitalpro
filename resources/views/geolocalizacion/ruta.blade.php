<x-app-layout>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2 class="mb-0">Ruta Optimizada</h2>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-primary" onclick="exportarRuta()">
                                <i class="fas fa-download"></i> Exportar Ruta
                            </button>
                            <a href="{{ route('geolocalizacion.mapa') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-map-marker-alt"></i> Volver al Mapa
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <!-- Información de la Ruta -->
                            <div class="info-ruta">
                                <div class="row">
                                    <div class="col-md-4">
                                        <h5>Distancia Total</h5>
                                        <p class="mb-0">{{ number_format($ruta['distancia_total'], 2) }} km</p>
                                    </div>
                                    <div class="col-md-4">
                                        <h5>Tiempo Estimado</h5>
                                        <p class="mb-0 tiempo-estimado">
                                            @if($ruta['tiempo_estimado']['horas_estimadas'] > 0)
                                                {{ $ruta['tiempo_estimado']['horas_estimadas'] }} horas
                                            @endif
                                            {{ $ruta['tiempo_estimado']['minutos_totales'] % 60 }} minutos
                                        </p>
                                    </div>
                                    <div class="col-md-4">
                                        <h5>Total Paradas</h5>
                                        <p class="mb-0">{{ count($ruta['ruta']) }} instituciones</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Mapa -->
                            <div id="mapa-ruta"></div>
                        </div>

                        <div class="col-md-4">
                            <!-- Lista de Paradas -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Secuencia de Visitas</h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="lista-paradas">
                                        @foreach($ruta['ruta'] as $index => $parada)
                                            <div class="parada-item" 
                                                 data-lat="{{ $parada['latitud'] }}"
                                                 data-lng="{{ $parada['longitud'] }}"
                                                 onclick="centrarEnParada({{ $parada['latitud'] }}, {{ $parada['longitud'] }})">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <span class="badge bg-primary">{{ $index + 1 }}</span>
                                                        <strong class="ms-2">{{ $parada['nombre'] }}</strong>
                                                    </div>
                                                    @if($index < count($ruta['ruta']) - 1)
                                                        <small class="text-muted">
                                                            {{ number_format($parada['distancia_siguiente'] ?? 0, 2) }} km
                                                        </small>
                                                    @endif
                                                </div>
                                                <div class="mt-2 text-muted">
                                                    <small>
                                                        <i class="fas fa-map-marker-alt"></i>
                                                        {{ $parada['direccion'] }}
                                                    </small>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Opciones de Ruta -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h5 class="mb-0">Opciones de Ruta</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Modo de Transporte</label>
                                        <select class="form-select" onchange="cambiarModoTransporte(this.value)">
                                            <option value="car">Automóvil</option>
                                            <option value="bicycle">Bicicleta</option>
                                            <option value="foot">A pie</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Optimizar por</label>
                                        <select class="form-select" onchange="cambiarOptimizacion(this.value)">
                                            <option value="distance">Distancia</option>
                                            <option value="time">Tiempo</option>
                                        </select>
                                    </div>
                                    <button class="btn btn-primary w-100" onclick="recalcularRuta()">
                                        Recalcular Ruta
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</x-app-layout>

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>
<script>
let mapa;
let rutaControl;
const paradas = @json($ruta['ruta']);

function inicializarMapa() {
    // Crear mapa
    mapa = L.map('mapa-ruta');
    
    // Agregar capa base
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(mapa);

    // Calcular centro y zoom óptimo
    const bounds = L.latLngBounds(paradas.map(p => [p.latitud, p.longitud]));
    mapa.fitBounds(bounds.pad(0.1));

    // Agregar marcadores
    paradas.forEach((parada, index) => {
        const marker = L.marker([parada.latitud, parada.longitud])
            .bindPopup(crearPopupContent(parada, index + 1))
            .addTo(mapa);
    });

    // Dibujar ruta
    dibujarRuta();
}

function crearPopupContent(parada, numero) {
    return `
        <div class="info-parada">
            <h5>${numero}. ${parada.nombre}</h5>
            <p class="mb-1">
                <span class="badge bg-primary">${parada.tipo}</span>
                <span class="badge bg-secondary">${parada.categoria}</span>
            </p>
            <p class="mb-1"><strong>Dirección:</strong> ${parada.direccion}</p>
            <p class="mb-0"><strong>Teléfono:</strong> ${parada.telefono || 'No disponible'}</p>
        </div>
    `;
}

function dibujarRuta() {
    if (rutaControl) {
        mapa.removeControl(rutaControl);
    }

    const waypoints = paradas.map(p => L.latLng(p.latitud, p.longitud));
    
    rutaControl = L.Routing.control({
        waypoints: waypoints,
        routeWhileDragging: false,
        showAlternatives: false,
        fitSelectedRoutes: false,
        lineOptions: {
            styles: [{color: '#0d6efd', weight: 4}]
        }
    }).addTo(mapa);
}

function centrarEnParada(lat, lng) {
    mapa.setView([lat, lng], 15);
    
    // Resaltar item en la lista
    document.querySelectorAll('.parada-item').forEach(item => {
        item.classList.remove('activa');
        if (item.dataset.lat == lat && item.dataset.lng == lng) {
            item.classList.add('activa');
        }
    });
}

function cambiarModoTransporte(modo) {
    // Implementar cambio de modo de transporte
    recalcularRuta();
}

function cambiarOptimizacion(tipo) {
    // Implementar cambio de tipo de optimización
    recalcularRuta();
}

function recalcularRuta() {
    const modo = document.querySelector('select[onchange*="cambiarModoTransporte"]').value;
    const optimizacion = document.querySelector('select[onchange*="cambiarOptimizacion"]').value;

    // Realizar petición para recalcular ruta
    fetch('/geolocalizacion/ruta/recalcular', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            instituciones: paradas.map(p => p.id),
            modo: modo,
            optimizacion: optimizacion
        })
    })
    .then(response => response.json())
    .then(data => {
        // Actualizar mapa y datos
        location.reload();
    })
    .catch(error => {
        console.error('Error al recalcular ruta:', error);
        alert('Error al recalcular la ruta');
    });
}

function exportarRuta() {
    window.location.href = '/geolocalizacion/ruta/exportar?' + new URLSearchParams({
        instituciones: paradas.map(p => p.id).join(',')
    });
}

// Inicializar mapa al cargar la página
document.addEventListener('DOMContentLoaded', inicializarMapa);
</script>
</x-app-layout>
