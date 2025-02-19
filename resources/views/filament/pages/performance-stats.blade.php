<div class="space-y-6">
    {{-- Estadísticas de Consultas --}}
    <div class="rounded-lg bg-white p-4 shadow-sm">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Consultas SQL</h3>
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-gray-50 p-3 rounded">
                <p class="text-sm font-medium text-gray-500">Consultas Lentas</p>
                <p class="mt-1 text-3xl font-semibold text-gray-900">
                    {{ $stats['slow_queries'] ?? 0 }}
                </p>
            </div>
            <div class="bg-gray-50 p-3 rounded">
                <p class="text-sm font-medium text-gray-500">Tiempo Promedio</p>
                <p class="mt-1 text-3xl font-semibold text-gray-900">
                    {{ number_format(($stats['query_time'] ?? 0), 2) }}s
                </p>
            </div>
        </div>
    </div>

    {{-- Estadísticas de Memoria --}}
    <div class="rounded-lg bg-white p-4 shadow-sm">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Uso de Memoria</h3>
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-gray-50 p-3 rounded">
                <p class="text-sm font-medium text-gray-500">Uso Actual</p>
                <p class="mt-1 text-3xl font-semibold text-gray-900">
                    {{ number_format(($stats['memory_usage']['current'] ?? 0), 2) }} MB
                </p>
            </div>
            <div class="bg-gray-50 p-3 rounded">
                <p class="text-sm font-medium text-gray-500">Pico de Uso</p>
                <p class="mt-1 text-3xl font-semibold text-gray-900">
                    {{ number_format(($stats['memory_usage']['peak'] ?? 0), 2) }} MB
                </p>
            </div>
        </div>
    </div>

    {{-- Estadísticas de Caché --}}
    <div class="rounded-lg bg-white p-4 shadow-sm">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Rendimiento de Caché</h3>
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-gray-50 p-3 rounded">
                <p class="text-sm font-medium text-gray-500">Ratio de Aciertos</p>
                <p class="mt-1 text-3xl font-semibold text-gray-900">
                    {{ number_format(($stats['cache']['hit_ratio'] ?? 0) * 100, 1) }}%
                </p>
            </div>
            <div class="bg-gray-50 p-3 rounded">
                <p class="text-sm font-medium text-gray-500">Tamaño Total</p>
                <p class="mt-1 text-3xl font-semibold text-gray-900">
                    {{ number_format(($stats['cache']['size'] ?? 0), 2) }} MB
                </p>
            </div>
        </div>
    </div>

    {{-- Tamaños de Base de Datos --}}
    <div class="rounded-lg bg-white p-4 shadow-sm">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Base de Datos</h3>
        <div class="space-y-4">
            <div class="bg-gray-50 p-3 rounded">
                <p class="text-sm font-medium text-gray-500">Tamaño Total</p>
                <p class="mt-1 text-3xl font-semibold text-gray-900">
                    {{ number_format(($stats['database_size'] ?? 0), 2) }} MB
                </p>
            </div>

            @if(!empty($stats['table_sizes']))
                <div class="mt-4">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Tamaño por Tabla</h4>
                    <div class="space-y-2">
                        @foreach($stats['table_sizes'] as $table => $size)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">{{ $table }}</span>
                                <span class="text-gray-900 font-medium">
                                    {{ number_format($size, 2) }} MB
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Sugerencias de Índices --}}
    @if(!empty($stats['suggested_indexes']))
        <div class="rounded-lg bg-white p-4 shadow-sm">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Sugerencias de Optimización</h3>
            <div class="space-y-4">
                @foreach($stats['suggested_indexes'] as $table => $suggestions)
                    <div class="bg-gray-50 p-3 rounded">
                        <p class="text-sm font-medium text-gray-700">{{ $table }}</p>
                        <ul class="mt-2 space-y-2">
                            @foreach($suggestions as $suggestion)
                                <li class="text-sm text-gray-600">
                                    <span class="font-medium">{{ $suggestion['type'] }}:</span>
                                    {{ implode(', ', $suggestion['columns']) }}
                                    <br>
                                    <span class="text-xs text-gray-500">
                                        Razón: {{ $suggestion['reason'] }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
