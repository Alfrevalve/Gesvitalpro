<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado del Sistema - GesVitalPro</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center">
                    <h1 class="text-3xl font-bold text-gray-900">
                        Estado del Sistema
                    </h1>
                    <div class="flex space-x-4">
                        <button onclick="refreshStatus()" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Actualizar Estado
                        </button>
                        <button onclick="showMaintenanceModal()" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Mantenimiento
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <!-- Estado General -->
            <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            @if($healthCheck['status'] === 'healthy')
                                <div class="h-12 w-12 rounded-full bg-green-100 flex items-center justify-center">
                                    <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                            @elseif($healthCheck['status'] === 'warning')
                                <div class="h-12 w-12 rounded-full bg-yellow-100 flex items-center justify-center">
                                    <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                </div>
                            @else
                                <div class="h-12 w-12 rounded-full bg-red-100 flex items-center justify-center">
                                    <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="ml-5">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Estado General del Sistema
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Última actualización: {{ \Carbon\Carbon::parse($healthCheck['timestamp'])->format('d/m/Y H:i:s') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estadísticas del Sistema -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($healthCheck['checks'] as $checkName => $check)
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <div class="flex items-center">
                                <div class="flex-1">
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        {{ ucfirst($checkName) }}
                                    </dt>
                                    <dd class="mt-1 text-3xl font-semibold text-gray-900">
                                        <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium
                                            {{ $check['status'] === 'healthy' ? 'bg-green-100 text-green-800' : 
                                               ($check['status'] === 'warning' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ strtoupper($check['status']) }}
                                        </span>
                                    </dd>
                                    @if(isset($check['message']))
                                        <p class="mt-2 text-sm text-gray-500">
                                            {{ $check['message'] }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Gráficos y Estadísticas -->
            <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Uso de Recursos -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Uso de Recursos</h3>
                        <div class="mt-4">
                            <canvas id="resourcesChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas de la Aplicación -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Estadísticas de la Aplicación</h3>
                        <dl class="mt-5 grid grid-cols-1 gap-5">
                            @foreach($stats['application_stats'] as $key => $value)
                                <div class="relative bg-gray-50 pt-5 px-4 sm:pt-6 sm:px-6 shadow rounded-lg overflow-hidden">
                                    <dt>
                                        <p class="text-sm font-medium text-gray-500 truncate">{{ ucwords(str_replace('_', ' ', $key)) }}</p>
                                    </dt>
                                    <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $value }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal de Mantenimiento -->
    <div id="maintenanceModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 hidden">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white rounded-lg px-4 pt-5 pb-4 overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
                <div>
                    <div class="mt-3 text-center sm:mt-5">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Tareas de Mantenimiento
                        </h3>
                        <div class="mt-5">
                            <div class="space-y-4">
                                <button onclick="runMaintenance('clear-cache')" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Limpiar Caché
                                </button>
                                <button onclick="runMaintenance('optimize')" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Optimizar Sistema
                                </button>
                                <button onclick="runMaintenance('migrate')" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Ejecutar Migraciones
                                </button>
                                <button onclick="runMaintenance('storage-link')" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Crear Enlaces Simbólicos
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-6">
                    <button onclick="hideMaintenanceModal()" class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Inicializar gráficos
        function initCharts() {
            const ctx = document.getElementById('resourcesChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Memoria Usada', 'Memoria Libre', 'Disco Usado', 'Disco Libre'],
                    datasets: [{
                        data: [
                            {{ str_replace(['GB', 'MB', 'KB'], '', $stats['system_stats']['memory_usage']['used']) }},
                            {{ str_replace(['GB', 'MB', 'KB'], '', $stats['system_stats']['memory_usage']['free']) }},
                            {{ str_replace(['GB', 'MB', 'KB'], '', $stats['system_stats']['disk_usage']['used']) }},
                            {{ str_replace(['GB', 'MB', 'KB'], '', $stats['system_stats']['disk_usage']['free']) }}
                        ],
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(54, 162, 235, 0.5)',
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(255, 99, 132, 0.5)'
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 99, 132, 1)',
                            'rgba(255, 99, 132, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        }
                    }
                }
            });
        }

        // Funciones para el modal de mantenimiento
        function showMaintenanceModal() {
            document.getElementById('maintenanceModal').classList.remove('hidden');
        }

        function hideMaintenanceModal() {
            document.getElementById('maintenanceModal').classList.add('hidden');
        }

        // Función para actualizar el estado
        async function refreshStatus() {
            try {
                const response = await fetch('/admin/system-health/check', {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                window.location.reload();
            } catch (error) {
                console.error('Error al actualizar el estado:', error);
            }
        }

        // Función para ejecutar tareas de mantenimiento
        async function runMaintenance(task) {
            try {
                const response = await fetch('/admin/system-health/maintenance', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ task })
                });
                const data = await response.json();
                alert(data.message);
                window.location.reload();
            } catch (error) {
                console.error('Error al ejecutar la tarea de mantenimiento:', error);
            }
        }

        // Inicializar la página
        document.addEventListener('DOMContentLoaded', function() {
            initCharts();
        });
    </script>
</body>
</html>
