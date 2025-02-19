<x-app-layout>
<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary">
            <i class="fas fa-hospital"></i> Editar Institución
        </h2>
        <a href="{{ route('instituciones.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <!-- Formulario -->
    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('instituciones.update', $institucion) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-4">
                    <!-- Nombre -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nombre" class="form-label">Nombre de la Institución <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('nombre') is-invalid @enderror" 
                                   id="nombre" 
                                   name="nombre" 
                                   value="{{ old('nombre', $institucion->nombre) }}" 
                                   required>
                            @error('nombre')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <!-- Tipo -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tipo" class="form-label">Tipo de Institución <span class="text-danger">*</span></label>
                            <select class="form-select @error('tipo') is-invalid @enderror" 
                                    id="tipo" 
                                    name="tipo" 
                                    required>
                                <option value="">Seleccione un tipo</option>
                                <option value="hospital" {{ old('tipo', $institucion->tipo) == 'hospital' ? 'selected' : '' }}>Hospital</option>
                                <option value="clinica" {{ old('tipo', $institucion->tipo) == 'clinica' ? 'selected' : '' }}>Clínica</option>
                                <option value="consultorio" {{ old('tipo', $institucion->tipo) == 'consultorio' ? 'selected' : '' }}>Consultorio</option>
                            </select>
                            @error('tipo')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <!-- Dirección -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="direccion" class="form-label">Dirección <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('direccion') is-invalid @enderror" 
                                   id="direccion" 
                                   name="direccion" 
                                   value="{{ old('direccion', $institucion->direccion) }}" 
                                   required>
                            @error('direccion')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <!-- Teléfono -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="telefono" class="form-label">Teléfono <span class="text-danger">*</span></label>
                            <input type="tel" 
                                   class="form-control @error('telefono') is-invalid @enderror" 
                                   id="telefono" 
                                   name="telefono" 
                                   value="{{ old('telefono', $institucion->telefono) }}" 
                                   required>
                            @error('telefono')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Actualizar Institución
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</x-app-layout>

<style>
<style>
    .form-label {
        font-weight: 500;
    }
    .card {
        border: none;
        border-radius: 0.5rem;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(15, 48, 97, 0.25);
    }
    .invalid-feedback {
        font-size: 0.875rem;
    }
</style>
</style>
