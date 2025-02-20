@extends('adminlte::page')

@section('title', 'Gestión de Usuarios')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>Usuarios</h1>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
        <i class="fas fa-user-plus"></i> Nuevo Usuario
    </a>
</div>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover" id="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Estado</th>
                        <th>Último Acceso</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                @if($user->profile_photo_url)
                                    <img src="{{ $user->profile_photo_url }}"
                                         alt="Avatar"
                                         class="img-circle mr-2"
                                         style="width: 32px; height: 32px;">
                                @else
                                    <div class="bg-primary rounded-circle mr-2 d-flex align-items-center justify-content-center"
                                         style="width: 32px; height: 32px;">
                                        <span class="text-white">{{ substr($user->name, 0, 1) }}</span>
                                    </div>
                                @endif
                                {{ $user->name }}
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span class="badge badge-{{ $user->is_active ? 'success' : 'danger' }}">
                                {{ $user->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td>
                            {{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Nunca' }}
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('admin.users.edit', $user) }}"
                                   class="btn btn-sm btn-info"
                                   title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <button type="button"
                                        class="btn btn-sm btn-{{ $user->is_active ? 'warning' : 'success' }}"
                                        onclick="toggleUserStatus({{ $user->id }})"
                                        title="{{ $user->is_active ? 'Desactivar' : 'Activar' }}">
                                    <i class="fas fa-{{ $user->is_active ? 'ban' : 'check' }}"></i>
                                </button>

                                @if(Auth::id() !== $user->id)
                                <button type="button"
                                        class="btn btn-sm btn-danger"
                                        onclick="confirmDelete({{ $user->id }})"
                                        title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                            </div>

                            <form id="toggle-form-{{ $user->id }}"
                                  action="{{ route('admin.users.toggle-status', $user) }}"
                                  method="POST"
                                  style="display: none;">
                                @csrf
                                @method('PATCH')
                            </form>

                            <form id="delete-form-{{ $user->id }}"
                                  action="{{ route('admin.users.destroy', $user) }}"
                                  method="POST"
                                  style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $users->links() }}
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .img-circle {
        border-radius: 50%;
        object-fit: cover;
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('#users-table').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
            },
            "order": [[0, "desc"]],
            "pageLength": 10
        });
    });

    function toggleUserStatus(userId) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¿Deseas cambiar el estado de este usuario?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, cambiar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('toggle-form-' + userId).submit();
            }
        });
    }

    function confirmDelete(userId) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + userId).submit();
            }
        });
    }
</script>
@stop
