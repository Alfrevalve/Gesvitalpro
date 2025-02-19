@extends('layouts.app')

@section('title', 'Tablero Kanban - Almacén')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Tablero Kanban - Almacén</h1>
        <div>
            <a href="{{ route('storage.index') }}" class="btn btn-secondary">
                <i class="bi bi-list"></i> Ver Lista
            </a>
            <a href="{{ route('storage.report') }}" class="btn btn-info ms-2">
                <i class="bi bi-graph-up"></i> Ver Reportes
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Columna Pendientes -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-clock"></i> Pendientes
                        <span class="badge bg-dark ms-2">{{ $pendingRequests->count() }}</span>
                    </h5>
                </div>
                <div class="card-body kanban-column">
                    @forelse($pendingRequests as $request)
                        <div class="card mb-3 request-card">
                            <div class="card-body">
                                <h6 class="card-title">Solicitud #{{ $request->id }}</h6>
                                <p class="card-text">
                                    @if($request->surgery)
                                        {{ Str::limit($request->surgery->description, 100) }}
                                    @else
                                        <span class="text-muted">Sin descripción</span>
                                    @endif
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        {{ $request->created_at->format('d/m/Y H:i') }}
                                    </small>
                                    <a href="{{ route('storage.show', $request) }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> Ver
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <p class="text-muted mb-0">No hay solicitudes pendientes</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Columna En Proceso -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-gear"></i> En Proceso
                        <span class="badge bg-light text-dark ms-2">{{ $inProgressRequests->count() }}</span>
                    </h5>
                </div>
                <div class="card-body kanban-column">
                    @forelse($inProgressRequests as $request)
                        <div class="card mb-3 request-card">
                            <div class="card-body">
                                <h6 class="card-title">Solicitud #{{ $request->id }}</h6>
                                <p class="card-text">
                                    @if($request->surgery)
                                        {{ Str::limit($request->surgery->description, 100) }}
                                    @else
                                        <span class="text-muted">Sin descripción</span>
                                    @endif
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        {{ $request->created_at->format('d/m/Y H:i') }}
                                    </small>
                                    <a href="{{ route('storage.show', $request) }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> Ver
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <p class="text-muted mb-0">No hay solicitudes en proceso</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Columna Completados -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-check-circle"></i> Completados
                        <span class="badge bg-light text-dark ms-2">{{ $completedRequests->count() }}</span>
                    </h5>
                </div>
                <div class="card-body kanban-column">
                    @forelse($completedRequests as $request)
                        <div class="card mb-3 request-card">
                            <div class="card-body">
                                <h6 class="card-title">Solicitud #{{ $request->id }}</h6>
                                <p class="card-text">
                                    @if($request->surgery)
                                        {{ Str::limit($request->surgery->description, 100) }}
                                    @else
                                        <span class="text-muted">Sin descripción</span>
                                    @endif
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        {{ $request->created_at->format('d/m/Y H:i') }}
                                    </small>
                                    <a href="{{ route('storage.show', $request) }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> Ver
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <p class="text-muted mb-0">No hay solicitudes completadas</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.kanban-column {
    min-height: calc(100vh - 250px);
    max-height: calc(100vh - 250px);
    overflow-y: auto;
}
.request-card {
    transition: all 0.3s ease;
}
.request-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}
.card-text {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>
@endpush
