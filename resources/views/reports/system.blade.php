<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte del Sistema - GesVitalPro</title>
    <style>
        :root {
            --primary-color: #4f46e5;
            --secondary-color: #6b7280;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --background-color: #f3f4f6;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.5;
            color: #1f2937;
            background-color: var(--background-color);
            margin: 0;
            padding: 2rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--background-color);
        }

        .header h1 {
            color: var(--primary-color);
            margin: 0;
            font-size: 2rem;
        }

        .header p {
            color: var(--secondary-color);
            margin: 0.5rem 0 0;
        }

        .section {
            margin-bottom: 2rem;
            padding: 1rem;
            background-color: #ffffff;
            border-radius: 0.375rem;
            border: 1px solid #e5e7eb;
        }

        .section h2 {
            color: var(--primary-color);
            margin-top: 0;
            font-size: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .metric-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .metric-card {
            padding: 1rem;
            background-color: #f9fafb;
            border-radius: 0.375rem;
            border: 1px solid #e5e7eb;
        }

        .metric-card h3 {
            margin: 0 0 0.5rem 0;
            font-size: 1rem;
            color: var(--secondary-color);
        }

        .metric-value {
            font-size: 1.5rem;
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
            background-color: #dcfce7;
            color: #166534;
        }

        .status-warning {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-error {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .chart-container {
            margin-top: 1rem;
            height: 300px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        th, td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        th {
            background-color: #f9fafb;
            font-weight: 600;
            color: var(--secondary-color);
        }

        tr:hover {
            background-color: #f9fafb;
        }

        .footer {
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 2px solid var(--background-color);
            text-align: center;
            color: var(--secondary-color);
            font-size: 0.875rem;
        }

        @media print {
            body {
                background-color: white;
                padding: 0;
            }

            .container {
                box-shadow: none;
                padding: 0;
            }

            .section {
                break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Reporte del Sistema</h1>
            <p>Generado el {{ \Carbon\Carbon::parse($data['generated_at'])->format('d/m/Y H:i:s') }}</p>
            <p>Período: {{ ucfirst($data['period']) }} | Tipo: {{ ucfirst($data['type']) }}</p>
        </div>

        @if(isset($data['system']))
        <div class="section">
            <h2>Información del Sistema</h2>
            <div class="metric-grid">
                <div class="metric-card">
                    <h3>Versión PHP</h3>
                    <div class="metric-value">{{ $data['system']['php_version'] }}</div>
                </div>
                <div class="metric-card">
                    <h3>Versión Laravel</h3>
                    <div class="metric-value">{{ $data['system']['laravel_version'] }}</div>
                </div>
                <div class="metric-card">
                    <h3>Ambiente</h3>
                    <div class="metric-value">{{ ucfirst($data['system']['environment']) }}</div>
                </div>
                <div class="metric-card">
                    <h3>Estado</h3>
                    <div class="status {{ $data['system']['maintenance_mode'] ? 'status-warning' : 'status-healthy' }}">
                        {{ $data['system']['maintenance_mode'] ? 'En Mantenimiento' : 'Operativo' }}
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(isset($data['health']))
        <div class="section">
            <h2>Estado de Salud</h2>
            <div class="metric-grid">
                <div class="metric-card">
                    <h3>Uso de Disco</h3>
                    <div class="metric-value">{{ $data['health']['disk_usage']['percentage'] }}%</div>
                    <p>{{ $data['health']['disk_usage']['used'] }} / {{ $data['health']['disk_usage']['total'] }}</p>
                </div>
                <div class="metric-card">
                    <h3>Tamaño de Base de Datos</h3>
                    <div class="metric-value">{{ $data['health']['database_size'] }}</div>
                </div>
                <div class="metric-card">
                    <h3>Estado de Cache</h3>
                    <div class="status {{ $data['health']['cache_status']['healthy'] ? 'status-healthy' : 'status-error' }}">
                        {{ $data['health']['cache_status']['message'] }}
                    </div>
                </div>
                <div class="metric-card">
                    <h3>Estado de Cola</h3>
                    <div class="status {{ $data['health']['queue_status']['healthy'] ? 'status-healthy' : 'status-error' }}">
                        {{ $data['health']['queue_status']['message'] }}
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(isset($data['performance']))
        <div class="section">
            <h2>Rendimiento</h2>
            <div class="metric-grid">
                <div class="metric-card">
                    <h3>Tiempo de Respuesta Promedio</h3>
                    <div class="metric-value">{{ $data['performance']['response_times']['average'] }}ms</div>
                </div>
                <div class="metric-card">
                    <h3>Uso de Memoria</h3>
                    <div class="metric-value">{{ $data['performance']['memory_usage']['current'] }}</div>
                    <p>Pico: {{ $data['performance']['memory_usage']['peak'] }}</p>
                </div>
                <div class="metric-card">
                    <h3>Consultas DB/seg</h3>
                    <div class="metric-value">{{ $data['performance']['database_queries']['per_second'] }}</div>
                </div>
                <div class="metric-card">
                    <h3>Ratio de Cache</h3>
                    <div class="metric-value">{{ $data['performance']['cache_hits']['ratio'] }}%</div>
                </div>
            </div>
        </div>
        @endif

        @if(isset($data['security']))
        <div class="section">
            <h2>Seguridad</h2>
            <table>
                <thead>
                    <tr>
                        <th>Métrica</th>
                        <th>Últimas 24h</th>
                        <th>Últimos 7d</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Intentos de Login Fallidos</td>
                        <td>{{ $data['security']['failed_logins']['last_24h'] }}</td>
                        <td>{{ $data['security']['failed_logins']['last_7d'] }}</td>
                        <td>
                            <span class="status {{ $data['security']['failed_logins']['status'] == 'normal' ? 'status-healthy' : 'status-warning' }}">
                                {{ ucfirst($data['security']['failed_logins']['status']) }}
                            </span>
                        </td>
                    </tr>
                    <!-- Más métricas de seguridad -->
                </tbody>
            </table>
        </div>
        @endif

        @if(isset($data['usage']))
        <div class="section">
            <h2>Uso del Sistema</h2>
            <div class="metric-grid">
                <div class="metric-card">
                    <h3>Usuarios Activos</h3>
                    <div class="metric-value">{{ $data['usage']['active_users']['count'] }}</div>
                    <p>↑ {{ $data['usage']['active_users']['growth'] }}% vs período anterior</p>
                </div>
                <!-- Más métricas de uso -->
            </div>
        </div>
        @endif

        <div class="footer">
            <p>GesVitalPro - Reporte generado automáticamente</p>
            <p>Para más información, contacte al administrador del sistema</p>
        </div>
    </div>
</body>
</html>
