@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Reportes de Visita</h3>
                    <div class="card-tools">
                        <a href="{{ route('reportes.visita.create') }}" class="btn btn-primary">
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
                                    <th>Fecha</th>
                                    <th>Asesor</th>
                                    <th>Institución</th>
                                    <th>Persona Contactada</th>
                                    <th>Motivo</th>
                                    <th>Estado</th>
                                    <th>Evidencia</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reportes as $reporte)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($reporte->fecha_visita)->format('d/m/Y') }}</td>
                                        <td>{{ $reporte->asesor->name }}</td>
                                        <td>{{ $reporte->institucion->nombre }}</td>
                                        <td>{{ $reporte->persona_contactada }}</td>
                                        <td>{{ Str::limit($reporte->motivo_visita, 50) }}</td>
                                        <td>
                                            <span class="badge badge-{{ $reporte->estado_seguimiento ? 'success' : 'warning' }}">
                                                {{ $reporte->estado_seguimiento ? 'Completado' : 'Pendiente' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if($reporte->archivo_evidencia)
                                                <a href="{{ Storage::url($reporte->archivo_evidencia) }}" 
                                                   class="btn btn-sm btn-info" target="_blank">
                                                    <i class="fas fa-file"></i>
                                                </a>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('reportes.visita.show', $reporte) }}" 
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('reportes.visita.edit', $reporte) }}" 
                                               class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('reportes.visita.destroy', $reporte) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" 
                                                        onclick="return confirm('¿Está seguro de eliminar este reporte?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
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

.badge {
    font-size: 0.9em;
    padding: 0.5em 0.75em;
}
</style>
@endpush
