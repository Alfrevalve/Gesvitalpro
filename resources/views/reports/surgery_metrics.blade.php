<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Métricas Quirúrgicas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h1 {
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Reporte de Métricas Quirúrgicas</h1>
    <h2>Período: {{ $metrics['period']['start'] }} - {{ $metrics['period']['end'] }}</h2>

    <h3>Métricas de Eficiencia</h3>
    <table>
        <tr>
            <th>Métrica</th>
            <th>Valor</th>
        </tr>
        <tr>
            <td>Duración Promedio del Proceso</td>
            <td>{{ $metrics['efficiency']['avg_process_duration'] }} minutos</td>
        </tr>
        <tr>
            <td>Tasa de Finalización a Tiempo</td>
            <td>{{ $metrics['efficiency']['on_time_completion_rate'] }}%</td>
        </tr>
        <tr>
            <td>Tasa de Retrasos</td>
            <td>{{ $metrics['efficiency']['delay_rate'] }}%</td>
        </tr>
        <tr>
            <td>Tasa de Cancelaciones</td>
            <td>{{ $metrics['efficiency']['cancellation_rate'] }}%</td>
        </tr>
    </table>

    <h3>Métricas de Calidad</h3>
    <table>
        <tr>
            <th>Métrica</th>
            <th>Valor</th>
        </tr>
        <tr>
            <td>Precisión en Preparación de Material</td>
            <td>{{ $metrics['quality']['material_preparation_accuracy'] }}%</td>
        </tr>
        <tr>
            <td>Tasa de Éxito de Cirugías</td>
            <td>{{ $metrics['quality']['surgery_success_rate'] }}%</td>
        </tr>
        <tr>
            <td>Condición de Devolución de Equipos</td>
            <td>{{ $metrics['quality']['equipment_return_condition'] }}%</td>
        </tr>
        <tr>
            <td>Tasa de Completitud de Documentación</td>
            <td>{{ $metrics['quality']['documentation_completion_rate'] }}%</td>
        </tr>
    </table>

    <h3>Métricas de Productividad</h3>
    <table>
        <tr>
            <th>Métrica</th>
            <th>Valor</th>
        </tr>
        <tr>
            <td>Procesos por Período</td>
            <td>{{ $metrics['productivity']['processes_per_period'] }}</td>
        </tr>
        <tr>
            <td>Cirugías por Personal</td>
            <td>{{ $metrics['productivity']['surgeries_per_staff'] }}</td>
        </tr>
        <tr>
            <td>Tasa de Utilización de Equipos</td>
            <td>{{ $metrics['productivity']['equipment_utilization_rate'] }}%</td>
        </tr>
        <tr>
            <td>Tasa de Rotación de Almacén</td>
            <td>{{ $metrics['productivity']['storage_turnover_rate'] }}</td>
        </tr>
    </table>

    <h3>Métricas de Servicio</h3>
    <table>
        <tr>
            <th>Métrica</th>
            <th>Valor</th>
        </tr>
        <tr>
            <td>Conversión de Visitas a Cirugías</td>
            <td>{{ $metrics['service']['visit_to_surgery_conversion'] }}%</td>
        </tr>
        <tr>
            <td>Tasa de Satisfacción del Cliente</td>
            <td>{{ $metrics['service']['customer_satisfaction_rate'] }}%</td>
        </tr>
        <tr>
            <td>Tiempo de Resolución de Quejas</td>
            <td>{{ $metrics['service']['complaint_resolution_time'] }} horas</td>
        </tr>
        <tr>
            <td>Precisión en Programación</td>
            <td>{{ $metrics['service']['scheduling_accuracy'] }}%</td>
        </tr>
    </table>
</body>
</html>
