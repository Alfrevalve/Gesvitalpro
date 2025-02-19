<x-app-layout>
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Reporte de Cobertura - Médicos</h1>
    </div>

    <!-- Filtros de Fecha -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form action="{{ route('medicos.reporte-cobertura') }}" method="GET" class="flex gap-4">
            <div class="flex-1">
                <label for="fecha_inicio" class="block text-sm font-medium text-gray-700">Fecha Inicio</label>
                <input type="date" 
                       name="fecha_inicio" 
                       id="fecha_inicio"
                       value="{{ $fechaInicio->format('Y-m-d') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div class="flex-1">
                <label for="fecha_fin" class="block text-sm font-medium text-gray-700">Fecha Fin</label>
                <input type="date" 
                       name="fecha_fin" 
                       id="fecha_fin"
                       value="{{ $fechaFin->format('Y-m-d') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- Resumen de Cobertura -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Total Médicos</h3>
            <p class="text-3xl font-bold text-blue-600">{{ $estadisticas['total_medicos'] }}</p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Médicos Visitados</h3>
            <p class="text-3xl font-bold text-green-600">{{ $estadisticas['medicos_visitados'] }}</p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Porcentaje de Cobertura</h3>
            <p class="text-3xl font-bold text-indigo-600">
                {{ number_format($estadisticas['porcentaje_cobertura'], 1) }}%
            </p>
        </div>
    </div>

    <!-- Tabla de Cobertura por Médico -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Detalle por Médico</h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Médico
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Especialidad
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Institución
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Total Visitas
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Estado
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Cobertura
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($medicos as $medico)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $medico->nombre }}
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ $medico->email }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                {{ $medico->especialidad }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $medico->institucion->nombre }}</div>
                            <div class="text-sm text-gray-500">{{ $medico->institucion->tipo }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $medico->visitas_count }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($medico->visitas_count > 0)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Visitado
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Pendiente
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                @php
                                    $maxVisitas = $medicos->max('visitas_count');
                                    $porcentaje = $maxVisitas > 0 ? ($medico->visitas_count / $maxVisitas) * 100 : 0;
                                @endphp
                                <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $porcentaje }}%"></div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Aquí se puede agregar código JavaScript para gráficos o funcionalidades adicionales
</script>
@endpush
</x-app-layout>
