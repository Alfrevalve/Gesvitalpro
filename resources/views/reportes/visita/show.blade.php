@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Detalles del Reporte de Visita</h3>
                    <div class="card-tools">
                        <a href="{{ route('reportes.visita.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box">
                                <div class="info-box-content">
                                    <h5 class="info-box-text">Información General</h5>
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>Fecha de Visita:</th>
                                            <td>{{ \Carbon\Carbon::parse($reporte->fecha_visita)->format('d/m/Y') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Asesor:</th>
                                            <td>{{ $reporte->asesor->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Estado:</th>
                                            <td>
                                                <span class="badge badge-{{ $reporte->estado_seguimiento ? 'success' : 'warning' }}">
                                                    {{ $reporte->estado_seguimiento ? 'Completado' : 'Pendiente' }}
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-box">
                                <div class="info-box-content">
                                    <h5 class="info-box-text">Información de Contacto</h5>
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>Institución:</th>
                                            <td>{{ $reporte->institucion->nombre }}</td>
                                        </tr>
                                        <tr>
                                            <th>Persona Contactada:</th>
                                            <td>{{ $reporte->persona_contactada }}</td>
                                        </tr>
                                        <tr>
                                            <th>Teléfono:</th>
                                            <td>{{ $reporte->telefono }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="info-box">
                                <div class="info-box-content">
                                    <h5 class="info-box-text">Motivo de la Visita</h5>
                                    <div class="p-3 bg-light rounded">
                                        {{ $reporte->motivo_visita }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-box">
                                <div class="info-box-content">
                                    <h5 class="info-box-text">Resumen de Seguimiento</h5>
                                    <div class="p-3 bg-light rounded">
                                        {{ $reporte->resumen_seguimiento }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($reporte->observaciones)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="info-box">
                                <div class="info-box-content">
                                    <h5 class="info-box-text">Observaciones</h5>
                                    <div class="p-3 bg-light rounded">
                                        {{ $reporte->observaciones }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($reporte->archivo_evidencia)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="info-box">
                                <div class="info-box-content">
                                    <h5 class="info-box-text">Evidencia</h5>
                                    <div class="p-3 bg-light rounded">
                                        <a href="{{ Storage::url($reporte->archivo_evidencia) }}" 
                                           class="btn btn-info" target="_blank">
                                            <i class="fas fa-file-download"></i> 
                                            Descargar Archivo de Evidencia
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.info-box {
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    border-radius: 0.25rem;
    background-color: #fff;
    padding: 1.5rem;
    margin-bottom: 1rem;
}

.info-box-text {
    color: #1f2d3d;
    margin-bottom: 1rem;
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 0.5rem;
}

.table th {
    width: 40%;
    background-color: #f4f6f9;
}

.badge {
    font-size: 0.9em;
    padding: 0.5em 0.75em;
}
</style>
@endpush
