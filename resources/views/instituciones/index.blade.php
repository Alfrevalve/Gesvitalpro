<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Instituciones</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075);
        }
        .institution-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        .institution-icon.hospital {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        .institution-icon.clinica {
            background-color: #f3e5f5;
            color: #7b1fa2;
        }
        .institution-icon.otro {
            background-color: #e8f5e9;
            color: #388e3c;
        }
        .badge.tipo-hospital {
            background-color: #1976d2;
        }
        .badge.tipo-clinica {
            background-color: #7b1fa2;
        }
        .badge.tipo-otro {
            background-color: #388e3c;
        }
        .badge {
            font-weight: 500;
            padding: 0.5em 0.75em;
        }
        .table td {
            vertical-align: middle;
        }
        .btn-group-sm > .btn, .btn-sm {
            padding: 0.25rem 0.5rem;
        }
    </style>
</head>
<body>
    @include('layouts.navbar')

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">Gestión de Instituciones</h1>
            <a href="{{ route('instituciones.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Nueva Institución
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                @if($instituciones->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-building fs-1 text-muted mb-3"></i>
                        <p class="h5 text-muted">No se encontraron instituciones registradas</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Tipo</th>
                                    <th>Dirección</th>
                                    <th>Contacto</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($instituciones as $institucion)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="institution-icon me-2 {{ strtolower($institucion->tipo_establecimiento ?? 'otro') }}">
                                                    <i class="bi {{ $institucion->getTipoIconClass() }}"></i>
                                                </div>
                                                {{ $institucion->nombre }}
                                                @if($institucion->codigo_renipress)
                                                    <small class="text-muted ms-2">({{ $institucion->codigo_renipress }})</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $institucion->getTipoBackgroundClass() }}">
                                                {{ $institucion->tipo_establecimiento ?? 'No especificado' }}
                                            </span>
                                            @if($institucion->categoria)
                                                <span class="badge bg-secondary ms-1">{{ $institucion->categoria }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <i class="bi bi-geo-alt text-danger me-2"></i>
                                            {{ $institucion->direccion }}
                                            @if($institucion->ciudad)
                                                <small class="text-muted">- {{ $institucion->ciudad }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($institucion->telefono)
                                                <div><i class="bi bi-telephone text-primary me-2"></i>{{ $institucion->telefono }}</div>
                                            @endif
                                            @if($institucion->email)
                                                <div><i class="bi bi-envelope text-primary me-2"></i>{{ $institucion->email }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="{{ route('instituciones.show', $institucion) }}"
                                                   class="btn btn-sm btn-info"
                                                   title="Ver detalles">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('instituciones.edit', $institucion) }}"
                                                   class="btn btn-sm btn-warning"
                                                   title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form action="{{ route('instituciones.destroy', $institucion) }}"
                                                      method="POST"
                                                      class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="btn btn-sm btn-danger"
                                                            title="Eliminar"
                                                            onclick="return confirm('¿Está seguro de eliminar esta institución?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        {{ $instituciones->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <footer class="footer mt-5 py-3 bg-white border-top">
        <div class="container text-center">
            <span class="text-muted">© {{ date('Y') }} GesBio. Todos los derechos reservados.</span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script>
        // Activar tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Cerrar alertas automáticamente
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);
    </script>
</body>
</html>
