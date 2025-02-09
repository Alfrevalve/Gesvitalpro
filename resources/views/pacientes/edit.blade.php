@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Editar Paciente</h1>
    <form action="{{ route('pacientes.update', $paciente->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="nombre">Nombre:</label>
            <input type="text" name="nombre" class="form-control" value="{{ $paciente->nombre }}" required>
        </div>
        <div class="form-group">
            <label for="edad">Edad:</label>
            <input type="number" name="edad" class="form-control" value="{{ $paciente->edad }}" required>
        </div>
        <div class="form-group">
            <label for="genero">Género:</label>
            <select name="genero" class="form-control" required>
                <option value="masculino" {{ $paciente->genero == 'masculino' ? 'selected' : '' }}>Masculino</option>
                <option value="femenino" {{ $paciente->genero == 'femenino' ? 'selected' : '' }}>Femenino</option>
                <option value="otro" {{ $paciente->genero == 'otro' ? 'selected' : '' }}>Otro</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Actualizar Paciente</button>
    </form>
</div>
@endsection
