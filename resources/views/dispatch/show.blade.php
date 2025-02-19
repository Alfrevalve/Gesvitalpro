@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3">
                <a href="{{ route('dispatch.index') }}" class="text-decoration-none">
                    <i class="bi bi-arrow-left"></i>
                </a>
                Entrega #{{ $surgeryRequest->id }}
            </h1>
        </div>
        <div class="col text-end">
            @if($surgeryRequest->dispatch_status != 'delivered')
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#confirmDeliveryModal">
                    <i class="bi bi-check-circle"></i> Confirmar Entrega
                </button>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- Información de la Entrega -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Información de Entrega</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Estado</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-{{ $surgeryRequest->dispatch_status == 'pending' ? 'warning' : ($surgeryRequest->dispatch_status == 'in_transit' ? 'info' : 'success') }}">
                                {{ ucfirst($surgeryRequest->dispatch_status) }}
                            </span>
                        </dd>

                        <dt class="col-sm-4">Preparado</dt>
                        <dd class="col-sm-8">{{ $surgeryRequest->preparation->created_at->format('d/m/Y H:i') }}</dd>

                        @if($surgeryRequest->dispatch_started_at)
                            <dt class="col-sm-4">En Tránsito</dt>
                            <dd class="col-sm-8">{{ $surgeryRequest->dispatch_started_at->format('d/m/Y H:i') }}</dd>
                        @endif

                        @if($surgeryRequest->delivered_at)
                            <dt class="col-sm-4">Entregado</dt>
                            <dd class="col-sm-8">{{ $surgeryRequest->delivered_at->format('d/m/Y H:i') }}</dd>
                        @endif

                        @if($surgeryRequest->delivery)
                            <dt class="col-sm-4">Receptor</dt>
                            <dd class="col-sm-8">{{ $surgeryRequest->delivery->recipient_name }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Información de la Cirugía -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Cirugía Asociada</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">ID</dt>
                        <dd class="col-sm-8">
                            <a href="{{ route('surgeries.show', $surgeryRequest->surgery) }}" class="text-decoration-none">
                                #{{ $surgeryRequest->surgery->id }}
                            </a>
                        </dd>

                        <dt class="col-sm-4">Descripción</dt>
                        <dd class="col-sm-8">{{ $surgeryRequest->surgery->description }}</dd>

                        <dt class="col-sm-4">Fecha</dt>
                        <dd class="col-sm-8">{{ $surgeryRequest->surgery->surgery_date?->format('d/m/Y') ?? 'No definida' }}</dd>

                        <dt class="col-sm-4">Estado</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-{{ $surgeryRequest->surgery->status_color }}">
                                {{ $surgeryRequest->surgery->status_text }}
                            </span>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Lista de Items y Detalles de Entrega -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Items a Entregar</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Cantidad</th>
                                    <th>Estado</th>
                                    <th>Notas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($surgeryRequest->items as $item)
                                    <tr>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>
                                            <span class="badge bg-{{ $item->status == 'pending' ? 'warning' : ($item->status == 'in_progress' ? 'info' : 'success') }}">
                                                {{ ucfirst($item->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $item->notes ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if($surgeryRequest->delivery)
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Detalles de Entrega</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Firma del Receptor</h6>
                                <img src="{{ $surgeryRequest->delivery->recipient_signature }}" alt="Firma" class="img-fluid border rounded mb-3">
                            </div>
                            @if($surgeryRequest->delivery->delivery_photo)
                                <div class="col-md-6">
                                    <h6>Foto de Entrega</h6>
                                    <img src="{{ Storage::url($surgeryRequest->delivery->delivery_photo) }}" alt="Foto de entrega" class="img-fluid border rounded mb-3">
                                </div>
                            @endif
                        </div>
                        @if($surgeryRequest->delivery->notes)
                            <div class="mt-3">
                                <h6>Notas de Entrega</h6>
                                <p class="mb-0">{{ $surgeryRequest->delivery->notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@include('dispatch.partials.delivery-modals')
@endsection
