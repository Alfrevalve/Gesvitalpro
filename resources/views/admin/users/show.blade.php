@extends('adminlte::page')

@section('title', 'Detalles del Usuario')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>Detalles del Usuario</h1>
    <div>
        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
            <i class="fas fa-edit"></i> Editar
        </a>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
</div>
@stop

@section('content')
<div class="row">
    <div class="col-md-4">
        <!-- Perfil del Usuario -->
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <div class="text-center">
                    @if($user->profile_photo_url)
                        <img class="profile-user-img img-fluid img-circle"
                             src="{{ $user->profile_photo_url }}"
                             alt="Avatar de usuario">
                    @else
                        <div class="bg-primary rounded-circle mx-auto d-flex align-items-center justify-content-center profile-user-img">
                            <span class="text-white" style="font-size: 2.5rem;">
                                {{ substr($user->name, 0, 1) }}
                            </span>
                        </div>
                    @endif
                </div>

                <h3 class="profile-username text-center">{{ $user->name }}</h3>
                <p class="text-muted text-center">{{ $user->email }}</p>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Estado</b>
                        <span class="float-right">
                            <span class="badge badge-{{ $user->is_active ? 'success' : 'danger' }}">
                                {{ $user->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </span>
                    </li>
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
                </ul>
            </div>
        </div>

        <!-- Roles y Permisos -->
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Roles y Permisos</h3>
            </div>
            <div class="card-body">
                <strong><i class="fas fa-user-tag mr-1"></i> Roles</strong>
                <p class="text-muted">
                    @forelse($user->roles as $role)
                        <span class="badge badge-info">{{ $role->name }}</span>
                    @empty
                        <span class="text-muted">Sin roles asignados</span>
                    @endforelse
                </p>

                <hr>

                <strong><i class="fas fa-key mr-1"></i> Permisos</strong>
                <p class="text-muted">
                    @forelse($user->getAllPermissions() as $permission)
                        <span class="badge badge-secondary">{{ $permission->name }}</span>
                    @empty
                        <span class="text-muted">Sin permisos específicos</span>
                    @endforelse
                </p>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <!-- Actividad Reciente -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Actividad Reciente</h3>
            </div>
            <div class="card-body">
                <div class="timeline">
                    @forelse($user->activities()->latest()->take(10)->get() as $activity)
                        <div class="time-label">
                            <span class="bg-primary">
                                {{ $activity->created_at->format('d M Y') }}
                            </span>
                        </div>
                        <div>
                            <i class="fas fa-{{ $activity->icon ?? 'dot-circle' }} bg-{{ $activity->type_color ?? 'info' }}"></i>
                            <div class="timeline-item">
                                <span class="time">
                                    <i class="fas fa-clock"></i>
                                    {{ $activity->created_at->format('H:i') }}
                                </span>
                                <h3 class="timeline-header">
                                    {{ $activity->description }}
                                </h3>
                                @if($activity->properties)
                                    <div class="timeline-body">
                                        <pre class="bg-light p-2 rounded">{{ json_encode($activity->properties, JSON_PRETTY_PRINT) }}</pre>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted">
                            <p>No hay actividad registrada</p>
                        </div>
                    @endforelse
                    <div>
                        <i class="fas fa-clock bg-gray"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Estadísticas</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="info-box">
                            <span class="info-box-icon bg-info">
                                <i class="fas fa-sign-in-alt"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Accesos</span>
                                <span class="info-box-number">{{ $user->login_count ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box">
                            <span class="info-box-icon bg-success">
                                <i class="fas fa-tasks"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Actividades</span>
                                <span class="info-box-number">{{ $user->activities()->count() }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box">
                            <span class="info-box-icon bg-warning">
                                <i class="fas fa-user-clock"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Días Activo</span>
                                <span class="info-box-number">{{ $user->created_at->diffInDays(now()) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
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

.timeline {
    margin: 0;
    padding: 0;
    position: relative;
}

.timeline::before {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    width: 4px;
    background: #ddd;
    left: 31px;
    margin: 0;
    border-radius: 2px;
}

.timeline > div {
    margin-right: 10px;
    margin-bottom: 15px;
    position: relative;
}

.time-label {
    margin-bottom: 15px;
}

.timeline-item {
    margin-left: 60px;
    margin-right: 15px;
    margin-bottom: 15px;
    padding: 1em;
    background: #f8f9fa;
    border-radius: 3px;
    position: relative;
}

.timeline-item::before {
    content: '';
    position: absolute;
    top: 16px;
    left: -14px;
    width: 0;
    height: 0;
    border-style: solid;
    border-width: 14px 14px 14px 0;
    border-color: transparent #f8f9fa transparent transparent;
}

.timeline-item > .time {
    float: right;
    color: #999;
    font-size: 12px;
}

.timeline-item > .timeline-header {
    margin: 0;
    color: #555;
    border-bottom: 1px solid #f4f4f4;
    padding: 10px 0;
    font-size: 14px;
    line-height: 1.1;
}

.timeline-item > .timeline-body {
    padding: 10px 0;
    font-size: 12px;
}

.timeline > div > i {
    width: 30px;
    height: 30px;
    font-size: 15px;
    line-height: 30px;
    position: absolute;
    color: #fff;
    background: #d2d6de;
    border-radius: 50%;
    text-align: center;
    left: 18px;
    top: 0;
}
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Activar tooltips
    $('[data-toggle="tooltip"]').tooltip();
});
</script>
@stop
