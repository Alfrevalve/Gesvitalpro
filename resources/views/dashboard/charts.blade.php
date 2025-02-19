<!-- Gráficos Mensuales -->
<div class="row mb-4">
    <div class="col-12">
        <h2 class="h4 mb-3">Estadísticas Mensuales</h2>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Cirugías por Mes</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="surgeryChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Ingresos por Mes</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Distribución de Cirugías -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Distribución de Cirugías</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="surgeryDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Estado de Equipos</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h6 class="card-title">Disponibles</h6>
                                <div class="stats-number">{{ $equipment_stats['status']['available'] }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h6 class="card-title">En Uso</h6>
                                <div class="stats-number">{{ $equipment_stats['status']['in_use'] }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h6 class="card-title">En Mantenimiento</h6>
                                <div class="stats-number">{{ $equipment_stats['status']['maintenance'] }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <h6 class="card-title">Próximo Mantenimiento</h6>
                                <div class="stats-number">{{ $equipment_stats['maintenance_due'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
