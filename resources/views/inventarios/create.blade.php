@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Crear Nuevo Inventario</h1>
    <form action="{{ route('inventarios.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="nombre">Nombre:</label>
            <input type="text" name="nombre" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="categoria">Categoría:</label>
            <input type="text" name="categoria" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="cantidad">Cantidad:</label>
            <input type="number" name="cantidad" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="nivel_minimo">Nivel Mínimo:</label>
            <input type="number" name="nivel_minimo" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="ubicacion">Ubicación:</label>
            <input type="text" name="ubicacion" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="fecha_mantenimiento">Fecha de Mantenimiento:</label>
            <input type="date" name="fecha_mantenimiento" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Crear Inventario</button>
    </form>
</div>
@endsection
