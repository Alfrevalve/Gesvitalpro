@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Detalles del Reporte Post Cirugía</h3>
                    <div class="card-tools">
                        <a href="{{ route('reportes.post-cirugia.index') }}" class="btn btn-secondary">
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
                                            <th>Fecha de Cirugía:</th>
                                            <td>{{ \Carbon\Carbon::parse($reporte->fecha_cirugia)->format('d/m/Y') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Hora Programada:</th>
                                            <td>{{ \Carbon\Carbon::parse($reporte->hora_programada)->format('H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Hora de Inicio:</th>
                                            <td>{{ \Carbon\Carbon::parse($reporte->hora_inicio)->format('H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Hora de Fin:</th>
                                            <td>{{ \Carbon\Carbon::parse($reporte->hora_fin)->format('H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Hoja de Consumo:</th>
                                            <td>
                                                <span class="badge badge-{{ $reporte->hoja_consumo ? 'success' : 'danger' }}">
                                                    {{ $reporte->hoja_consumo ? 'Sí' : 'No' }}
                                                </span>
                                                @if($reporte->hoja_consumo && $reporte->hoja_consumo_archivo)
                                                    <a href="{{ Storage::url($reporte->hoja_consumo_archivo) }}" 
                                                       class="btn btn-sm btn-info ml-2" target="_blank">
                                                        <i class="fas fa-file"></i> Ver Archivo
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-box">
                                <div class="info-box-content">
                                    <h5 class="info-box-text">Personal e Institución</h5>
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>Instrumentista:</th>
                                            <td>{{ $reporte->instrumentista->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Médico:</th>
                                            <td>{{ $reporte->medico->nombre }}</td>
                                        </tr>
                                        <tr>
                                            <th>Paciente:</th>
                                            <td>{{ $reporte->paciente->nombre }}</td>
                                        </tr>
                                        <tr>
                                            <th>Institución:</th>
                                            <td>{{ $reporte->institucion->nombre }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="info-box">
                                <div class="info-box-content">
                                    <h5 class="info-box-text">Sistemas Utilizados</h5>
                                    <div class="sistemas-container">
                                        @if(is_array($reporte->sistemas) && count($reporte->sistemas) > 0)
                                            <div class="row">
                                                @foreach($reporte->sistemas as $sistemaId)
                                                    <div class="col-md-3">
                                                        <div class="sistema-item">
                                                            <i class="fas fa-check-circle text-success"></i>
                                                            {{ \App\Models\Sistema::find($sistemaId)->nombre }}
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-muted">No se registraron sistemas utilizados</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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

.sistema-item {
    padding: 0.5rem;
    margin-bottom: 0.5rem;
    background-color: #f8f9fa;
    border-radius: 0.25rem;
}

.sistema-item i {
    margin-right: 0.5rem;
}

.table th {
    width: 40%;
    background-color: #f4f6f9;
}
</style>
@endpush
