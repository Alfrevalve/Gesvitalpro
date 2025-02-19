@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Equipo</h1>

    <form action="{{ route('equipments.update', $equipment) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">Nombre</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ $equipment->name }}" required>
        </div>

        <div class="form-group">
            <label for="type">Tipo</label>
            <input type="text" name="type" id="type" class="form-control" value="{{ $equipment->type }}" required>
        </div>

        <div class="form-group">
            <label for="serial_number">Número de Serie</label>
            <input type="text" name="serial_number" id="serial_number" class="form-control" value="{{ $equipment->serial_number }}" required>
        </div>

        <div class="form-group">
            <label for="line_id">Línea</label>
            <select name="line_id" id="line_id" class="form-control" required>
                <!-- Aquí se deben cargar las líneas disponibles -->
                @foreach($lines as $line)
                    <option value="{{ $line->id }}" {{ $line->id == $equipment->line_id ? 'selected' : '' }}>{{ $line->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="status">Estado</label>
            <select name="status" id="status" class="form-control" required>
                <option value="available" {{ $equipment->status == 'available' ? 'selected' : '' }}>Disponible</option>
                <option value="in_use" {{ $equipment->status == 'in_use' ? 'selected' : '' }}>En Uso</option>
                <option value="maintenance" {{ $equipment->status == 'maintenance' ? 'selected' : '' }}>En Mantenimiento</option>
            </select>
        </div>

        <button type="submit" class="boton boton--azul">Actualizar</button>
    </form>
</div>
@endsection
