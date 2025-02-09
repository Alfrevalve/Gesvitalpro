<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Inventario</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="container">
        <h1>Agregar Inventario</h1>
        <form action="{{ route('inventarios.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="codigo">Código de Producto</label>
                <input type="text" name="codigo" id="codigo" required>
            </div>
            <div class="form-group">
                <label for="nombre">Nombre del Producto</label>
                <input type="text" name="nombre" id="nombre" required>
            </div>
            <div class="form-group">
                <label for="cantidad">Cantidad Disponible</label>
                <input type="number" name="cantidad" id="cantidad" required>
            </div>
            <div class="form-group">
                <label for="nivel_minimo">Stock Mínimo</label>
                <input type="number" name="nivel_minimo" id="nivel_minimo" required>
            </div>
            <button type="submit" class="btn btn-primary">Guardar Inventario</button>
        </form>
    </div>
</body>
</html>
