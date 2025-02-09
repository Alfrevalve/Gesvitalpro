@extends('layouts.app')

@section('title', 'Importar/Exportar Inventario')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-file-excel me-2"></i>Importar/Exportar Inventario
        </h1>
        <a href="{{ route('inventarios.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    <div class="row">
        <!-- Importar -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-import me-2"></i>Importar Datos
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('inventarios.import') }}" 
                          method="POST" 
                          enctype="multipart/form-data"
                          class="needs-validation" 
                          novalidate>
                        @csrf
                        
                        <!-- Archivo -->
                        <div class="mb-4">
                            <label class="form-label">Archivo Excel</label>
                            <div class="input-group">
                                <input type="file" 
                                       class="form-control @error('file') is-invalid @enderror" 
                                       name="file" 
                                       accept=".xlsx,.xls,.csv"
                                       required>
                                <button class="btn btn-outline-secondary" 
                                        type="button"
                                        data-bs-toggle="modal"
                                        data-bs-target="#templateModal">
                                    <i class="fas fa-question-circle"></i>
                                </button>
                                @error('file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text">
                                Formatos soportados: .xlsx, .xls, .csv
                            </div>
                        </div>

                        <!-- Opciones -->
                        <div class="mb-4">
                            <label class="form-label">Opciones de Importación</label>
                            <div class="form-check mb-2">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       name="actualizar_existentes" 
                                       id="actualizarExistentes" 
                                       value="1">
                                <label class="form-check-label" for="actualizarExistentes">
                                    Actualizar items existentes
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       name="ignorar_errores" 
                                       id="ignorarErrores" 
                                       value="1">
                                <label class="form-check-label" for="ignorarErrores">
                                    Continuar importación si hay errores
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload me-2"></i>Importar Datos
                        </button>
                    </form>

                    @if(session('import_errors'))
                        <div class="alert alert-danger mt-4">
                            <h6 class="alert-heading">
                                <i class="fas fa-exclamation-triangle me-2"></i>Errores en la importación
                            </h6>
                            <ul class="mb-0">
                                @foreach(session('import_errors') as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Historial de Importaciones -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>Historial de Importaciones
                    </h5>
                </div>
                <div class="card-body">
                    @if($importaciones && $importaciones->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Usuario</th>
                                        <th>Registros</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($importaciones as $importacion)
                                        <tr>
                                            <td>{{ $importacion->created_at->format('d/m/Y H:i') }}</td>
                                            <td>{{ $importacion->usuario->name }}</td>
                                            <td>{{ $importacion->registros_procesados }}</td>
                                            <td>
                                                @if($importacion->estado == 'completado')
                                                    <span class="badge bg-success">Completado</span>
                                                @elseif($importacion->estado == 'error')
                                                    <span class="badge bg-danger">Error</span>
                                                @else
                                                    <span class="badge bg-warning">Parcial</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No hay historial de importaciones</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Exportar -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-export me-2"></i>Exportar Datos
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('inventarios.export') }}" method="GET">
                        <!-- Formato -->
                        <div class="mb-4">
                            <label class="form-label">Formato de Exportación</label>
                            <select class="form-select" name="formato" required>
                                <option value="xlsx">Excel (XLSX)</option>
                                <option value="csv">CSV</option>
                                <option value="pdf">PDF</option>
                            </select>
                        </div>

                        <!-- Filtros -->
                        <div class="mb-4">
                            <label class="form-label">Filtros</label>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <select class="form-select" name="categoria">
                                        <option value="">Todas las categorías</option>
                                        @foreach($categorias as $categoria)
                                            <option value="{{ $categoria }}">{{ ucfirst($categoria) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <select class="form-select" name="estado">
                                        <option value="">Todos los estados</option>
                                        <option value="disponible">Disponible</option>
                                        <option value="agotado">Agotado</option>
                                        <option value="bajo_stock">Bajo Stock</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Campos -->
                        <div class="mb-4">
                            <label class="form-label">Campos a Exportar</label>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               name="campos[]" 
                                               value="codigo" 
                                               checked>
                                        <label class="form-check-label">Código</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               name="campos[]" 
                                               value="nombre" 
                                               checked>
                                        <label class="form-check-label">Nombre</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               name="campos[]" 
                                               value="categoria" 
                                               checked>
                                        <label class="form-check-label">Categoría</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               name="campos[]" 
                                               value="stock" 
                                               checked>
                                        <label class="form-check-label">Stock</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               name="campos[]" 
                                               value="precio" 
                                               checked>
                                        <label class="form-check-label">Precio</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               name="campos[]" 
                                               value="ubicacion">
                                        <label class="form-check-label">Ubicación</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-download me-2"></i>Exportar Datos
                        </button>
                    </form>
                </div>
            </div>

            <!-- Historial de Exportaciones -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>Historial de Exportaciones
                    </h5>
                </div>
                <div class="card-body">
                    @if($exportaciones && $exportaciones->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Usuario</th>
                                        <th>Formato</th>
                                        <th>Registros</th>
                                        <th>Archivo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($exportaciones as $exportacion)
                                        <tr>
                                            <td>{{ $exportacion->created_at->format('d/m/Y H:i') }}</td>
                                            <td>{{ $exportacion->usuario->name }}</td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    {{ strtoupper($exportacion->formato) }}
                                                </span>
                                            </td>
                                            <td>{{ $exportacion->registros_exportados }}</td>
                                            <td>
                                                @if($exportacion->archivo)
                                                    <a href="{{ asset('storage/' . $exportacion->archivo) }}" 
                                                       class="btn btn-sm btn-outline-primary"
                                                       download>
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                @else
                                                    <span class="text-muted">No disponible</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No hay historial de exportaciones</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Plantilla -->
<div class="modal fade" id="templateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-table me-2"></i>Plantilla de Importación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    La plantilla debe contener las siguientes columnas con el formato especificado:
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Columna</th>
                                <th>Tipo</th>
                                <th>Requerido</th>
                                <th>Descripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>codigo</td>
                                <td>Texto</td>
                                <td>Sí</td>
                                <td>Código único del item</td>
                            </tr>
                            <tr>
                                <td>nombre</td>
                                <td>Texto</td>
                                <td>Sí</td>
                                <td>Nombre del item</td>
                            </tr>
                            <tr>
                                <td>categoria</td>
                                <td>Texto</td>
                                <td>Sí</td>
                                <td>Categoría del item</td>
                            </tr>
                            <tr>
                                <td>stock</td>
                                <td>Número</td>
                                <td>Sí</td>
                                <td>Cantidad disponible</td>
                            </tr>
                            <tr>
                                <td>stock_minimo</td>
                                <td>Número</td>
                                <td>Sí</td>
                                <td>Cantidad mínima permitida</td>
                            </tr>
                            <tr>
                                <td>precio</td>
                                <td>Decimal</td>
                                <td>Sí</td>
                                <td>Precio unitario</td>
                            </tr>
                            <tr>
                                <td>ubicacion</td>
                                <td>Texto</td>
                                <td>No</td>
                                <td>Ubicación en almacén</td>
                            </tr>
                            <tr>
                                <td>descripcion</td>
                                <td>Texto</td>
                                <td>No</td>
                                <td>Descripción del item</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <a href="{{ route('inventarios.template') }}" class="btn btn-primary">
                        <i class="fas fa-download me-2"></i>Descargar Plantilla
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .form-check-input:checked {
        background-color: #1cc88a;
        border-color: #1cc88a;
    }
    .table td {
        vertical-align: middle;
    }
    .badge {
        font-size: 0.85em;
        padding: 0.5em 1em;
    }
</style>
@endpush

@push('scripts')
<script>
    // Validación del formulario
    (function () {
        'use strict'
        var forms = document.querySelectorAll('.needs-validation')
        Array.prototype.slice.call(forms)
            .forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
    })()

    // Validación de campos de exportación
    document.querySelector('form[action="{{ route('inventarios.export') }}"]')
        .addEventListener('submit', function(e) {
            var campos = document.querySelectorAll('input[name="campos[]"]:checked');
            if (campos.length === 0) {
                e.preventDefault();
                alert('Debe seleccionar al menos un campo para exportar');
            }
        });
</script>
@endpush
