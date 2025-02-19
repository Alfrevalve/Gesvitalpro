<x-app-layout>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h2 class="mb-0">Mapa de Instituciones</h2>
                </div>

                <div class="card-body">
                    <div class="controles-mapa">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" id="buscar-ubicacion" class="form-control" 
                                           placeholder="Buscar ubicación...">
                                    <button class="btn btn-outline-secondary" type="button" 
                                            onclick="buscarUbicacion()">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select id="filtro-tipo" class="form-select" onchange="filtrarMarcadores()">
                                    <option value="">Todos los tipos</option>
                                    @foreach($instituciones->pluck('tipo_establecimiento')->unique() as $tipo)
                                        <option value="{{ $tipo }}">{{ $tipo }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select id="filtro-categoria" class="form-select" onchange="filtrarMarcadores()">
                                    <option value="">Todas las categorías</option>
                                    @foreach($instituciones->pluck('categoria')->unique() as $categoria)
                                        <option value="{{ $categoria }}">{{ $categoria }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-primary w-100" onclick="buscarCercanas()">
                                    Buscar Cercanas
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="mapa-instituciones"></div>

                    <div class="mt-3">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Estadísticas</h5>
                                        <p>Total instituciones: {{ $instituciones->count() }}</p>
                                        <p>Con ubicación: {{ $instituciones->filter->tieneUbicacion()->count() }}</p>
                                        <p>Sin ubicación: {{ $instituciones->reject->tieneUbicacion()->count() }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Leyenda</h5>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($instituciones->pluck('tipo_establecimiento')->unique() as $tipo)
                                                <span class="badge bg-primary">{{ $tipo }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para buscar instituciones cercanas -->
<div class="modal fade" id="modalCercanas" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Buscar Instituciones Cercanas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formBuscarCercanas">
                    <div class="mb-3">
                        <label class="form-label">Radio de búsqueda (km)</label>
                        <input type="number" class="form-control" id="radio" 
                               value="5" min="1" max="50">
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="usarUbicacionActual">
                        <label class="form-check-label">
                            Usar mi ubicación actual
                        </label>
                    </div>
                    <div id="coordenadas-manuales">
                        <div class="mb-3">
                            <label class="form-label">Latitud</label>
                            <input type="number" class="form-control" id="latitud" 
                                   step="any" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Longitud</label>
                            <input type="number" class="form-control" id="longitud" 
                                   step="any" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="ejecutarBusquedaCercanas()">
                    Buscar
                </button>
            </div>
        </div>
    </div>
</div>
</x-app-layout>

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    let mapa;
    let marcadores = [];
    const instituciones = @json($instituciones);

    // Inicializar mapa
    function inicializarMapa() {
        mapa = L.map('mapa-instituciones').setView([-12.0464, -77.0428], 12);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(mapa);

        // Agregar marcadores para cada institución
        instituciones.forEach(institucion => {
            if (institucion.latitud && institucion.longitud) {
                agregarMarcador(institucion);
            }
        });
    }

    // Agregar marcador al mapa
    function agregarMarcador(institucion) {
        const marcador = L.marker([institucion.latitud, institucion.longitud])
            .bindPopup(crearPopupContent(institucion));
        
        marcador.institucion = institucion;
        marcador.addTo(mapa);
        marcadores.push(marcador);
    }

    // Crear contenido del popup
    function crearPopupContent(institucion) {
        return `
            <div class="info-institucion">
                <h4>${institucion.nombre}</h4>
                <p>
                    <span class="badge bg-primary">${institucion.tipo_establecimiento}</span>
                    <span class="badge bg-secondary">${institucion.categoria}</span>
                </p>
                <p><strong>Dirección:</strong> ${institucion.direccion}</p>
                <p><strong>Teléfono:</strong> ${institucion.telefono || 'No disponible'}</p>
                <div class="mt-2">
                    <a href="${institucion.google_maps_url}" target="_blank" 
                       class="btn btn-sm btn-outline-primary">
                        Ver en Google Maps
                    </a>
                    <a href="${institucion.minsa_url}" target="_blank" 
                       class="btn btn-sm btn-outline-secondary">
                        Ver en MINSA
                    </a>
                </div>
            </div>
        `;
    }

    // Filtrar marcadores
    function filtrarMarcadores() {
        const tipo = document.getElementById('filtro-tipo').value;
        const categoria = document.getElementById('filtro-categoria').value;

        marcadores.forEach(marcador => {
            const visible = (!tipo || marcador.institucion.tipo_establecimiento === tipo) &&
                          (!categoria || marcador.institucion.categoria === categoria);
            
            if (visible) {
                mapa.addLayer(marcador);
            } else {
                mapa.removeLayer(marcador);
            }
        });
    }

    // Buscar ubicación
    function buscarUbicacion() {
        const query = document.getElementById('buscar-ubicacion').value;
        
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    mapa.setView([data[0].lat, data[0].lon], 15);
                } else {
                    alert('Ubicación no encontrada');
                }
            })
            .catch(error => {
                console.error('Error al buscar ubicación:', error);
                alert('Error al buscar ubicación');
            });
    }

    // Mostrar modal de búsqueda cercana
    function buscarCercanas() {
        const modal = new bootstrap.Modal(document.getElementById('modalCercanas'));
        modal.show();
    }

    // Ejecutar búsqueda de instituciones cercanas
    function ejecutarBusquedaCercanas() {
        const radio = document.getElementById('radio').value;
        let latitud, longitud;

        if (document.getElementById('usarUbicacionActual').checked) {
            navigator.geolocation.getCurrentPosition(
                position => {
                    realizarBusquedaCercanas(
                        position.coords.latitude,
                        position.coords.longitude,
                        radio
                    );
                },
                error => {
                    console.error('Error al obtener ubicación:', error);
                    alert('No se pudo obtener la ubicación actual');
                }
            );
        } else {
            latitud = document.getElementById('latitud').value;
            longitud = document.getElementById('longitud').value;
            realizarBusquedaCercanas(latitud, longitud, radio);
        }
    }

    // Realizar la búsqueda de cercanas
    function realizarBusquedaCercanas(latitud, longitud, radio) {
        fetch(`/geolocalizacion/cercanas?latitud=${latitud}&longitud=${longitud}&radio=${radio}`)
            .then(response => response.json())
            .then(data => {
                bootstrap.Modal.getInstance(document.getElementById('modalCercanas')).hide();
                
                // Limpiar marcadores anteriores
                marcadores.forEach(marcador => mapa.removeLayer(marcador));
                marcadores = [];

                // Agregar nuevos marcadores
                data.instituciones.forEach(institucion => {
                    agregarMarcador(institucion);
                });

                // Centrar mapa en la ubicación de búsqueda
                mapa.setView([latitud, longitud], 13);
            })
            .catch(error => {
                console.error('Error al buscar instituciones cercanas:', error);
                alert('Error al buscar instituciones cercanas');
            });
    }

    // Inicializar mapa al cargar la página
    document.addEventListener('DOMContentLoaded', inicializarMapa);

    // Manejar checkbox de ubicación actual
    document.getElementById('usarUbicacionActual').addEventListener('change', function() {
        document.getElementById('coordenadas-manuales').style.display = 
            this.checked ? 'none' : 'block';
    });
</script>
</x-app-layout>
