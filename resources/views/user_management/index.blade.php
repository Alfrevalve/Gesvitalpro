@extends('layouts.bootstrap')

@section('content')
<div class="container mt-4">
    <h1 class="mb-4">Lista de Usuarios</h1>
    @if($usuarios->isEmpty())
        <div class="alert alert-warning" role="alert">
            No hay usuarios registrados.
        </div>
    @else
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($usuarios as $usuario)
                <tr>
                    <td>{{ $usuario->name }}</td>
                    <td>{{ $usuario->email }}</td>
                    <td>{{ $usuario->rol->nombre }}</td>
                    <td>
                        <a href="{{ route('user.edit', $usuario->id) }}" class="btn btn-warning">Editar</a>
                        <form action="{{ route('user.destroy', $usuario->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $usuarios->links() }}
    @endif
</div>
@endsection
