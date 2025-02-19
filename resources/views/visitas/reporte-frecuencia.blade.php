<x-app-layout>
    <div class="pagetitle">
        <h1>Reporte de Frecuencia de Visitas</h1>
    </div>

    <section class="section">
        <div class="row">
            <!-- Filtros -->
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Filtros</h5>
                        <form action="{{ route('visitas.reporte-frecuencia') }}" method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                                <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio"
                                    value="{{ request('fecha_inicio', $fechaInicio->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-4">
                                <label for="fecha_fin" class="form-label">Fecha Fin</label>
                                <input type="date" class="form-control" id="fecha_fin" name="fecha_fin"
                                    value="{{ request('fecha_fin', $fechaFin->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search me-1"></i> Filtrar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="col-12 mb-4">
                <div class="row">
                    <div class="col-xxl-3 col-md-6">
                        <div class="card info-card sales-card">
                            <div class="card-body">
                                <h5 class="card-title">Total de Visitas</h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-calendar-check"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6>{{ $estadisticas['total_visitas'] }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-3 col-md-6">
                        <div class="card info-card revenue-card">
                            <div class="card-body">
                                <h5 class="card-title">Promedio Diario</h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-bar-chart"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6>{{ number_format($estadisticas['promedio_diario'], 1) }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-3 col-md-6">
                        <div class="card info-card customers-card">
                            <div class="card-body">
                                <h5 class="card-title">Día con Más Visitas</h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-calendar-plus"></i>
                                    </div>
                                    <div class="ps-3">
                                        @if($estadisticas['dia_mas_visitas'])
                                            <h6>{{ \Carbon\Carbon::parse($estadisticas['dia_mas_visitas']->fecha)->format('d/m/Y') }}</h6>
                                            <span class="text-muted small pt-2">{{ $estadisticas['dia_mas_visitas']->total }} visitas</span>
                                        @else
                                            <h6>N/A</h6>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-3 col-md-6">
                        <div class="card info-card">
                            <div class="card-body">
                                <h5 class="card-title">Asesor más Activo</h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-person-check"></i>
                                    </div>
                                    <div class="ps-3">
                                        @if($estadisticas['asesor_mas_visitas'])
                                            <h6>{{ $estadisticas['asesor_mas_visitas']->asesor->name ?? 'N/A' }}</h6>
                                            <span class="text-muted small pt-2">{{ $estadisticas['asesor_mas_visitas']->total }} visitas</span>
                                        @else
                                            <h6>N/A</h6>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tablas de Datos -->
            <div class="col-12">
                <div class="row">
                    <!-- Visitas por Día -->
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Visitas por Día</h5>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Total de Visitas</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($visitasPorDia as $visita)
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::parse($visita->fecha)->format('d/m/Y') }}</td>
                                                    <td>{{ $visita->total }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="2" class="text-center">No hay datos disponibles</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Visitas por Asesor -->
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Visitas por Asesor</h5>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Asesor</th>
                                                <th>Total de Visitas</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($visitasPorAsesor as $asesor)
                                                <tr>
                                                    <td>{{ $asesor->asesor->name ?? 'N/A' }}</td>
                                                    <td>{{ $asesor->total }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="2" class="text-center">No hay datos disponibles</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        .info-card {
            padding-bottom: 10px;
        }
        .card-icon {
            width: 50px;
            height: 50px;
            font-size: 1.5rem;
        }
        .info-card h6 {
            font-size: 28px;
            color: #012970;
            font-weight: 700;
            margin: 0;
            padding: 0;
        }
        .card-title {
            padding: 20px 0 15px 0;
            font-size: 18px;
            font-weight: 500;
            color: #012970;
            font-family: "Poppins", sans-serif;
        }
        .sales-card .card-icon {
            color: #4154f1;
            background: #f6f6fe;
        }
        .revenue-card .card-icon {
            color: #2eca6a;
            background: #e0f8e9;
        }
        .customers-card .card-icon {
            color: #ff771d;
            background: #ffecdf;
        }
    </style>
</x-app-layout>
