@extends('layouts.app')

@section('title', isset($item) ? 'Editar Item' : 'Nuevo Item')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-box me-2"></i>
            {{ isset($item) ? 'Editar Item' : 'Nuevo Item' }}
        </h1>
        <a href="{{ route('inventarios.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    <!-- Formulario -->
    <div class="card">
        <div class="card-body">
            <form action="{{ isset($item) ? route('inventarios.update', $item->id) : route('inventarios.store') }}" 
                  method="POST" 
                  enctype="multipart/form-data"
                  class="needs-validation" 
                  novalidate>
                @csrf
                @if(isset($item))
                    @method('PUT')
                @endif

                <div class="row g-4">
                    <!-- Información Básica -->
                    <div class="col-md-12">
                        <h5 class="border-bottom pb-2 mb-4">
                            <i class="fas fa-info-circle me-2"></i>Información Básica
                        </h5>
                    </div>

                    <!-- Imagen -->
                    <div class="col-md-12 mb-4">
                        <div class="text-center">
                            <div class="avatar-xl mx-auto mb-3">
                                @if(isset($item) && $item->imagen)
                                    <img src="{{ asset('storage/' . $item->imagen) }}" 
                                         class="img-thumbnail" 
                                         alt="Imagen actual">
                                @else
                                    <i class="fas fa-box fa-5x text-secondary"></i>
                                @endif
                            </div>
                            <div class="mt-2">
                                <label class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-upload me-2"></i>Subir Imagen
                                    <input type="file" 
                                           name="imagen" 
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
                                   value="{{ old('nombre', isset($item) ? $item->nombre : '') }}"
                                   required>
                            <label for="nombre">Nombre del Item</label>
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Código -->
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="text" 
                                   class="form-control @error('codigo') is-invalid @enderror" 
                                   id="codigo" 
                                   name="codigo" 
                                   value="{{ old('codigo', isset($item) ? $item->codigo : '') }}"
                                   required>
                            <label for="codigo">Código</label>
                            @error('codigo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Categoría -->
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <select class="form-select @error('categoria') is-invalid @enderror" 
                                    id="categoria" 
                                    name="categoria" 
                                    required>
                                <option value="">Seleccione una categoría</option>
                                @foreach($categorias as $categoria)
                                    <option value="{{ $categoria }}" 
                                            {{ old('categoria', isset($item) ? $item->categoria : '') == $categoria ? 'selected' : '' }}>
                                        {{ ucfirst($categoria) }}
                                    </option>
                                @endforeach
                            </select>
                            <label for="categoria">Categoría</label>
                            @error('categoria')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Ubicación -->
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="text" 
                                   class="form-control @error('ubicacion') is-invalid @enderror" 
                                   id="ubicacion" 
                                   name="ubicacion" 
                                   value="{{ old('ubicacion', isset($item) ? $item->ubicacion : '') }}">
                            <label for="ubicacion">Ubicación en Almacén</label>
                            @error('ubicacion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Información de Stock -->
                    <div class="col-md-12">
                        <h5 class="border-bottom pb-2 mb-4">
                            <i class="fas fa-boxes me-2"></i>Información de Stock
                        </h5>
                    </div>

                    <!-- Stock Actual -->
                    <div class="col-md-4">
                        <div class="form-floating mb-3">
                            <input type="number" 
                                   class="form-control @error('stock') is-invalid @enderror" 
                                   id="stock" 
                                   name="stock" 
                                   value="{{ old('stock', isset($item) ? $item->stock : 0) }}"
                                   min="0"
                                   required>
                            <label for="stock">Stock Actual</label>
                            @error('stock')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Stock Mínimo -->
                    <div class="col-md-4">
                        <div class="form-floating mb-3">
                            <input type="number" 
                                   class="form-control @error('stock_minimo') is-invalid @enderror" 
                                   id="stock_minimo" 
                                   name="stock_minimo" 
                                   value="{{ old('stock_minimo', isset($item) ? $item->stock_minimo : 0) }}"
                                   min="0"
                                   required>
                            <label for="stock_minimo">Stock Mínimo</label>
                            @error('stock_minimo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Precio -->
                    <div class="col-md-4">
                        <div class="form-floating mb-3">
                            <input type="number" 
                                   class="form-control @error('precio') is-invalid @enderror" 
                                   id="precio" 
                                   name="precio" 
                                   value="{{ old('precio', isset($item) ? $item->precio : 0.00) }}"
                                   step="0.01"
                                   min="0"
                                   required>
                            <label for="precio">Precio Unitario</label>
                            @error('precio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Descripción -->
                    <div class="col-md-12">
                        <div class="form-floating mb-3">
                            <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                      id="descripcion" 
                                      name="descripcion" 
                                      style="height: 100px">{{ old('descripcion', isset($item) ? $item->descripcion : '') }}</textarea>
                            <label for="descripcion">Descripción</label>
                            @error('descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Notas -->
                    <div class="col-md-12">
                        <div class="form-floating mb-3">
                            <textarea class="form-control @error('notas') is-invalid @enderror" 
                                      id="notas" 
                                      name="notas" 
                                      style="height: 100px">{{ old('notas', isset($item) ? $item->notas : '') }}</textarea>
                            <label for="notas">Notas Adicionales</label>
                            @error('notas')
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
                            <i class="fas fa-save me-2"></i>{{ isset($item) ? 'Actualizar' : 'Guardar' }}
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
                img.className = 'img-thumbnail';
                
                var container = input.closest('.text-center').querySelector('.avatar-xl');
                container.innerHTML = '';
                container.appendChild(img);
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Formateo automático del precio
    document.getElementById('precio').addEventListener('blur', function(e) {
        if(this.value) {
            this.value = parseFloat(this.value).toFixed(2);
        }
    });
</script>
@endpush
