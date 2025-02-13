@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Crear Reporte Post Cirugía</h3>
                </div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('reportes.post-cirugia.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Fecha de Cirugía y Selección de Cirugía -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fecha_cirugia">Fecha de Cirugía</label>
                                    <input type="date" class="form-control" id="fecha_cirugia" name="fecha_cirugia" 
                                           value="{{ old('fecha_cirugia', date('Y-m-d')) }}" required
                                           onchange="cargarCirugias()">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="cirugia_id">Cirugía Programada</label>
                                    <select class="form-control" id="cirugia_id" name="cirugia_id" required>
                                        <option value="">Seleccione una cirugía</option>
                                        @foreach($cirugias as $cirugia)
                                            <option value="{{ $cirugia->id }}" 
                                                    data-medico="{{ $cirugia->medico->nombre }}"
                                                    data-paciente="{{ $cirugia->paciente->nombre }}"
                                                    data-institucion="{{ $cirugia->institucion->nombre }}"
                                                    data-hora="{{ \Carbon\Carbon::parse($cirugia->fecha_hora)->format('H:i') }}">
                                                {{ \Carbon\Carbon::parse($cirugia->fecha_hora)->format('H:i') }} - 
                                                {{ $cirugia->paciente->nombre }} - 
                                                {{ $cirugia->tipo_cirugia }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Información automática -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Instrumentista</label>
                                    <input type="text" class="form-control" value="{{ $instrumentista->name }}" readonly>
                                    <input type="hidden" name="instrumentista_id" value="{{ $instrumentista->id }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Médico</label>
                                    <input type="text" class="form-control" id="medico_nombre" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Paciente</label>
                                    <input type="text" class="form-control" id="paciente_nombre" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Institución</label>
                                    <input type="text" class="form-control" id="institucion_nombre" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- Hora Programada, Inicio y Fin -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Hora Programada</label>
                                    <input type="text" class="form-control" id="hora_programada" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="hora_inicio">Hora de Inicio</label>
                                    <input type="time" class="form-control" id="hora_inicio" name="hora_inicio" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="hora_fin">Hora de Fin</label>
                                    <input type="time" class="form-control" id="hora_fin" name="hora_fin" required>
                                </div>
                            </div>
                        </div>

                        <!-- Hoja de Consumo -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="hoja_consumo">Hoja de Consumo</label>
                                    <select class="form-control" id="hoja_consumo" name="hoja_consumo" required 
                                            onchange="toggleHojaConsumoFile()">
                                        <option value="1">Sí</option>
                                        <option value="0">No</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group" id="hoja_consumo_file_group">
                                    <label for="hoja_consumo_archivo">Archivo de Hoja de Consumo</label>
                                    <input type="file" class="form-control" id="hoja_consumo_archivo" 
                                           name="hoja_consumo_archivo" accept=".jpg,.jpeg,.pdf">
                                    <small class="form-text text-muted">
                                        Formatos permitidos: JPG, JPEG, PDF. Tamaño máximo: 2MB
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Sistemas -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Sistemas Utilizados</label>
                                    <div class="sistemas-container">
                                        @foreach($sistemas as $sistema)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="sistemas[]" value="{{ $sistema->id }}" 
                                                       id="sistema_{{ $sistema->id }}">
                                                <label class="form-check-label" for="sistema_{{ $sistema->id }}">
                                                    {{ $sistema->nombre }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Guardar Reporte</button>
                                <a href="{{ route('reportes.index') }}" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('cirugia_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    
    // Actualizar campos automáticos
    document.getElementById('medico_nombre').value = selectedOption.dataset.medico;
    document.getElementById('paciente_nombre').value = selectedOption.dataset.paciente;
    document.getElementById('institucion_nombre').value = selectedOption.dataset.institucion;
    document.getElementById('hora_programada').value = selectedOption.dataset.hora;
});

function toggleHojaConsumoFile() {
    const hojaConsumo = document.getElementById('hoja_consumo');
    const fileGroup = document.getElementById('hoja_consumo_file_group');
    const fileInput = document.getElementById('hoja_consumo_archivo');
    
    if (hojaConsumo.value === '1') {
        fileGroup.style.display = 'block';
        fileInput.required = true;
    } else {
        fileGroup.style.display = 'none';
        fileInput.required = false;
        fileInput.value = ''; // Limpiar el archivo si se selecciona "No"
    }
}

// Llamar a la función al cargar la página para establecer el estado inicial
document.addEventListener('DOMContentLoaded', function() {
    toggleHojaConsumoFile();
});

function cargarCirugias() {
    const fecha = document.getElementById('fecha_cirugia').value;
    
    // Realizar petición AJAX para obtener las cirugías de la fecha seleccionada
    fetch(`/api/cirugias-por-fecha/${fecha}`)
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('cirugia_id');
            select.innerHTML = '<option value="">Seleccione una cirugía</option>';
            
            data.forEach(cirugia => {
                const option = document.createElement('option');
                option.value = cirugia.id;
                option.dataset.medico = cirugia.medico.nombre;
                option.dataset.paciente = cirugia.paciente.nombre;
                option.dataset.institucion = cirugia.institucion.nombre;
                option.dataset.hora = moment(cirugia.fecha_hora).format('HH:mm');
                option.textContent = `${moment(cirugia.fecha_hora).format('HH:mm')} - ${cirugia.paciente.nombre} - ${cirugia.tipo_cirugia}`;
                select.appendChild(option);
            });
        })
        .catch(error => console.error('Error:', error));
}
</script>
@endpush

@push('styles')
<style>
.sistemas-container {
    max-height: 200px;
    overflow-y: auto;
    border: 1px solid #ddd;
    padding: 10px;
    border-radius: 4px;
}

.form-check {
    margin-bottom: 8px;
}
</style>
@endpush
