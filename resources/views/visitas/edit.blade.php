<x-app-layout>
<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary">
            <i class="fas fa-calendar-alt"></i> Editar Visita
        </h2>
        <a href="{{ route('visitas.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <!-- Formulario -->
    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('visitas.update', $visita) }}" method="POST" id="visitaForm">
                @csrf
                @method('PUT')

                <div class="row g-4">
                    <!-- Fecha y Hora -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="fecha_hora" class="form-label">Fecha y Hora <span class="text-danger">*</span></label>
                            <input type="datetime-local" 
                                   class="form-control @error('fecha_hora') is-invalid @enderror" 
                                   id="fecha_hora" 
                                   name="fecha_hora" 
                                   value="{{ old('fecha_hora', $visita->fecha_hora->format('Y-m-d\TH:i')) }}" 
                                   {{ $visita->estado !== 'programada' ? 'readonly' : '' }}
                                   required>
                            @error('fecha_hora')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                            @if($visita->estado === 'programada')
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i> Puede modificar la fecha y hora si la visita está programada
                                </small>
                            @endif
                        </div>
                    </div>

                    <!-- Institución -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="institucion_id" class="form-label">Institución <span class="text-danger">*</span></label>
                            <select class="form-select @error('institucion_id') is-invalid @enderror" 
                                    id="institucion_id" 
                                    name="institucion_id" 
                                    {{ $visita->estado !== 'programada' ? 'disabled' : '' }}
                                    required>
                                <option value="">Seleccione una institución</option>
                                @foreach($instituciones as $id => $nombre)
                                    <option value="{{ $id }}" {{ old('institucion_id', $visita->institucion_id) == $id ? 'selected' : '' }}>
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
                                    {{ $visita->estado !== 'programada' ? 'disabled' : '' }}
                                    required>
                                <option value="">Seleccione un médico</option>
                                @foreach($medicos as $id => $nombre)
                                    <option value="{{ $id }}" {{ old('medico_id', $visita->medico_id) == $id ? 'selected' : '' }}>
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

                    <!-- Estado -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="estado" class="form-label">Estado <span class="text-danger">*</span></label>
                            <select class="form-select @error('estado') is-invalid @enderror" 
                                    id="estado" 
                                    name="estado" 
                                    required>
                                <option value="programada" {{ old('estado', $visita->estado) == 'programada' ? 'selected' : '' }}>Programada</option>
                                <option value="realizada" {{ old('estado', $visita->estado) == 'realizada' ? 'selected' : '' }}>Realizada</option>
                                <option value="cancelada" {{ old('estado', $visita->estado) == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                            </select>
                            @error('estado')
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
                                   value="{{ old('motivo', $visita->motivo) }}" 
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
                                      rows="3">{{ old('observaciones', $visita->observaciones) }}</textarea>
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
                        <i class="fas fa-save me-2"></i>Actualizar Visita
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
    textarea {
        resize: vertical;
        min-height: 100px;
    }
    .form-control:disabled, 
    .form-control[readonly],
    .form-select:disabled {
        background-color: #f8f9fa;
        opacity: 1;
    }
</style>
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('visitaForm');
    const estadoSelect = document.getElementById('estado');
    const fechaHoraInput = document.getElementById('fecha_hora');
    const institucionSelect = document.getElementById('institucion_id');
    const medicoSelect = document.getElementById('medico_id');
    
    // Guardar estado inicial
    const estadoInicial = '{{ $visita->estado }}';
    
    // Manejar cambios en el estado
    estadoSelect.addEventListener('change', function() {
        const nuevoEstado = this.value;
        
        if (estadoInicial === 'realizada' || estadoInicial === 'cancelada') {
            if (nuevoEstado !== estadoInicial) {
                alert('No se puede cambiar el estado de una visita realizada o cancelada');
                this.value = estadoInicial;
            }
        }
    });
    
    // Validación del formulario
    form.addEventListener('submit', function(event) {
        let isValid = true;
        
        if (estadoInicial === 'programada') {
            // Validar fecha futura solo si la visita está programada
            const fechaHora = new Date(fechaHoraInput.value);
            const ahora = new Date();
            
            if (fechaHora <= ahora) {
                alert('La fecha y hora de la visita debe ser futura');
                isValid = false;
            }
            
            // Validar selección de institución y médico
            if (!institucionSelect.value) {
                alert('Debe seleccionar una institución');
                isValid = false;
            }
            
            if (!medicoSelect.value) {
                alert('Debe seleccionar un médico');
                isValid = false;
            }
        }
        
        if (!isValid) {
            event.preventDefault();
        }
    });
});
</script>
@endpush
