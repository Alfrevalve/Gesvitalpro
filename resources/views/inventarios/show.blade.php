@extends('layouts.app')

@section('title', 'Detalles del Item')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-box me-2"></i>Detalles del Item
        </h1>
        <div>
            <a href="{{ route('inventarios.edit', $item->id) }}" class="btn btn-warning me-2">
                <i class="fas fa-edit me-2"></i>Editar
            </a>
            <a href="{{ route('inventarios.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Información Principal -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Información del Item
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="text-muted">Nombre</label>
                                <p class="h5">{{ $item->nombre }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="text-muted">Código</label>
                                <p class="h5">{{ $item->codigo }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="text-muted">Categoría</label>
                                <p class="h5">
                                    <span class="badge bg-info">{{ ucfirst($item->categoria) }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="text-muted">Ubicación</label>
                                <p class="h5">{{ $item->ubicacion ?: 'No especificada' }}</p>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="detail-item">
                                <label class="text-muted">Descripción</label>
                                <p class="h5">{{ $item->descripcion ?: 'Sin descripción' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Historial de Movimientos -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>Historial de Movimientos
                    </h5>
                </div>
                <div class="card-body">
                    @if($item->movimientos && $item->movimientos->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Tipo</th>
                                        <th>Cantidad</th>
                                        <th>Usuario</th>
                                        <th>Motivo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($item->movimientos as $movimiento)
                                        <tr>
                                            <td>{{ $movimiento->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                @if($movimiento->tipo == 'entrada')
                                                    <span class="badge bg-success">Entrada</span>
                                                @else
                                                    <span class="badge bg-danger">Salida</span>
                                                @endif
                                            </td>
                                            <td>{{ $movimiento->cantidad }}</td>
                                            <td>{{ $movimiento->usuario->name }}</td>
                                            <td>{{ $movimiento->motivo }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No hay movimientos registrados</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Imagen y Estado -->
            <div class="card mb-4">
                <div class="card-body text-center">
                    <div class="avatar-xl mx-auto mb-3">
                        @if($item->imagen)
                            <img src="{{ asset('storage/' . $item->imagen) }}" 
                                 class="img-thumbnail" 
                                 alt="Imagen de {{ $item->nombre }}">
                        @else
                            <i class="fas fa-box fa-5x text-secondary"></i>
                        @endif
                    </div>
                    <h4 class="mb-1">{{ $item->nombre }}</h4>
                    <p class="text-muted mb-3">Código: {{ $item->codigo }}</p>
                    <div class="mb-3">
                        @if($item->stock > $item->stock_minimo)
                            <span class="badge bg-success">
                                <i class="fas fa-check me-1"></i>Disponible
                            </span>
                        @elseif($item->stock > 0)
                            <span class="badge bg-warning">
                                <i class="fas fa-exclamation-triangle me-1"></i>Bajo Stock
                            </span>
                        @else
                            <span class="badge bg-danger">
                                <i class="fas fa-times me-1"></i>Agotado
                            </span>
                        @endif
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <div class="row text-center">
                        <div class="col">
                            <div class="text-muted small">Stock Actual</div>
                            <strong>{{ $item->stock }}</strong>
                        </div>
                        <div class="col">
                            <div class="text-muted small">Stock Mínimo</div>
                            <strong>{{ $item->stock_minimo }}</strong>
                        </div>
                        <div class="col">
                            <div class="text-muted small">Precio</div>
                            <strong>${{ number_format($item->precio, 2) }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Estadísticas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="stat-item mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Valor en Stock</span>
                            <span class="badge bg-primary">
                                ${{ number_format($item->stock * $item->precio, 2) }}
                            </span>
                        </div>
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar" role="progressbar" style="width: 100%"></div>
                        </div>
                    </div>
                    <div class="stat-item mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Movimientos Totales</span>
                            <span class="badge bg-info">
                                {{ $item->movimientos->count() }}
                            </span>
                        </div>
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar bg-info" role="progressbar" style="width: 100%"></div>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Nivel de Stock</span>
                            <span class="badge {{ $item->stock > $item->stock_minimo ? 'bg-success' : 'bg-warning' }}">
                                {{ ($item->stock_minimo > 0) ? 
                                   number_format(($item->stock / $item->stock_minimo) * 100, 0) : 0 }}%
                            </span>
                        </div>
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar {{ $item->stock > $item->stock_minimo ? 'bg-success' : 'bg-warning' }}" 
                                 role="progressbar" 
                                 style="width: {{ ($item->stock_minimo > 0) ? 
                                                min(($item->stock / $item->stock_minimo) * 100, 100) : 0 }}%">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notas -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-sticky-note me-2"></i>Notas Adicionales
                    </h5>
                </div>
                <div class="card-body">
                    @if($item->notas)
                        {{ $item->notas }}
                    @else
                        <div class="text-muted text-center">
                            <i class="fas fa-info-circle me-2"></i>Sin notas adicionales
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
    .avatar-xl {
        width: 120px;
        height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .avatar-xl img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .detail-item {
        margin-bottom: 1.5rem;
    }

    .detail-item:last-child {
        margin-bottom: 0;
    }

    .detail-item label {
        display: block;
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
    }

    .detail-item p {
        margin-bottom: 0;
    }

    .stat-item {
        padding: 0.5rem 0;
    }

    .progress {
        background-color: #e9ecef;
    }

    .badge {
        padding: 0.5em 1em;
        font-weight: 500;
    }

    .table td {
        vertical-align: middle;
    }
</style>
@endpush
