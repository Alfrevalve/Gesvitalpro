@component('mail::message')
# Alerta de Rendimiento del Sistema

{{ $message }}

## Detalles del Reporte

@component('mail::panel')
* Consultas Lentas: {{ $report['slow_queries'] ?? 0 }}
* Uso de Memoria: {{ round($report['memory_usage']['current'] ?? 0, 2) }}MB
* Memoria Pico: {{ round($report['memory_usage']['peak'] ?? 0, 2) }}MB
* Ratio de Cache: {{ round(($report['cache']['hit_ratio'] ?? 0) * 100, 2) }}%
* Tamaño de BD: {{ round($report['database_size'] ?? 0, 2) }}MB
@endcomponent

@if(!empty($report['suggested_indexes']))
## Índices Sugeridos

@component('mail::panel')
@foreach($report['suggested_indexes'] as $suggestion)
* Columnas: {{ implode(', ', $suggestion['columns']) }}
  Razón: {{ $suggestion['reason'] }}
@endforeach
@endcomponent
@endif

## Acciones Recomendadas

@component('mail::panel')
1. Revisar las consultas lentas y optimizar según sea necesario
2. Monitorear el uso de memoria si continúa aumentando
3. Verificar la efectividad del sistema de caché
4. Considerar la implementación de los índices sugeridos
@endcomponent

Timestamp: {{ $timestamp }}

@component('mail::button', ['url' => config('app.url').'/admin/performance'])
Ver Panel de Rendimiento
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent
