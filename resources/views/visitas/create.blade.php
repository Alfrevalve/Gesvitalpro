<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Visita</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="container mt-5">
        <h2>Formulario de Visita</h2>
        <form action="{{ route('visitas.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="fecha_hora">Fecha y Hora:</label>
                <input type="datetime-local" class="form-control" id="fecha_hora" name="fecha_hora" required>
            </div>
            <div class="form-group">
                <label for="asesor">Asesor:</label>
                <input type="text" class="form-control" id="asesor" name="asesor" required>
            </div>
            <div class="form-group">
                <label for="institucion">Institución:</label>
                <input type="text" class="form-control" id="institucion" name="institucion" required>
            </div>
            <div class="form-group">
                <label for="persona_contactada">Persona Contactada:</label>
                <input type="text" class="form-control" id="persona_contactada" name="persona_contactada" required>
            </div>
            <div class="form-group">
                <label for="telefono">Teléfono:</label>
                <input type="tel" class="form-control" id="telefono" name="telefono" required>
            </div>
            <div class="form-group">
                <label for="motivo_visita">Motivo de la Visita:</label>
                <textarea class="form-control" id="motivo_visita" name="motivo_visita" required></textarea>
            </div>
            <div class="form-group">
                <label for="resumen_seguimiento">Resumen de Seguimiento:</label>
                <textarea class="form-control" id="resumen_seguimiento" name="resumen_seguimiento" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Guardar Visita</button>
        </form>
    </div>
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
