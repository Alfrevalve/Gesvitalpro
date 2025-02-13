<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Estado del Sistema - GesVitalPro</title>
    <style>
        :root {
            --primary-color: #4F46E5;
            --success-color: #10B981;
            --warning-color: #F59E0B;
            --danger-color: #EF4444;
            --gray-color: #6B7280;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.5;
            margin: 0;
            padding: 2rem;
            color: #1F2937;
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #E5E7EB;
        }

        .header h1 {
            color: var(--primary-color);
            margin: 0;
            font-size: 2rem;
        }

        .header p {
            color: var(--gray-color);
            margin: 0.5rem 0 0;
        }

        .section {
            margin-bottom: 2rem;
            padding: 1rem;
            background-color: #FFFFFF;
            border-radius: 0.5rem;
            border: 1px solid #E5E7EB;
            page-break-inside: avoid;
        }

        .section h2 {
            color: var(--primary-color);
            margin-top: 0;
            font-size: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #E5E7EB;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .metric-card {
            padding: 1rem;
            background-color: #F9FAFB;
            border-radius: 0.375rem;
            border: 1px solid #E5E7EB;
        }

        .metric-card h3 {
            margin: 0 0 0.5rem 0;
            font-size: 0.875rem;
            color: var(--gray-color);
        }

        .metric-value {
            font-size: 1.25rem;
            font-weight: bold;
            color: var(--primary-color);
        }

        .status {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-healthy { 
            background-color: #DCFCE7;
            color: #166534;
        }

        .status-warning {
            background-color: #FEF3C7;
            color: #92400E;
        }

        .status-error {
            background-color: #FEE2E2;
            color: #991B1B;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            font-size: 0.875rem;
        }

        th, td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #E5E7EB;
        }

        th {
            background-color: #F9FAFB;
            font-weight: 600;
            color: var(--gray-color);
        }

        .chart {
            margin-top: 1rem;
            height: 200px;
            background-color: #F9FAFB;
            border: 1px solid #E5E7EB;
            border-radius: 0.375rem;
        }

        .footer {
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 2px solid #E5E7EB;
            text-align: center;
            color: var(--gray-color);
            font-size: 0.875rem;
        }

        @media print {
            body {
                padding: 0;
            }

            .section {
                border: none;
                padding: 0;
                margin-bottom: 1rem;
            }

            .chart {
                break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Estado del Sistema</h1>
        <p>Generado el {{ $generated_at->format('d/m/Y H:i:s') }}</p>
        <p>Período: {{ ucfirst($period) }} | Tipo: {{ ucfirst($type) }}</p>
    </div>

    <!-- Información del Sistema -->
    <div class="section">
        <h2>Información del Sistema</h2>
        <div class="grid">
            <div class="metric-card">
                <h3>Versión PHP</h3>
                <div class="metric-value">{{ $system_info['php_version'] }}</div>
            </div>
            <div class="metric-card">
                <h3>Versión Laravel</h3>
                <div class="metric-value">{{ $system_info['laravel_version'] }}</div>
            </div>
            <div class="metric-card">
                <h3>Ambiente</h3>
                <div class="metric-value">{{ ucfirst($system_info['environment']) }}</div>
            </div>
            <div class="metric-card">
                <h3>Estado</h3>
                <div class="status {{ $system_info['maintenance_mode'] ? 'status-warning' : 'status-healthy' }}">
                    {{ $system_info['maintenance_mode'] ? 'Mantenimiento' : 'Operativo' }}
                </div>
            </div>
        </div>
    </div>

    <!-- Estado de Salud -->
    <div class="section">
        <h2>Estado de Salud</h2>
        <div class="grid">
            @foreach($health_check['results'] as $check => $result)
                <div class="metric-card">
                    <h3>{{ ucfirst($check) }}</h3>
                    <div class="status {{ $result['status'] === 'healthy' ? 'status-healthy' : ($result['status'] === 'warning' ? 'status-warning' : 'status-error') }}">
                        {{ ucfirst($result['status']) }}
                    </div>
                    @if(isset($result['message']))
                        <p class="text-sm text-gray-500">{{ $result['message'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- Alertas -->
    <div class="section">
        <h2>Alertas</h2>
        <div class="grid">
            <div class="metric-card">
                <h3>Total Alertas</h3>
                <div class="metric-value">{{ $alerts['total'] }}</div>
            </div>
            <div class="metric-card">
                <h3>Críticas</h3>
                <div class="metric-value">{{ $alerts['critical'] }}</div>
            </div>
            <div class="metric-card">
                <h3>Advertencias</h3>
                <div class="metric-value">{{ $alerts['warning'] }}</div>
            </div>
            <div class="metric-card">
                <h3>Resueltas</h3>
                <div class="metric-value">{{ $alerts['resolved'] }}</div>
            </div>
        </div>

        @if(count($alerts['recent']) > 0)
            <table>
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Mensaje</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($alerts['recent'] as $alert)
                        <tr>
                            <td>{{ ucfirst($alert->type) }}</td>
                            <td>{{ $alert->message }}</td>
                            <td>
                                <span class="status {{ $alert->resolved ? 'status-healthy' : ($alert->type === 'critical' ? 'status-error' : 'status-warning') }}">
                                    {{ $alert->resolved ? 'Resuelto' : 'Activo' }}
                                </span>
                            </td>
                            <td>{{ $alert->created_at->format('d/m/Y H:i:s') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <!-- Rendimiento -->
    <div class="section">
        <h2>Rendimiento</h2>
        <div class="grid">
            <div class="metric-card">
                <h3>Tiempo de Respuesta Promedio</h3>
                <div class="metric-value">{{ $performance['response_times']['average'] ?? 'N/A' }} ms</div>
            </div>
            <div class="metric-card">
                <h3>Uso de Memoria</h3>
                <div class="metric-value">{{ $performance['memory_usage']['current'] ?? 'N/A' }}</div>
            </div>
            <div class="metric-card">
                <h3>Consultas DB/seg</h3>
                <div class="metric-value">{{ $performance['database']['queries_per_second'] ?? 'N/A' }}</div>
            </div>
            <div class="metric-card">
                <h3>Ratio de Cache</h3>
                <div class="metric-value">{{ $performance['cache']['hit_ratio'] ?? 'N/A' }}%</div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>GesVitalPro - Reporte generado automáticamente</p>
        <p>Para más información, contacte al administrador del sistema</p>
    </div>
</body>
</html>
