@extends('layouts.app')

@section('title', 'Almacén')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Solicitudes de Material</h1>
                <div>
                    <a href="{{ route('storage.kanban') }}" class="btn btn-primary">
                        <i class="bi bi-kanban"></i> Ver Kanban
                    </a>
                    <a href="{{ route('storage.report') }}" class="btn btn-info ms-2">
                        <i class="bi bi-graph-up"></i> Ver Reportes
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    @if($requests->isEmpty())
                        <div class="text-center py-5">
                            <i class="bi bi-inbox display-1 text-muted"></i>
                            <p class="mt-3">No hay solicitudes pendientes</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Cirugía</th>
                                        <th>Estado</th>
                                        <th>Fecha Solicitud</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($requests as $request)
                                        <tr>
                                            <td>{{ $request->id }}</td>
                                            <td>
                                                @if($request->surgery)
                                                    {{ $request->surgery->description }}
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                @switch($request->status)
                                                    @case('pending')
                                                        <span class="badge bg-warning">Pendiente</span>
                                                        @break
                                                    @case('in_progress')
                                                        <span class="badge bg-info">En Proceso</span>
                                                        @break
                                                    @case('completed')
                                                        <span class="badge bg-success">Completado</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-secondary">{{ $request->status }}</span>
                                                @endswitch
                                            </td>
                                            <td>{{ $request->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <a href="{{ route('storage.show', $request) }}"
                                                   class="btn btn-sm btn-primary">
                                                    <i class="bi bi-eye"></i> Ver
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $requests->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}
.table > :not(caption) > * > * {
    padding: 0.75rem;
}
.badge {
    font-weight: 500;
}
</style>
@endpush
