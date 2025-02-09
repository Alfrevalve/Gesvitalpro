@extends('layouts.app')

@section('title', isset($paciente) ? 'Editar Paciente' : 'Nuevo Paciente')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user me-2"></i>
            {{ isset($paciente) ? 'Editar Paciente' : 'Nuevo Paciente' }}
        </h1>
        <a href="{{ route('pacientes.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    <!-- Formulario -->
    <div class="card">
        <div class="card-body">
            <form action="{{ isset($paciente) ? route('pacientes.update', $paciente->id) : route('pacientes.store') }}" 
                  method="POST" 
                  enctype="multipart/form-data"
                  id="pacienteForm"
                  class="needs-validation" 
                  novalidate>
                @csrf
                @if(isset($paciente))
                    @method('PUT')
                @endif

                <div class="row g-4">
                    <!-- Información Personal -->
                    <div class="col-md-12">
                        <h5 class="border-bottom pb-2 mb-4">
                            <i class="fas fa-info-circle me-2"></i>Información Personal
                        </h5>
                    </div>

                    <!-- Foto -->
                    <div class="col-md-12 mb-4">
                        <div class="text-center">
                            <div class="avatar-xl mx-auto mb-3">
                                @if(isset($paciente) && $paciente->foto)
                                    <img src="{{ asset('storage/' . $paciente->foto) }}" 
                                         class="rounded-circle" 
                                         alt="Foto actual">
                                @else
                                    <i class="fas fa-user-circle fa-5x text-secondary"></i>
                                @endif
                            </div>
                            <div class="mt-2">
                                <label class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-upload me-2"></i>Subir Foto
                                    <input type="file" 
                                           name="foto" 
                                           class="d-none" 
                                           accept="image/*"
                                           onchange="previewImage(this)">
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Nombre -->
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="text" 
                                   class="form-control @error('nombre') is-invalid @enderror" 
                                   id="nombre" 
                                   name="nombre" 
                                   value="{{ old('nombre', isset($paciente) ? $paciente->nombre : '') }}"
                                   required>
                            <label for="nombre">Nombre Completo</label>
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Identificación -->
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="text" 
                                   class="form-control @error('id_number') is-invalid @enderror" 
                                   id="id_number" 
                                   name="id_number" 
                                   value="{{ old('id_number', isset($paciente) ? $paciente->id_number : '') }}"
                                   required>
                            <label for="id_number">Número de Identificación</label>
                            @error('id_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email', isset($paciente) ? $paciente->email : '') }}"
                                   required>
                            <label for="email">Correo Electrónico</label>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Teléfono -->
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="tel" 
                                   class="form-control @error('telefono') is-invalid @enderror" 
                                   id="telefono" 
                                   name="telefono" 
                                   value="{{ old('telefono', isset($paciente) ? $paciente->telefono : '') }}"
                                   required>
                            <label for="telefono">Teléfono</label>
                            @error('telefono')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Información Médica -->
                    <div class="col-md-12">
                        <h5 class="border-bottom pb-2 mb-4">
                            <i class="fas fa-heartbeat me-2"></i>Información Médica
                        </h5>
                    </div>

                    <!-- Fecha de Nacimiento -->
                    <div class="col-md-4">
                        <div class="form-floating mb-3">
                            <input type="date" 
                                   class="form-control @error('fecha_nacimiento') is-invalid @enderror" 
                                   id="fecha_nacimiento" 
                                   name="fecha_nacimiento" 
                                   value="{{ old('fecha_nacimiento', isset($paciente) ? $paciente->fecha_nacimiento : '') }}"
                                   required>
                            <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                            @error('fecha_nacimiento')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Tipo de Sangre -->
                    <div class="col-md-4">
                        <div class="form-floating mb-3">
                            <select class="form-select @error('tipo_sangre') is-invalid @enderror" 
                                    id="tipo_sangre" 
                                    name="tipo_sangre" 
                                    required>
                                <option value="">Seleccione tipo de sangre</option>
                                @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $tipo)
                                    <option value="{{ $tipo }}" 
                                            {{ old('tipo_sangre', isset($paciente) ? $paciente->tipo_sangre : '') == $tipo ? 'selected' : '' }}>
                                        {{ $tipo }}
                                    </option>
                                @endforeach
                            </select>
                            <label for="tipo_sangre">Tipo de Sangre</label>
                            @error('tipo_sangre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Estado -->
                    <div class="col-md-4">
                        <div class="form-floating mb-3">
                            <select class="form-select @error('estado') is-invalid @enderror" 
                                    id="estado" 
                                    name="estado" 
                                    required>
                                <option value="activo" {{ old('estado', isset($paciente) ? $paciente->estado : '') == 'activo' ? 'selected' : '' }}>Activo</option>
                                <option value="inactivo" {{ old('estado', isset($paciente) ? $paciente->estado : '') == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                            <label for="estado">Estado</label>
                            @error('estado')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Alergias -->
                    <div class="col-md-12">
                        <div class="form-floating mb-3">
                            <textarea class="form-control @error('alergias') is-invalid @enderror" 
                                      id="alergias" 
                                      name="alergias" 
                                      style="height: 100px">{{ old('alergias', isset($paciente) ? $paciente->alergias : '') }}</textarea>
                            <label for="alergias">Alergias</label>
                            @error('alergias')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Antecedentes -->
                    <div class="col-md-12">
                        <div class="form-floating mb-3">
                            <textarea class="form-control @error('antecedentes') is-invalid @enderror" 
                                      id="antecedentes" 
                                      name="antecedentes" 
                                      style="height: 100px">{{ old('antecedentes', isset($paciente) ? $paciente->antecedentes : '') }}</textarea>
                            <label for="antecedentes">Antecedentes Médicos</label>
                            @error('antecedentes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Dirección -->
                    <div class="col-md-12">
                        <h5 class="border-bottom pb-2 mb-4">
                            <i class="fas fa-map-marker-alt me-2"></i>Dirección
                        </h5>
                    </div>

                    <!-- Dirección Completa -->
                    <div class="col-md-12">
                        <div class="form-floating mb-3">
                            <textarea class="form-control @error('direccion') is-invalid @enderror" 
                                      id="direccion" 
                                      name="direccion" 
                                      style="height: 100px"
                                      required>{{ old('direccion', isset($paciente) ? $paciente->direccion : '') }}</textarea>
                            <label for="direccion">Dirección Completa</label>
                            @error('direccion')
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
                            <i class="fas fa-save me-2"></i>{{ isset($paciente) ? 'Actualizar' : 'Guardar' }}
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
    .avatar-xl {
        width: 120px;
        height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .avatar-xl img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

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

    // Preview de imagen
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            
            reader.onload = function(e) {
                var img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'rounded-circle';
                
                var container = input.closest('.text-center').querySelector('.avatar-xl');
                container.innerHTML = '';
                container.appendChild(img);
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Calcular edad automáticamente
    document.getElementById('fecha_nacimiento').addEventListener('change', function() {
        var birthDate = new Date(this.value);
        var today = new Date();
        var age = today.getFullYear() - birthDate.getFullYear();
        var m = today.getMonth() - birthDate.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        document.getElementById('edad').value = age;
    });
</script>
@endpush
