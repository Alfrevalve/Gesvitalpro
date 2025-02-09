@extends('layouts.bootstrap')

@section('content')
<h1>Crear Nueva Cirugía</h1>
<form action="{{ route('cirugias.store') }}" method="POST">
    @csrf
    <div>
        <label for="nombre">Nombre de la Cirugía:</label>
        <input type="text" name="nombre" required>
    </div>
    <div>
        <label for="fecha">Fecha:</label>
        <input type="date" name="fecha" required>
    </div>
    <button type="submit">Crear Cirugía</button>
</form>
@endsection
