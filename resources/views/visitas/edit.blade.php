@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Editar Visita</h1>
    <form action="{{ route('visitas.update', $visita->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="fecha_hora">Fecha y Hora:</label>
            <input type="datetime-local" name="fecha_hora" class="form-control" value="{{ $visita->fecha_hora }}" required>
        </div>
        <div class="form-group">
            <label for="institucion">Institución:</label>
            <input type="text" name="institucion" class="form-control" value="{{ $visita->institucion }}" required>
        </div>
        <div class="form-group">
            <label for="persona_contactada">Persona Contactada:</label>
            <input type="text" name="persona_contactada" class="form-control" value="{{ $visita->persona_contactada }}" required>
        </div>
        <div class="form-group">
            <label for="motivo">Motivo:</label>
            <input type="text" name="motivo" class="form-control" value="{{ $visita->motivo }}" required>
        </div>
        <div class="form-group">
            <label for="seguimiento_requerido">Seguimiento Requerido:</label>
            <input type="checkbox" name="seguimiento_requerido" {{ $visita->seguimiento_requerido ? 'checked' : '' }}>
        </div>
        <button type="submit" class="btn btn-primary">Actualizar Visita</button>
    </form>
</div>
@endsection
