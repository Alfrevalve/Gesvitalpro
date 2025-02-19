<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Nueva Institución</title>
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
        .form-label {
            font-weight: 500;
        }
        .form-control:focus, .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13,110,253,.25);
        }
    </style>
</head>
<body>
    @include('layouts.navbar')

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">Nueva Institución</h1>
            <a href="{{ route('instituciones.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('instituciones.store') }}" method="POST">
                    @csrf

                    <div class="row g-4">
                        <!-- Información Básica -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre de la Institución <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nombre') is-invalid @enderror"
                                       id="nombre" name="nombre" value="{{ old('nombre') }}" required>
                                @error('nombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="codigo_renipress" class="form-label">Código RENIPRESS</label>
                                <input type="text" class="form-control @error('codigo_renipress') is-invalid @enderror"
                                       id="codigo_renipress" name="codigo_renipress" value="{{ old('codigo_renipress') }}">
                                @error('codigo_renipress')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Tipo y Categoría -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tipo_establecimiento" class="form-label">Tipo de Establecimiento <span class="text-danger">*</span></label>
                                <select class="form-select @error('tipo_establecimiento') is-invalid @enderror"
                                        id="tipo_establecimiento" name="tipo_establecimiento" required>
                                    <option value="">Seleccione un tipo</option>
                                    <option value="hospital" {{ old('tipo_establecimiento') == 'hospital' ? 'selected' : '' }}>Hospital</option>
                                    <option value="clinica" {{ old('tipo_establecimiento') == 'clinica' ? 'selected' : '' }}>Clínica</option>
                                    <option value="consultorio" {{ old('tipo_establecimiento') == 'consultorio' ? 'selected' : '' }}>Consultorio</option>
                                </select>
                                @error('tipo_establecimiento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="categoria" class="form-label">Categoría</label>
                                <input type="text" class="form-control @error('categoria') is-invalid @enderror"
                                       id="categoria" name="categoria" value="{{ old('categoria') }}">
                                @error('categoria')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Ubicación -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="direccion" class="form-label">Dirección <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('direccion') is-invalid @enderror"
                                       id="direccion" name="direccion" value="{{ old('direccion') }}" required>
                                @error('direccion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="ciudad" class="form-label">Ciudad</label>
                                <input type="text" class="form-control @error('ciudad') is-invalid @enderror"
                                       id="ciudad" name="ciudad" value="{{ old('ciudad') }}">
                                @error('ciudad')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Contacto -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control @error('telefono') is-invalid @enderror"
                                       id="telefono" name="telefono" value="{{ old('telefono') }}">
                                @error('telefono')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                       id="email" name="email" value="{{ old('email') }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Red de Salud -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="red_salud" class="form-label">Red de Salud</label>
                                <input type="text" class="form-control @error('red_salud') is-invalid @enderror"
                                       id="red_salud" name="red_salud" value="{{ old('red_salud') }}">
                                @error('red_salud')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Guardar Institución
                        </button>
                    </div>
                </form>
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
</body>
</html>
