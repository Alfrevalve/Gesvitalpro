@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Agregar Nuevo Equipo</h1>

    <form action="{{ route('equipments.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="name">Nombre</label>
            <input type="text" name="name" id="name" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="type">Tipo</label>
            <input type="text" name="type" id="type" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="serial_number">Número de Serie</label>
            <input type="text" name="serial_number" id="serial_number" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="line_id">Línea</label>
            <select name="line_id" id="line_id" class="form-control" required>
                <!-- Aquí se deben cargar las líneas disponibles -->
                @foreach($lines as $line)
                    <option value="{{ $line->id }}">{{ $line->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="status">Estado</label>
            <select name="status" id="status" class="form-control" required>
                <option value="available">Disponible</option>
                <option value="in_use">En Uso</option>
                <option value="maintenance">En Mantenimiento</option>
            </select>
        </div>

        <button type="submit" class="boton boton--azul">Guardar</button>
    </form>
</div>
@endsection
