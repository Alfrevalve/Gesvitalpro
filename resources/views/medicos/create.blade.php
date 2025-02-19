<x-app-layout>
<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary">
            <i class="fas fa-user-md"></i> Nuevo Médico
        </h2>
        <a href="{{ route('medicos.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <!-- Formulario -->
    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('medicos.store') }}" method="POST">
                @csrf

                <div class="row g-4">
                    <!-- Nombre -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nombre" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('nombre') is-invalid @enderror" 
                                   id="nombre" 
                                   name="nombre" 
                                   value="{{ old('nombre') }}" 
                                   required>
                            @error('nombre')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <!-- Especialidad -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="especialidad" class="form-label">Especialidad <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('especialidad') is-invalid @enderror" 
                                   id="especialidad" 
                                   name="especialidad" 
                                   value="{{ old('especialidad') }}" 
                                   required>
                            @error('especialidad')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <!-- Institución -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="institucion_id" class="form-label">Institución <span class="text-danger">*</span></label>
                            <select class="form-select @error('institucion_id') is-invalid @enderror" 
                                    id="institucion_id" 
                                    name="institucion_id" 
                                    required>
                                <option value="">Seleccione una institución</option>
                                @foreach($instituciones as $id => $nombre)
                                    <option value="{{ $id }}" {{ old('institucion_id') == $id ? 'selected' : '' }}>
                                        {{ $nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('institucion_id')
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
                                   value="{{ old('telefono') }}" 
                                   required>
                            @error('telefono')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email" class="form-label">Correo Electrónico</label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}">
                            @error('email')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Guardar Médico
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
</style>
</style>
