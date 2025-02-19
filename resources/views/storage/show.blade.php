@extends('layouts.app')

@section('title', 'Detalles de Solicitud')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Solicitud #{{ $surgeryRequest->id }}</h1>
                <div>
                    <a href="{{ route('storage.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Detalles de la Solicitud -->
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Detalles de la Solicitud</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Estado:</strong>
                                        @switch($surgeryRequest->status)
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
                                                <span class="badge bg-secondary">{{ $surgeryRequest->status }}</span>
                                        @endswitch
                                    </p>
                                    <p><strong>Fecha de Solicitud:</strong> {{ $surgeryRequest->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Última Actualización:</strong> {{ $surgeryRequest->updated_at->format('d/m/Y H:i') }}</p>
                                    @if($surgeryRequest->preparation)
                                        <p><strong>Preparado por:</strong> {{ $surgeryRequest->preparation->preparedBy->name ?? 'N/A' }}</p>
                                    @endif
                                </div>
                            </div>

                            @if($surgeryRequest->surgery)
                                <hr>
                                <h6>Detalles de la Cirugía</h6>
                                <p><strong>Descripción:</strong> {{ $surgeryRequest->surgery->description }}</p>
                                <p><strong>Fecha Programada:</strong>
                                    {{ $surgeryRequest->surgery->surgery_date ? $surgeryRequest->surgery->surgery_date->format('d/m/Y H:i') : 'No programada' }}
                                </p>
                            @endif

                            @if($surgeryRequest->notes)
                                <hr>
                                <h6>Notas</h6>
                                <p>{{ $surgeryRequest->notes }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Lista de Items -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Items Solicitados</h5>
                        </div>
                        <div class="card-body">
                            @if($surgeryRequest->items->isEmpty())
                                <p class="text-muted">No hay items registrados</p>
                            @else
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Item</th>
                                                <th>Cantidad</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($surgeryRequest->items as $item)
                                                <tr>
                                                    <td>{{ $item->name }}</td>
                                                    <td>{{ $item->quantity }}</td>
                                                    <td>
                                                        @if($item->prepared)
                                                            <span class="badge bg-success">Preparado</span>
                                                        @else
                                                            <span class="badge bg-warning">Pendiente</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Panel de Acciones -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Acciones</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('storage.update-status', $surgeryRequest) }}" method="POST">
                                @csrf
                                @method('PATCH')

                                <div class="mb-3">
                                    <label for="status" class="form-label">Cambiar Estado</label>
                                    <select name="status" id="status" class="form-select" required>
                                        <option value="pending" {{ $surgeryRequest->status === 'pending' ? 'selected' : '' }}>Pendiente</option>
                                        <option value="in_progress" {{ $surgeryRequest->status === 'in_progress' ? 'selected' : '' }}>En Proceso</option>
                                        <option value="completed" {{ $surgeryRequest->status === 'completed' ? 'selected' : '' }}>Completado</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="notes" class="form-label">Notas</label>
                                    <textarea name="notes" id="notes" class="form-control" rows="3">{{ $surgeryRequest->notes }}</textarea>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    Actualizar Estado
                                </button>
                            </form>
                        </div>
                    </div>
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
    margin-bottom: 1rem;
}
.badge {
    font-weight: 500;
}
</style>
@endpush
