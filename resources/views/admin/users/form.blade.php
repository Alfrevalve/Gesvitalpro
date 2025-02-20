@extends('adminlte::page')

@section('title', isset($user) ? 'Editar Usuario' : 'Nuevo Usuario')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>{{ isset($user) ? 'Editar Usuario' : 'Nuevo Usuario' }}</h1>
    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
</div>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <form action="{{ isset($user) ? route('admin.users.update', $user) : route('admin.users.store') }}"
                      method="POST"
                      enctype="multipart/form-data">
                    @csrf
                    @if(isset($user))
                        @method('PUT')
                    @endif

                    <div class="form-group">
                        <label for="name">Nombre</label>
                        <input type="text"
                               class="form-control @error('name') is-invalid @enderror"
                               id="name"
                               name="name"
                               value="{{ old('name', $user->name ?? '') }}"
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email"
                               class="form-control @error('email') is-invalid @enderror"
                               id="email"
                               name="email"
                               value="{{ old('email', $user->email ?? '') }}"
                               required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @if(!isset($user))
                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <input type="password"
                               class="form-control @error('password') is-invalid @enderror"
                               id="password"
                               name="password"
                               required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Confirmar Contraseña</label>
                        <input type="password"
                               class="form-control"
                               id="password_confirmation"
                               name="password_confirmation"
                               required>
                    </div>
                    @endif

                    @if(isset($user))
                    <div class="form-group">
                        <label for="new_password">Nueva Contraseña (opcional)</label>
                        <input type="password"
                               class="form-control @error('password') is-invalid @enderror"
                               id="new_password"
                               name="password">
                        <small class="form-text text-muted">
                            Dejar en blanco para mantener la contraseña actual
                        </small>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Confirmar Nueva Contraseña</label>
                        <input type="password"
                               class="form-control"
                               id="password_confirmation"
                               name="password_confirmation">
                    </div>
                    @endif

                    <div class="form-group">
                        <label for="roles">Roles</label>
                        <select class="form-control select2 @error('roles') is-invalid @enderror"
                                id="roles"
                                name="roles[]"
                                multiple>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}"
                                        {{ isset($user) && $user->roles->contains($role->id) ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('roles')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox"
                                   class="custom-control-input"
                                   id="is_active"
                                   name="is_active"
                                   value="1"
                                   {{ old('is_active', isset($user) ? $user->is_active : true) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">Usuario Activo</label>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            {{ isset($user) ? 'Actualizar' : 'Crear' }} Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Información Adicional</h3>
            </div>
            <div class="card-body">
                @if(isset($user))
                    <div class="text-center mb-4">
                        @if($user->profile_photo_url)
                            <img src="{{ $user->profile_photo_url }}"
                                 alt="Avatar"
                                 class="img-circle profile-user-img img-fluid">
                        @else
                            <div class="bg-primary rounded-circle mx-auto d-flex align-items-center justify-content-center"
                                 style="width: 100px; height: 100px;">
                                <span class="text-white" style="font-size: 2.5rem;">
                                    {{ substr($user->name, 0, 1) }}
                                </span>
                            </div>
                        @endif
                    </div>

                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>Fecha de Registro</b>
                            <span class="float-right">{{ $user->created_at->format('d/m/Y') }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>Último Acceso</b>
                            <span class="float-right">
                                {{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Nunca' }}
                            </span>
                        </li>
                        <li class="list-group-item">
                            <b>Estado</b>
                            <span class="float-right">
                                <span class="badge badge-{{ $user->is_active ? 'success' : 'danger' }}">
                                    {{ $user->is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </span>
                        </li>
                    </ul>
                @else
                    <p class="text-muted mb-0">
                        Complete el formulario para crear un nuevo usuario.
                        Los usuarios pueden tener múltiples roles que definen sus permisos en el sistema.
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .profile-user-img {
        width: 100px;
        height: 100px;
        object-fit: cover;
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap4',
            placeholder: 'Seleccionar roles',
            language: {
                noResults: function() {
                    return "No se encontraron resultados";
                }
            }
        });
    });
</script>
@stop
