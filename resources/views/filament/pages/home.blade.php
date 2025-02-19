<x-filament::page>
    <x-filament::widgets
        :widgets="$this->getHeaderWidgets()"
        :columns="3"
    />

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
        <!-- Upcoming Surgeries Card -->
        <div class="p-6 bg-white rounded-lg shadow">
            <h2 class="text-lg font-medium text-gray-900 mb-4">
                Próximas Cirugías
            </h2>
            <div class="space-y-4">
                @php
                    $upcomingSurgeries = \App\Models\Surgery::query()
                        ->where('status', \App\Models\Surgery::STATUS_PENDING)
                        ->where('surgery_date', '>=', now())
                        ->where('surgery_date', '<=', now()->addDays(7))
                        ->orderBy('surgery_date')
                        ->limit(5)
                        ->get();
                @endphp

                @forelse($upcomingSurgeries as $surgery)
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-900">{{ $surgery->patient_name }}</p>
                            <p class="text-sm text-gray-500">{{ $surgery->surgery_type }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900">
                                {{ $surgery->surgery_date->format('d/m/Y') }}
                            </p>
                            <p class="text-sm text-gray-500">
                                {{ $surgery->institucion->name }}
                            </p>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-4">No hay cirugías programadas para los próximos 7 días</p>
                @endforelse
            </div>
        </div>

        <!-- Equipment Status Card -->
        <div class="p-6 bg-white rounded-lg shadow">
            <h2 class="text-lg font-medium text-gray-900 mb-4">
                Estado de Equipos
            </h2>
            <div class="space-y-4">
                @php
                    $equipmentStats = [
                        'available' => \App\Models\Equipment::where('status', 'available')->count(),
                        'in_use' => \App\Models\Equipment::where('status', 'in_use')->count(),
                        'maintenance' => \App\Models\Equipment::where('status', 'maintenance')->count(),
                    ];
                @endphp

                <div class="grid grid-cols-3 gap-4">
                    <div class="p-4 bg-green-50 rounded-lg text-center">
                        <p class="text-2xl font-bold text-green-600">{{ $equipmentStats['available'] }}</p>
                        <p class="text-sm text-green-800">Disponibles</p>
                    </div>
                    <div class="p-4 bg-blue-50 rounded-lg text-center">
                        <p class="text-2xl font-bold text-blue-600">{{ $equipmentStats['in_use'] }}</p>
                        <p class="text-sm text-blue-800">En Uso</p>
                    </div>
                    <div class="p-4 bg-yellow-50 rounded-lg text-center">
                        <p class="text-2xl font-bold text-yellow-600">{{ $equipmentStats['maintenance'] }}</p>
                        <p class="text-sm text-yellow-800">Mantenimiento</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament::page>
