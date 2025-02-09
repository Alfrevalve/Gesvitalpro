@extends('layouts.app')

@section('title', 'Gestión de Usuarios')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-users me-2"></i>Gestión de Usuarios
        </h1>
        <div>
            <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fas fa-file-excel me-2"></i>Importar Usuarios
            </button>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
                <i class="fas fa-user-plus me-2"></i>Nuevo Usuario
            </button>
        </div>
    </div>

    <!-- Filtros Rápidos -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Usuarios
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Usuarios Activos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['activos'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Roles Únicos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['roles'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-tag fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Nuevos (30 días)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['nuevos'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-plus fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros de Búsqueda -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('usuarios.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <div class="form-floating">
                        <input type="text" 
                               class="form-control" 
                               id="search" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Buscar usuarios">
                        <label for="search">Buscar por nombre, email o username</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-floating">
                        <select class="form-select" id="role" name="role">
                            <option value="">Todos los roles</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ request('role') == $role->id ? 'selected' : '' }}>
                                    {{ ucfirst($role->name) }}
                                </option>
                            @endforeach
                        </select>
                        <label for="role">Rol</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-floating">
                        <select class="form-select" id="status" name="status">
                            <option value="">Todos los estados</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Activo</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                        <label for="status">Estado</label>
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i>Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de Usuarios -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Roles</th>
                            <th>Estado</th>
                            <th>Último Acceso</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($usuarios as $usuario)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-3">
                                            @if($usuario->avatar)
                                                <img src="{{ asset('storage/' . $usuario->avatar) }}" 
                                                     class="rounded-circle" 
                                                     alt="{{ $usuario->name }}"
                                                     width="40">
                                            @else
                                                <div class="avatar-initial rounded-circle bg-primary">
                                                    {{ strtoupper(substr($usuario->name, 0, 2)) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $usuario->name }}</div>
                                            <div class="small text-muted">{{ $usuario->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @foreach($usuario->roles as $role)
                                        <span class="badge bg-info">{{ ucfirst($role->name) }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    @if($usuario->status === 'active')
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-danger">Inactivo</span>
                                    @endif
                                </td>
                                <td>
                                    @if($usuario->last_login_at)
                                        <div class="small">
                                            {{ $usuario->last_login_at->format('d/m/Y H:i') }}
                                            <div class="text-muted">
                                                {{ $usuario->last_login_at->diffForHumans() }}
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">Nunca</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" 
                                                class="btn btn-sm btn-info me-2" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#viewUserModal{{ $usuario->id }}"
                                                title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" 
                                                class="btn btn-sm btn-warning me-2" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editUserModal{{ $usuario->id }}"
                                                title="Editar usuario">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        @if($usuario->id !== auth()->id())
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deleteUserModal{{ $usuario->id }}"
                                                    title="Eliminar usuario">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-users fa-3x mb-3"></i>
                                        <p>No se encontraron usuarios</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted">
                    Mostrando {{ $usuarios->firstItem() ?? 0 }} - {{ $usuarios->lastItem() ?? 0 }} 
                    de {{ $usuarios->total() }} usuarios
                </div>
                {{ $usuarios->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal Crear Usuario -->
<div class="modal fade" id="createUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus me-2"></i>Nuevo Usuario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('usuarios.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Avatar -->
                        <div class="col-12 text-center mb-3">
                            <div class="avatar-upload mx-auto">
                                <div class="avatar-preview rounded-circle">
                                    <img src="{{ asset('images/default-avatar.png') }}" 
                                         alt="Preview" 
                                         id="avatarPreview">
                                </div>
                                <label class="btn btn-primary mt-2">
                                    <i class="fas fa-upload me-2"></i>Subir Avatar
                                    <input type="file" 
                                           name="avatar" 
                                           class="d-none" 
                                           accept="image/*"
                                           onchange="previewImage(this)">
                                </label>
                            </div>
                        </div>

                        <!-- Información Básica -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name') }}" 
                                       required>
                                <label for="name">Nombre Completo</label>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" 
                                       class="form-control @error('username') is-invalid @enderror" 
                                       id="username" 
                                       name="username" 
                                       value="{{ old('username') }}" 
                                       required>
                                <label for="username">Nombre de Usuario</label>
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email') }}" 
                                       required>
                                <label for="email">Correo Electrónico</label>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       required>
                                <label for="password">Contraseña</label>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Roles -->
                        <div class="col-12">
                            <label class="form-label">Roles</label>
                            <div class="row g-3">
                                @foreach($roles as $role)
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   name="roles[]" 
                                                   value="{{ $role->id }}" 
                                                   id="role{{ $role->id }}">
                                            <label class="form-check-label" for="role{{ $role->id }}">
                                                {{ ucfirst($role->name) }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Estado -->
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       name="status" 
                                       value="active" 
                                       id="statusSwitch" 
                                       checked>
                                <label class="form-check-label" for="statusSwitch">Usuario Activo</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Guardar Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($usuarios as $usuario)
    <!-- Modal Ver Usuario -->
    <div class="modal fade" id="viewUserModal{{ $usuario->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user me-2"></i>Detalles del Usuario
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <div class="avatar-lg mx-auto mb-3">
                            @if($usuario->avatar)
                                <img src="{{ asset('storage/' . $usuario->avatar) }}" 
                                     class="rounded-circle" 
                                     alt="{{ $usuario->name }}">
                            @else
                                <div class="avatar-initial rounded-circle bg-primary">
                                    {{ strtoupper(substr($usuario->name, 0, 2)) }}
                                </div>
                            @endif
                        </div>
                        <h5 class="mb-1">{{ $usuario->name }}</h5>
                        <p class="text-muted mb-0">{{ $usuario->email }}</p>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <div class="detail-item">
                                <label class="text-muted">Usuario</label>
                                <p class="mb-0">{{ $usuario->username }}</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="detail-item">
                                <label class="text-muted">Estado</label>
                                <p class="mb-0">
                                    @if($usuario->status === 'active')
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-danger">Inactivo</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="detail-item">
                                <label class="text-muted">Roles</label>
                                <p class="mb-0">
                                    @foreach($usuario->roles as $role)
                                        <span class="badge bg-info me-1">{{ ucfirst($role->name) }}</span>
                                    @endforeach
                                </p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="detail-item">
                                <label class="text-muted">Creado</label>
                                <p class="mb-0">{{ $usuario->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="detail-item">
                                <label class="text-muted">Último Acceso</label>
                                <p class="mb-0">
                                    @if($usuario->last_login_at)
                                        {{ $usuario->last_login_at->format('d/m/Y H:i') }}
                                    @else
                                        Nunca
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar Usuario -->
    <div class="modal fade" id="editUserModal{{ $usuario->id }}" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-edit me-2"></i>Editar Usuario
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('usuarios.update', $usuario->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="row g-3">
                            <!-- Avatar -->
                            <div class="col-12 text-center mb-3">
                                <div class="avatar-upload mx-auto">
                                    <div class="avatar-preview rounded-circle">
                                        @if($usuario->avatar)
                                            <img src="{{ asset('storage/' . $usuario->avatar) }}" 
                                                 alt="Preview" 
                                                 id="avatarPreview{{ $usuario->id }}">
                                        @else
                                            <img src="{{ asset('images/default-avatar.png') }}" 
                                                 alt="Preview" 
                                                 id="avatarPreview{{ $usuario->id }}">
                                        @endif
                                    </div>
                                    <label class="btn btn-primary mt-2">
                                        <i class="fas fa-upload me-2"></i>Cambiar Avatar
                                        <input type="file" 
                                               name="avatar" 
                                               class="d-none" 
                                               accept="image/*"
                                               onchange="previewImage(this, {{ $usuario->id }})">
                                    </label>
                                </div>
                            </div>

                            <!-- Información Básica -->
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name{{ $usuario->id }}" 
                                           name="name" 
                                           value="{{ old('name', $usuario->name) }}" 
                                           required>
                                    <label for="name{{ $usuario->id }}">Nombre Completo</label>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" 
                                           class="form-control @error('username') is-invalid @enderror" 
                                           id="username{{ $usuario->id }}" 
                                           name="username" 
                                           value="{{ old('username', $usuario->username) }}" 
                                           required>
                                    <label for="username{{ $usuario->id }}">Nombre de Usuario</label>
                                    @error('username')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           id="email{{ $usuario->id }}" 
                                           name="email" 
                                           value="{{ old('email', $usuario->email) }}" 
                                           required>
                                    <label for="email{{ $usuario->id }}">Correo Electrónico</label>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           id="password{{ $usuario->id }}" 
                                           name="password"
                                           placeholder="Dejar en blanco para mantener la actual">
                                    <label for="password{{ $usuario->id }}">Nueva Contraseña</label>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Roles -->
                            <div class="col-12">
                                <label class="form-label">Roles</label>
                                <div class="row g-3">
                                    @foreach($roles as $role)
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       name="roles[]" 
                                                       value="{{ $role->id }}" 
                                                       id="role{{ $role->id }}_{{ $usuario->id }}"
                                                       {{ $usuario->roles->contains($role->id) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="role{{ $role->id }}_{{ $usuario->id }}">
                                                    {{ ucfirst($role->name) }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Estado -->
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="status" 
                                           value="active" 
                                           id="statusSwitch{{ $usuario->id }}" 
                                           {{ $usuario->status === 'active' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="statusSwitch{{ $usuario->id }}">
                                        Usuario Activo
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Eliminar Usuario -->
    <div class="modal fade" id="deleteUserModal{{ $usuario->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirmar Eliminación
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro que desea eliminar al usuario <strong>{{ $usuario->name }}</strong>?</p>
                    <p class="text-danger mb-0">Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </button>
                    <form action="{{ route('usuarios.destroy', $usuario->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>Eliminar Usuario
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach

<!-- Modal Importar Usuarios -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-file-import me-2"></i>Importar Usuarios
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('usuarios.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="form-label">Archivo Excel</label>
                        <input type="file" 
                               class="form-control @error('file') is-invalid @enderror" 
                               name="file" 
                               accept=".xlsx,.xls,.csv"
                               required>
                        @error('file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            Formatos soportados: .xlsx, .xls, .csv
                        </div>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" 
                               type="checkbox" 
                               name="update_existing" 
                               id="updateExisting" 
                               value="1">
                        <label class="form-check-label" for="updateExisting">
                            Actualizar usuarios existentes
                        </label>
                    </div>

                    <a href="{{ route('usuarios.template') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-download me-2"></i>Descargar Plantilla
                    </a>
                </div>
                <div class="modal-footer">
