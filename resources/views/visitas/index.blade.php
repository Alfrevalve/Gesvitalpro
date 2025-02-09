@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Lista de Visitas</h1>
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Fecha y Hora</th>
                <th>Institución</th>
                <th>Persona Contactada</th>
                <th>Motivo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($visitas as $visita)
            <tr>
                <td>{{ $visita->fecha_hora }}</td>
                <td>{{ $visita->institucion }}</td>
                <td>{{ $visita->persona_contactada }}</td>
                <td>{{ $visita->motivo }}</td>
                <td>
                    <a href="{{ route('visitas.edit', $visita->id) }}" class="btn btn-warning">Editar</a>
                    <form action="{{ route('visitas.destroy', $visita->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $visitas->links() }}
</div>
@endsection
