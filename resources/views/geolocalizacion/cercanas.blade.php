<x-app-layout>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2 class="mb-0">Instituciones Cercanas</h2>
                        <a href="{{ url('/geolocalizacion/mapa') }}" class="btn btn-outline-primary">
                            <i class="fas fa-map-marker-alt"></i> Volver al Mapa
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if($instituciones->isEmpty())
                        <div class="alert alert-info">
                            No se encontraron instituciones en el radio especificado.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Tipo</th>
                                        <th>Categoría</th>
                                        <th>Distancia</th>
                                        <th>Dirección</th>
                                        <th>Teléfono</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($instituciones as $institucion)
                                        <tr>
                                            <td>{{ $institucion['nombre'] }}</td>
                                            <td>
                                                <span class="badge bg-primary">
                                                    {{ $institucion['tipo'] }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    {{ $institucion['categoria'] }}
                                                </span>
                                            </td>
                                            <td>
                                                {{ number_format($institucion['distancia'], 2) }} km
                                            </td>
                                            <td>{{ $institucion['direccion'] }}</td>
                                            <td>{{ $institucion['telefono'] ?? 'No disponible' }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="https://www.google.com/maps?q={{ $institucion['latitud'] }},{{ $institucion['longitud'] }}"
                                                       target="_blank"
                                                       class="btn btn-sm btn-outline-primary"
                                                       title="Ver en Google Maps">
                                                        <i class="fas fa-map-marked-alt"></i>
                                                    </a>
                                                    
                                                    <a href="https://geominsa.minsa.gob.pe/geominsaportal/apps/webappviewer/index.html?id=7358ce1c142846e2bc5df45964303bcd&find={{ $institucion['codigo_renipress'] }}"
                                                       target="_blank"
                                                       class="btn btn-sm btn-outline-secondary"
                                                       title="Ver en MINSA">
                                                        <i class="fas fa-hospital"></i>
                                                    </a>

                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-info"
                                                            onclick="mostrarDetalles({{ json_encode($institucion) }})"
                                                            title="Ver detalles">
                                                        <i class="fas fa-info-circle"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Resumen</h5>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <p><strong>Total encontradas:</strong> {{ count($instituciones) }}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <p><strong>Distancia máxima:</strong> 
                                                {{ number_format($instituciones->max('distancia'), 2) }} km
                                            </p>
                                        </div>
                                        <div class="col-md-4">
                                            <p><strong>Distancia promedio:</strong> 
                                                {{ number_format($instituciones->avg('distancia'), 2) }} km
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Detalles -->
<div class="modal fade" id="modalDetalles" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles de la Institución</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <h4 id="nombre-institucion"></h4>
                    <div id="badges-container" class="mb-2"></div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Código RENIPRESS:</strong></p>
                        <p id="codigo-renipress"></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Red de Salud:</strong></p>
                        <p id="red-salud"></p>
                    </div>
                </div>
                <div class="mb-3">
                    <p><strong>Dirección:</strong></p>
                    <p id="direccion-completa"></p>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Teléfono:</strong></p>
                        <p id="telefono-contacto"></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Distancia:</strong></p>
                        <p id="distancia-institucion"></p>
                    </div>
                </div>
                <div class="mb-3">
                    <p><strong>Coordenadas:</strong></p>
                    <p id="coordenadas"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <a id="btn-google-maps" href="#" target="_blank" class="btn btn-primary">
                    Ver en Google Maps
                </a>
                <a id="btn-minsa" href="#" target="_blank" class="btn btn-info">
                    Ver en MINSA
                </a>
            </div>
        </div>
    </div>
</div>
</x-app-layout>

@section('scripts')
<script>
function mostrarDetalles(institucion) {
    // Actualizar contenido del modal
    document.getElementById('nombre-institucion').textContent = institucion.nombre;
    
    // Actualizar badges
    const badgesContainer = document.getElementById('badges-container');
    badgesContainer.innerHTML = `
        <span class="badge bg-primary">${institucion.tipo}</span>
        <span class="badge bg-secondary">${institucion.categoria}</span>
    `;
    
    // Actualizar resto de información
    document.getElementById('codigo-renipress').textContent = institucion.codigo_renipress;
    document.getElementById('red-salud').textContent = institucion.red_salud;
    document.getElementById('direccion-completa').textContent = institucion.direccion;
    document.getElementById('telefono-contacto').textContent = institucion.telefono || 'No disponible';
    document.getElementById('distancia-institucion').textContent = 
        `${Number(institucion.distancia).toFixed(2)} km`;
    document.getElementById('coordenadas').textContent = 
        `${institucion.latitud}, ${institucion.longitud}`;

    // Actualizar enlaces
    document.getElementById('btn-google-maps').href = 
        `https://www.google.com/maps?q=${institucion.latitud},${institucion.longitud}`;
    document.getElementById('btn-minsa').href = 
        `https://geominsa.minsa.gob.pe/geominsaportal/apps/webappviewer/index.html?id=7358ce1c142846e2bc5df45964303bcd&find=${institucion.codigo_renipress}`;

    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('modalDetalles'));
    modal.show();
}
</script>
</x-app-layout>
