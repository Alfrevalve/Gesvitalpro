<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Cirugía</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="container">
        <h1>Agregar Cirugía</h1>
        <form action="{{ route('cirugias.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="paciente_id">Nombre del Paciente</label>
                <select name="paciente_id" id="paciente_id" required>
                    <option value="">Seleccionar Paciente</option>
                    @foreach($pacientes as $paciente)
                        <option value="{{ $paciente->id }}">{{ $paciente->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="fecha_hora">Fecha y Hora</label>
                <input type="datetime-local" name="fecha_hora" id="fecha_hora" required>
            </div>
            <div class="form-group">
                <label for="hospital">Hospital</label>
                <input type="text" name="hospital" id="hospital" required>
            </div>
            <div class="form-group">
                <label for="equipo_requerido">Equipo Requerido</label>
                <input type="text" name="equipo_requerido" id="equipo_requerido" required>
            </div>
            <div class="form-group">
                <label for="consumibles">Consumibles</label>
                <input type="text" name="consumibles" id="consumibles" required>
            </div>
            <div class="form-group">
                <label for="personal_asignado">Personal Asignado</label>
                <input type="text" name="personal_asignado" id="personal_asignado" required>
            </div>
            <div class="form-group">
                <label for="tiempo_estimado">Tiempo Estimado</label>
                <input type="text" name="tiempo_estimado" id="tiempo_estimado" required>
            </div>
            <div class="form-group">
                <label for="estado">Estado</label>
                <select name="estado" id="estado" required>
                    <option value="Programada">Programada</option>
                    <option value="En Proceso">En Proceso</option>
                    <option value="Terminada">Terminada</option>
                    <option value="Cancelada">Cancelada</option>
                    <option value="Reprogramada">Reprogramada</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Guardar Cirugía</button>
        </form>
    </div>
</body>
</html>
