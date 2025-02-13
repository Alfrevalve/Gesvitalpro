@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Crear Reporte de Visita</h3>
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

                    <form action="{{ route('reportes.visita.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Fecha y Visita -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fecha_visita">Fecha de Visita</label>
                                    <input type="date" class="form-control" id="fecha_visita" name="fecha_visita" 
                                           value="{{ old('fecha_visita', date('Y-m-d')) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="visita_id">Visita Relacionada</label>
                                    <select class="form-control" id="visita_id" name="visita_id" required>
                                        <option value="">Seleccione una visita</option>
                                        @foreach($visitas as $visita)
                                            <option value="{{ $visita->id }}" 
                                                    data-institucion="{{ $visita->institucion_id }}">
                                                {{ \Carbon\Carbon::parse($visita->fecha_hora)->format('d/m/Y') }} - 
                                                {{ $visita->institucion->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Institución y Contacto -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="institucion_id">Institución</label>
                                    <select class="form-control" id="institucion_id" name="institucion_id" required>
                                        <option value="">Seleccione una institución</option>
                                        @foreach($instituciones as $institucion)
                                            <option value="{{ $institucion->id }}">
                                                {{ $institucion->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="persona_contactada">Persona Contactada</label>
                                    <input type="text" class="form-control" id="persona_contactada" 
                                           name="persona_contactada" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="telefono">Teléfono</label>
                                    <input type="tel" class="form-control" id="telefono" name="telefono" required>
                                </div>
                            </div>
                        </div>

                        <!-- Motivo y Resumen -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="motivo_visita">Motivo de la Visita</label>
                                    <textarea class="form-control" id="motivo_visita" name="motivo_visita" 
                                              rows="4" required></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="resumen_seguimiento">Resumen de Seguimiento</label>
                                    <textarea class="form-control" id="resumen_seguimiento" name="resumen_seguimiento" 
                                              rows="4" required></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Evidencia y Estado -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="archivo_evidencia">Archivo de Evidencia</label>
                                    <input type="file" class="form-control" id="archivo_evidencia" 
                                           name="archivo_evidencia" accept=".jpg,.jpeg,.png,.pdf">
                                    <small class="form-text text-muted">
                                        Formatos permitidos: JPG, JPEG, PNG, PDF. Tamaño máximo: 2MB
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="estado_seguimiento">Estado de Seguimiento</label>
                                    <select class="form-control" id="estado_seguimiento" name="estado_seguimiento" required>
                                        <option value="1">Completado</option>
                                        <option value="0">Pendiente</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Observaciones -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="observaciones">Observaciones</label>
                                    <textarea class="form-control" id="observaciones" name="observaciones" 
                                              rows="3"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Guardar Reporte</button>
                                <a href="{{ route('reportes.visita.index') }}" class="btn btn-secondary">Cancelar</a>
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
document.getElementById('visita_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const institucionId = selectedOption.dataset.institucion;
    
    if (institucionId) {
        document.getElementById('institucion_id').value = institucionId;
    }
});
</script>
@endpush
