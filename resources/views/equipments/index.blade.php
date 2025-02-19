@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Lista de Equipos</h1>
    <a href="{{ route('equipments.create') }}" class="boton boton--azul">Agregar Nuevo Equipo</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Número de Serie</th>
                <th>Línea</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($equipments as $equipment)
                <tr>
                    <td>{{ $equipment->name }}</td>
                    <td>{{ $equipment->type }}</td>
                    <td>{{ $equipment->serial_number }}</td>
                    <td>{{ $equipment->line->name }}</td>
                    <td>{{ $equipment->status }}</td>
                    <td>
                        <a href="{{ route('equipments.edit', $equipment) }}" class="boton boton--azul">Editar</a>
                        <form action="{{ route('equipments.destroy', $equipment) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="boton boton--rojo">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
