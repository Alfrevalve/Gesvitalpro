@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3">Despacho - Tablero Kanban</h1>
        </div>
        <div class="col text-end">
            <a href="{{ route('dispatch.index') }}" class="btn btn-outline-primary me-2">
                <i class="bi bi-list"></i> Ver Lista
            </a>
            <a href="{{ route('dispatch.report') }}" class="btn btn-outline-secondary">
                <i class="bi bi-file-earmark-text"></i> Ver Reportes
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Columna Pendientes -->
        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-clock"></i> Pendientes de Entrega
                        <span class="badge bg-dark float-end">{{ $pendingDeliveries->count() }}</span>
                    </h5>
                </div>
                <div class="card-body" style="height: calc(100vh - 200px); overflow-y: auto;">
                    @foreach($pendingDeliveries as $delivery)
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="card-title">
                                    Solicitud #{{ $delivery->id }}
                                    <span class="float-end text-muted">{{ $delivery->preparation->created_at->format('d/m/Y') }}</span>
                                </h6>
                                <p class="card-text">{{ Str::limit($delivery->surgery->description, 100) }}</p>
                                <p class="card-text">
                                    <small class="text-muted">
                                        <i class="bi bi-building"></i> {{ $delivery->surgery->line->name }}
                                    </small>
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-info">{{ $delivery->items->count() }} items</span>
                                    <div class="btn-group">
                                        <a href="{{ route('dispatch.show', $delivery) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> Ver
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#confirmDeliveryModal{{ $delivery->id }}">
                                            <i class="bi bi-check-circle"></i> Entregar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Columna En Tránsito -->
        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-truck"></i> En Tránsito
                        <span class="badge bg-dark float-end">{{ $inTransitDeliveries->count() }}</span>
                    </h5>
                </div>
                <div class="card-body" style="height: calc(100vh - 200px); overflow-y: auto;">
                    @foreach($inTransitDeliveries as $delivery)
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="card-title">
                                    Solicitud #{{ $delivery->id }}
                                    <span class="float-end text-muted">{{ $delivery->dispatch_started_at->format('d/m/Y H:i') }}</span>
                                </h6>
                                <p class="card-text">{{ Str::limit($delivery->surgery->description, 100) }}</p>
                                <p class="card-text">
                                    <small class="text-muted">
                                        <i class="bi bi-building"></i> {{ $delivery->surgery->line->name }}
                                    </small>
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-info">{{ $delivery->items->count() }} items</span>
                                    <div class="btn-group">
                                        <a href="{{ route('dispatch.show', $delivery) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> Ver
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#confirmDeliveryModal{{ $delivery->id }}">
                                            <i class="bi bi-check-circle"></i> Confirmar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Columna Entregados -->
        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-check-circle"></i> Entregados
                        <span class="badge bg-dark float-end">{{ $deliveredRequests->count() }}</span>
                    </h5>
                </div>
                <div class="card-body" style="height: calc(100vh - 200px); overflow-y: auto;">
                    @foreach($deliveredRequests as $delivery)
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="card-title">
                                    Solicitud #{{ $delivery->id }}
                                    <span class="float-end text-muted">{{ $delivery->delivered_at->format('d/m/Y H:i') }}</span>
                                </h6>
                                <p class="card-text">{{ Str::limit($delivery->surgery->description, 100) }}</p>
                                <p class="card-text">
                                    <small class="text-muted">
                                        <i class="bi bi-building"></i> {{ $delivery->surgery->line->name }}<br>
                                        <i class="bi bi-person"></i> Recibido por: {{ $delivery->delivery->recipient_name }}
                                    </small>
                                </p>
                                <div class="text-end">
                                    <a href="{{ route('dispatch.show', $delivery) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> Ver Detalles
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .card-body::-webkit-scrollbar {
        width: 6px;
    }
    .card-body::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    .card-body::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 3px;
    }
    .card-body::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
</style>
@endpush

@include('dispatch.partials.delivery-modals')
@endsection
