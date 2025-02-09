@extends('layouts.app')

@section('title', 'Historial de Movimientos')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-history me-2"></i>Historial de Movimientos
        </h1>
        <a href="{{ route('inventarios.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('inventarios.movimientos') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <div class="form-floating">
                        <input type="text" 
                               class="form-control" 
                               id="item" 
                               name="item" 
                               value="{{ request('item') }}" 
                               placeholder="Buscar por item">
                        <label for="item">Buscar por Item</label>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-floating">
                        <select class="form-select" id="tipo" name="tipo">
                            <option value="">Todos los tipos</option>
                            <option value="entrada" {{ request('tipo') == 'entrada' ? 'selected' : '' }}>Entrada</option>
                            <option value="salida" {{ request('tipo') == 'salida' ? 'selected' : '' }}>Salida</option>
                        </select>
                        <label for="tipo">Tipo</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-floating">
                        <input type="date" 
                               class="form-control" 
                               id="fecha_inicio" 
                               name="fecha_inicio" 
                               value="{{ request('fecha_inicio') }}">
                        <label for="fecha_inicio">Fecha Inicio</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-floating">
                        <input type="date" 
                               class="form-control" 
                               id="fecha_fin" 
                               name="fecha_fin" 
                               value="{{ request('fecha_fin') }}">
                        <label for="fecha_fin">Fecha Fin</label>
                    </div>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Resumen -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-left-primary h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Movimientos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exchange-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-left-success h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Entradas
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['entradas'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-arrow-up fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-left-danger h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Salidas
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['salidas'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-arrow-down fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-left-info h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Items Afectados
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['items'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-boxes fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Movimientos -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Item</th>
                            <th>Tipo</th>
                            <th>Cantidad</th>
                            <th>Stock Final</th>
                            <th>Usuario</th>
                            <th>Motivo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movimientos as $movimiento)
                            <tr>
                                <td>{{ $movimiento->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($movimiento->item->imagen)
                                            <img src="{{ asset('storage/' . $movimiento->item->imagen) }}" 
                                                 class="rounded me-2" 
                                                 alt="{{ $movimiento->item->nombre }}"
                                                 width="40">
                                        @else
                                            <i class="fas fa-box fa-2x text-secondary me-2"></i>
                                        @endif
                                        <div>
                                            <div class="fw-bold">{{ $movimiento->item->nombre }}</div>
                                            <small class="text-muted">{{ $movimiento->item->codigo }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($movimiento->tipo == 'entrada')
                                        <span class="badge bg-success">
                                            <i class="fas fa-arrow-up me-1"></i>Entrada
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="fas fa-arrow-down me-1"></i>Salida
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-bold {{ $movimiento->tipo == 'entrada' ? 'text-success' : 'text-danger' }}">
                                        {{ $movimiento->tipo == 'entrada' ? '+' : '-' }}{{ $movimiento->cantidad }}
                                    </span>
                                </td>
                                <td>{{ $movimiento->stock_final }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-2">
                                            @if($movimiento->usuario->avatar)
                                                <img src="{{ asset('storage/' . $movimiento->usuario->avatar) }}" 
                                                     class="rounded-circle" 
                                                     alt="{{ $movimiento->usuario->name }}"
                                                     width="32">
                                            @else
                                                <i class="fas fa-user-circle fa-2x text-secondary"></i>
                                            @endif
                                        </div>
                                        {{ $movimiento->usuario->name }}
                                    </div>
                                </td>
                                <td>
                                    <span class="text-muted">{{ $movimiento->motivo }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p>No hay movimientos registrados</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div>
                    Mostrando {{ $movimientos->firstItem() ?? 0 }} - {{ $movimientos->lastItem() ?? 0 }} de {{ $movimientos->total() }} movimientos
                </div>
                {{ $movimientos->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .border-left-primary {
        border-left: 0.25rem solid #4e73df !important;
    }
    .border-left-success {
        border-left: 0.25rem solid #1cc88a !important;
    }
    .border-left-danger {
        border-left: 0.25rem solid #e74a3b !important;
    }
    .border-left-info {
        border-left: 0.25rem solid #36b9cc !important;
    }
    .table td {
        vertical-align: middle;
    }
    .badge {
        font-size: 0.85em;
        padding: 0.5em 1em;
    }
    .avatar-sm {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .avatar-sm img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
</style>
@endpush

@push('scripts')
<script>
    // Validación de fechas
    document.getElementById('fecha_fin').addEventListener('change', function() {
        var fechaInicio = document.getElementById('fecha_inicio').value;
        if(fechaInicio && this.value < fechaInicio) {
            alert('La fecha final no puede ser menor que la fecha inicial');
            this.value = fechaInicio;
        }
    });

    document.getElementById('fecha_inicio').addEventListener('change', function() {
        var fechaFin = document.getElementById('fecha_fin').value;
        if(fechaFin && this.value > fechaFin) {
            alert('La fecha inicial no puede ser mayor que la fecha final');
            this.value = fechaFin;
        }
    });
</script>
@endpush
