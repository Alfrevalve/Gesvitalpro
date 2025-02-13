@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Reportes Post Cirugía</h3>
                    <div class="card-tools">
                        <a href="{{ route('reportes.post-cirugia.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Nuevo Reporte
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Fecha Cirugía</th>
                                    <th>Hora Programada</th>
                                    <th>Hora Inicio</th>
                                    <th>Hora Fin</th>
                                    <th>Instrumentista</th>
                                    <th>Médico</th>
                                    <th>Paciente</th>
                                    <th>Institución</th>
                                    <th>Hoja de Consumo</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reportes as $reporte)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($reporte->fecha_cirugia)->format('d/m/Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($reporte->hora_programada)->format('H:i') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($reporte->hora_inicio)->format('H:i') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($reporte->hora_fin)->format('H:i') }}</td>
                                        <td>{{ $reporte->instrumentista->name }}</td>
                                        <td>{{ $reporte->medico->nombre }}</td>
                                        <td>{{ $reporte->paciente->nombre }}</td>
                                        <td>{{ $reporte->institucion->nombre }}</td>
                                        <td>
                                            <span class="badge badge-{{ $reporte->hoja_consumo ? 'success' : 'danger' }}">
                                                {{ $reporte->hoja_consumo ? 'Sí' : 'No' }}
                                            </span>
                                            @if($reporte->hoja_consumo && $reporte->hoja_consumo_archivo)
                                                <a href="{{ Storage::url($reporte->hoja_consumo_archivo) }}" 
                                                   class="btn btn-sm btn-info ml-1" target="_blank">
                                                    <i class="fas fa-file"></i>
                                                </a>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('reportes.post-cirugia.show', $reporte) }}" 
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> Ver
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No hay reportes registrados</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $reportes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.card-tools {
    float: right;
}

.table th {
    background-color: #f4f6f9;
}
</style>
@endpush
