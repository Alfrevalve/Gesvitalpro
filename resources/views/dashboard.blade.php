<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Quirúrgico</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="container">
        <h1>Dashboard Quirúrgico</h1>
        <h2>Métricas Clave</h2>
        <div class="metrics">
            <div class="metric">
                <h3>Cirugías Realizadas por Línea</h3>
                <ul>
                    @foreach ($metrics['surgeries_by_line'] as $line => $count)
                        <li>{{ $line }}: {{ $count }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="metric">
                <h3>Cirugías Realizadas por Instrumentista</h3>
                <ul>
                    @foreach ($metrics['surgeries_by_instrumentist'] as $instrumentist => $count)
                        <li>{{ $instrumentist }}: {{ $count }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="metric">
                <h3>Cirugías Mensuales</h3>
                <ul>
                    @foreach ($metrics['monthly_surgeries'] as $month => $count)
                        <li>{{ $month }}: {{ $count }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="metric">
                <h3>Cirugías Reprogramadas</h3>
                <p>{{ $metrics['rescheduled_surgeries'] }}</p>
            </div>
            <div class="metric">
                <h3>Cirugías Completadas</h3>
                <p>{{ $metrics['completed_surgeries'] }}</p>
            </div>
            <div class="metric">
                <h3>Cirugías Suspendidas</h3>
                <p>{{ $metrics['cancelled_surgeries'] }}</p>
            </div>
            <div class="metric">
                <h3>Frecuencia de Visitas</h3>
                <ul>
                    @foreach ($metrics['visit_frequency'] as $medico => $count)
                        <li>{{ $medico }}: {{ $count }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="metric">
                <h3>Cobertura de Instituciones</h3>
                <ul>
                    @foreach ($metrics['institution_coverage'] as $institucion)
                        <li>{{ $institucion['nombre'] }}: {{ $institucion['visitas'] }} ({{ $institucion['cobertura'] }})</li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="actions">
            <h2>Generar Reportes</h2>
            <a href="{{ route('report.pdf') }}" class="btn">Generar Reporte PDF</a>
            <a href="{{ route('report.excel') }}" class="btn">Generar Reporte Excel</a>
        </div>
    </div>
</body>
</html>
