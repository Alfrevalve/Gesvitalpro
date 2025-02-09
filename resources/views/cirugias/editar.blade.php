<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cirugía</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="container">
        <h1>Editar Cirugía</h1>
        <form action="{{ route('cirugias.update', $cirugia->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="paciente_id">Nombre del Paciente</label>
                <select name="paciente_id" id="paciente_id" required>
                    <option value="">Seleccionar Paciente</option>
                    @foreach($pacientes as $paciente)
                        <option value="{{ $paciente->id }}" {{ $paciente->id == $cirugia->paciente_id ? 'selected' : '' }}>
                            {{ $paciente->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="fecha_hora">Fecha y Hora</label>
                <input type="datetime-local" name="fecha_hora" id="fecha_hora" value="{{ $cirugia->fecha_hora }}" required>
            </div>
            <div class="form-group">
                <label for="hospital">Hospital</label>
                <input type="text" name="hospital" id="hospital" value="{{ $cirugia->hospital }}" required>
            </div>
            <div class="form-group">
                <label for="equipo_requerido">Equipo Requerido</label>
                <input type="text" name="equipo_requerido" id="equipo_requerido" value="{{ $cirugia->equipo_requerido }}" required>
            </div>
            <div class="form-group">
                <label for="consumibles">Consumibles</label>
                <input type="text" name="consumibles" id="consumibles" value="{{ $cirugia->consumibles }}" required>
            </div>
            <div class="form-group">
                <label for="personal_asignado">Personal Asignado</label>
                <input type="text" name="personal_asignado" id="personal_asignado" value="{{ $cirugia->personal_asignado }}" required>
            </div>
            <div class="form-group">
                <label for="tiempo_estimado">Tiempo Estimado</label>
                <input type="text" name="tiempo_estimado" id="tiempo_estimado" value="{{ $cirugia->tiempo_estimado }}" required>
            </div>
            <div class="form-group">
                <label for="estado">Estado</label>
                <select name="estado" id="estado" required>
                    <option value="Programada" {{ $cirugia->estado == 'Programada' ? 'selected' : '' }}>Programada</option>
                    <option value="En Proceso" {{ $cirugia->estado == 'En Proceso' ? 'selected' : '' }}>En Proceso</option>
                    <option value="Terminada" {{ $cirugia->estado == 'Terminada' ? 'selected' : '' }}>Terminada</option>
                    <option value="Cancelada" {{ $cirugia->estado == 'Cancelada' ? 'selected' : '' }}>Cancelada</option>
                    <option value="Reprogramada" {{ $cirugia->estado == 'Reprogramada' ? 'selected' : '' }}>Reprogramada</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Actualizar Cirugía</button>
        </form>
    </div>
</body>
</html>
