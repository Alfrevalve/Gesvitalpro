<!-- Estadísticas Principales -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="card stats-card bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stats-number">{{ $stats['total_lines'] }}</div>
                        <div class="stats-label text-white-50">Líneas Activas</div>
                    </div>
                    <i class="bi bi-diagram-3 fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card stats-card bg-success text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stats-number">{{ $stats['total_institutions'] }}</div>
                        <div class="stats-label text-white-50">Instituciones</div>
                    </div>
                    <i class="bi bi-building fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card stats-card bg-info text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stats-number">{{ $stats['total_doctors'] }}</div>
                        <div class="stats-label text-white-50">Médicos</div>
                    </div>
                    <i class="bi bi-person-vcard fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card stats-card bg-warning text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stats-number">{{ $stats['total_equipment'] }}</div>
                        <div class="stats-label text-white-50">Equipos</div>
                    </div>
                    <i class="bi bi-tools fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Usuarios Activos</h5>
            </div>
            <div class="card-body">
                <div class="stats-number text-center mb-2">{{ $stats['active_users'] }}</div>
                <div class="text-muted text-center">usuarios registrados</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Cirugías este Mes</h5>
            </div>
            <div class="card-body">
                <div class="stats-number text-center mb-2">{{ $stats['total_surgeries_month'] }}</div>
                <div class="text-muted text-center">cirugías realizadas</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Ingresos del Mes</h5>
            </div>
            <div class="card-body">
                <div class="stats-number text-center mb-2">${{ $stats['total_revenue_formatted'] }}</div>
                <div class="text-muted text-center">en cirugías completadas</div>
            </div>
        </div>
    </div>
</div>
