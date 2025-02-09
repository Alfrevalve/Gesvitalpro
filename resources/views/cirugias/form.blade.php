@extends('layouts.app')

@section('title', isset($cirugia) ? 'Editar Cirugía' : 'Nueva Cirugía')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-procedures me-2"></i>
            {{ isset($cirugia) ? 'Editar Cirugía' : 'Nueva Cirugía' }}
        </h1>
        <a href="{{ route('cirugias.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    <!-- Formulario -->
    <div class="card">
        <div class="card-body">
            <form action="{{ isset($cirugia) ? route('cirugias.update', $cirugia->id) : route('cirugias.store') }}" 
                  method="POST" 
                  id="cirugiaForm"
                  class="needs-validation" 
                  novalidate>
                @csrf
                @if(isset($cirugia))
                    @method('PUT')
                @endif

                <div class="row g-4">
                    <!-- Información Principal -->
                    <div class="col-md-12">
                        <h5 class="border-bottom pb-2 mb-4">
                            <i class="fas fa-info-circle me-2"></i>Información Principal
                        </h5>
                    </div>

                    <!-- Fecha y Hora -->
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="datetime-local" 
                                   class="form-control @error('fecha_cirugia') is-invalid @enderror" 
                                   id="fecha_cirugia" 
                                   name="fecha_cirugia" 
                                   value="{{ old('fecha_cirugia', isset($cirugia) ? $cirugia->fecha_cirugia : '') }}"
                                   required>
                            <label for="fecha_cirugia">Fecha y Hora de Cirugía</label>
                            @error('fecha_cirugia')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Duración Estimada -->
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="number" 
                                   class="form-control @error('duracion_estimada') is-invalid @enderror" 
                                   id="duracion_estimada" 
                                   name="duracion_estimada" 
                                   value="{{ old('duracion_estimada', isset($cirugia) ? $cirugia->duracion_estimada : '') }}"
                                   min="1"
                                   required>
                            <label for="duracion_estimada">Duración Estimada (minutos)</label>
                            @error('duracion_estimada')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Personal Médico -->
                    <div class="col-md-12">
                        <h5 class="border-bottom pb-2 mb-4">
                            <i class="fas fa-user-md me-2"></i>Personal Médico
                        </h5>
                    </div>

                    <!-- Cirujano -->
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <select class="form-select @error('cirujano') is-invalid @enderror" 
                                    id="cirujano" 
                                    name="cirujano" 
                                    required>
                                <option value="">Seleccione un cirujano</option>
                                @foreach($cirujanos as $cirujano)
                                    <option value="{{ $cirujano->id }}" 
                                            {{ old('cirujano', isset($cirugia) ? $cirugia->cirujano : '') == $cirujano->id ? 'selected' : '' }}>
                                        {{ $cirujano->nombre }} {{ $cirujano->apellido }}
                                    </option>
                                @endforeach
                            </select>
                            <label for="cirujano">Cirujano Principal</label>
                            @error('cirujano')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Instrumentista -->
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <select class="form-select @error('instrumentista') is-invalid @enderror" 
                                    id="instrumentista" 
                                    name="instrumentista" 
                                    required>
                                <option value="">Seleccione un instrumentista</option>
                                @foreach($instrumentistas as $instrumentista)
                                    <option value="{{ $instrumentista->id }}" 
                                            {{ old('instrumentista', isset($cirugia) ? $cirugia->instrumentista : '') == $instrumentista->id ? 'selected' : '' }}>
                                        {{ $instrumentista->nombre }} {{ $instrumentista->apellido }}
                                    </option>
                                @endforeach
                            </select>
                            <label for="instrumentista">Instrumentista</label>
                            @error('instrumentista')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Información del Paciente -->
                    <div class="col-md-12">
                        <h5 class="border-bottom pb-2 mb-4">
                            <i class="fas fa-user me-2"></i>Información del Paciente
                        </h5>
                    </div>

                    <!-- Paciente -->
                    <div class="col-md-12">
                        <div class="form-floating mb-3">
                            <select class="form-select @error('paciente') is-invalid @enderror" 
                                    id="paciente" 
                                    name="paciente" 
                                    required>
                                <option value="">Seleccione un paciente</option>
                                @foreach($pacientes as $paciente)
                                    <option value="{{ $paciente->id }}" 
                                            {{ old('paciente', isset($cirugia) ? $cirugia->paciente : '') == $paciente->id ? 'selected' : '' }}>
                                        {{ $paciente->name }} - {{ $paciente->id_number }}
                                    </option>
                                @endforeach
                            </select>
                            <label for="paciente">Paciente</label>
                            @error('paciente')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Detalles de la Cirugía -->
                    <div class="col-md-12">
                        <h5 class="border-bottom pb-2 mb-4">
                            <i class="fas fa-clipboard-list me-2"></i>Detalles de la Cirugía
                        </h5>
                    </div>

                    <!-- Especialidad -->
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="text" 
                                   class="form-control @error('especialidad') is-invalid @enderror" 
                                   id="especialidad" 
                                   name="especialidad" 
                                   value="{{ old('especialidad', isset($cirugia) ? $cirugia->especialidad : '') }}"
                                   required>
                            <label for="especialidad">Especialidad</label>
                            @error('especialidad')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Tipo de Anestesia -->
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <select class="form-select @error('tipo_anestesia') is-invalid @enderror" 
                                    id="tipo_anestesia" 
                                    name="tipo_anestesia" 
                                    required>
                                <option value="">Seleccione tipo de anestesia</option>
                                <option value="general" {{ old('tipo_anestesia', isset($cirugia) ? $cirugia->tipo_anestesia : '') == 'general' ? 'selected' : '' }}>General</option>
                                <option value="local" {{ old('tipo_anestesia', isset($cirugia) ? $cirugia->tipo_anestesia : '') == 'local' ? 'selected' : '' }}>Local</option>
                                <option value="regional" {{ old('tipo_anestesia', isset($cirugia) ? $cirugia->tipo_anestesia : '') == 'regional' ? 'selected' : '' }}>Regional</option>
                            </select>
                            <label for="tipo_anestesia">Tipo de Anestesia</label>
                            @error('tipo_anestesia')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Estado -->
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <select class="form-select @error('estado_cirugia') is-invalid @enderror" 
                                    id="estado_cirugia" 
                                    name="estado_cirugia" 
                                    required>
                                <option value="">Seleccione estado</option>
                                <option value="programada" {{ old('estado_cirugia', isset($cirugia) ? $cirugia->estado_cirugia : '') == 'programada' ? 'selected' : '' }}>Programada</option>
                                <option value="en_proceso" {{ old('estado_cirugia', isset($cirugia) ? $cirugia->estado_cirugia : '') == 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
                                <option value="completada" {{ old('estado_cirugia', isset($cirugia) ? $cirugia->estado_cirugia : '') == 'completada' ? 'selected' : '' }}>Completada</option>
                                <option value="cancelada" {{ old('estado_cirugia', isset($cirugia) ? $cirugia->estado_cirugia : '') == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                            </select>
                            <label for="estado_cirugia">Estado de la Cirugía</label>
                            @error('estado_cirugia')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Institución -->
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <select class="form-select @error('instituciones_hospitalarias') is-invalid @enderror" 
                                    id="instituciones_hospitalarias" 
                                    name="instituciones_hospitalarias" 
                                    required>
                                <option value="">Seleccione una institución</option>
                                @foreach($instituciones as $institucion)
                                    <option value="{{ $institucion->id }}" 
                                            {{ old('instituciones_hospitalarias', isset($cirugia) ? $cirugia->instituciones_hospitalarias : '') == $institucion->id ? 'selected' : '' }}>
                                        {{ $institucion->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <label for="instituciones_hospitalarias">Institución Hospitalaria</label>
                            @error('instituciones_hospitalarias')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Notas Adicionales -->
                    <div class="col-md-12">
                        <div class="form-floating mb-3">
                            <textarea class="form-control @error('notas_adicionales') is-invalid @enderror" 
                                      id="notas_adicionales" 
                                      name="notas_adicionales" 
                                      style="height: 100px">{{ old('notas_adicionales', isset($cirugia) ? $cirugia->notas_adicionales : '') }}</textarea>
                            <label for="notas_adicionales">Notas Adicionales</label>
                            @error('notas_adicionales')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Requisitos Especiales -->
                    <div class="col-md-12">
                        <div class="form-floating mb-3">
                            <textarea class="form-control @error('requisitos_especiales') is-invalid @enderror" 
                                      id="requisitos_especiales" 
                                      name="requisitos_especiales" 
                                      style="height: 100px">{{ old('requisitos_especiales', isset($cirugia) ? $cirugia->requisitos_especiales : '') }}</textarea>
                            <label for="requisitos_especiales">Requisitos Especiales</label>
                            @error('requisitos_especiales')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="col-12 text-end">
                        <button type="button" class="btn btn-secondary me-2" onclick="window.history.back()">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>{{ isset($cirugia) ? 'Actualizar' : 'Guardar' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .form-floating > .form-control,
    .form-floating > .form-select {
        height: calc(3.5rem + 2px);
        line-height: 1.25;
    }

    .form-floating > label {
        padding: 1rem 0.75rem;
    }

    .form-floating > .form-control:focus ~ label,
    .form-floating > .form-control:not(:placeholder-shown) ~ label,
    .form-floating > .form-select ~ label {
        transform: scale(.85) translateY(-0.5rem) translateX(0.15rem);
    }

    textarea.form-control {
        height: auto;
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

    // Inicializar Select2 para mejorar los dropdowns
    $(document).ready(function() {
        $('.form-select').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });
    });

    // Validar fecha mínima
    document.getElementById('fecha_cirugia').min = new Date().toISOString().slice(0, 16);
</script>
@endpush
