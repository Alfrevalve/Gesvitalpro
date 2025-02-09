@extends('layouts.bootstrap')

@section('content')
<h1>Editar Cirugía</h1>
<form action="{{ route('cirugias.update', $cirugia->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div>
        <label for="nombre">Nombre de la Cirugía:</label>
        <input type="text" name="nombre" value="{{ $cirugia->nombre }}" required>
    </div>
    <div>
        <label for="fecha">Fecha:</label>
        <input type="date" name="fecha" value="{{ $cirugia->fecha }}" required>
    </div>
    <button type="submit">Actualizar Cirugía</button>
</form>
@endsection
