@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary">
            <i class="bi bi-calendar-plus"></i> Registrar Nueva Visita
        </h2>
        <a href="{{ route('visitas.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    <!-- Formulario -->
    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('visitas.store') }}" method="POST" id="visitaForm">
                @csrf

                <div class="row g-4">
                    <!-- Fecha y Hora -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="fecha_hora" class="form-label">Fecha y Hora <span class="text-danger">*</span></label>
                            <input type="datetime-local"
                                   class="form-control @error('fecha_hora') is-invalid @enderror"
                                   id="fecha_hora"
                                   name="fecha_hora"
                                   value="{{ old('fecha_hora', now()->format('Y-m-d\TH:i')) }}"
                                   min="{{ now()->format('Y-m-d\TH:i') }}"
                                   required>
                            @error('fecha_hora')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> Seleccione una fecha y hora futura
                            </small>
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

                    <!-- Médico -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="medico_id" class="form-label">Médico <span class="text-danger">*</span></label>
                            <select class="form-select @error('medico_id') is-invalid @enderror"
                                    id="medico_id"
                                    name="medico_id"
                                    required>
                                <option value="">Seleccione un médico</option>
                                @foreach($medicos as $id => $nombre)
                                    <option value="{{ $id }}" {{ old('medico_id') == $id ? 'selected' : '' }}>
                                        {{ $nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('medico_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>


                    <!-- Motivo -->
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="motivo" class="form-label">Motivo de la Visita <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('motivo') is-invalid @enderror"
                                   id="motivo"
                                   name="motivo"
                                   value="{{ old('motivo') }}"
                                   required
                                   maxlength="255">
                            @error('motivo')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea class="form-control @error('observaciones') is-invalid @enderror"
                                      id="observaciones"
                                      name="observaciones"
                                      rows="3">{{ old('observaciones') }}</textarea>
                            @error('observaciones')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Registrar Visita
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
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
    textarea {
        resize: vertical;
        min-height: 100px;
    }
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('visitaForm');

    form.addEventListener('submit', function(event) {
        event.preventDefault();
        let isValid = true;
        let errors = [];

        // Remover alertas anteriores
        document.querySelectorAll('.alert').forEach(alert => alert.remove());
        document.querySelectorAll('.is-invalid').forEach(field => field.classList.remove('is-invalid'));

        // Validar fecha futura
        const fechaHora = new Date(document.getElementById('fecha_hora').value);
        const ahora = new Date();

        if (fechaHora <= ahora) {
            document.getElementById('fecha_hora').classList.add('is-invalid');
            errors.push('La fecha y hora de la visita debe ser futura');
            isValid = false;
        }

        // Validar campos requeridos
        ['institucion_id', 'medico_id', 'motivo'].forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                errors.push(`El campo ${field.previousElementSibling.textContent.replace(' *', '')} es requerido`);
                isValid = false;
            }
        });

        if (!isValid) {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger alert-dismissible fade show';
            alertDiv.innerHTML = `
                <ul class="mb-0">
                    ${errors.map(error => `<li>${error}</li>`).join('')}
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            form.insertBefore(alertDiv, form.firstChild);
            window.scrollTo(0, 0);
        } else {
            form.submit();
        }
    });
});
</script>
@endpush
