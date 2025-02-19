<div class="grid grid-cols-3 gap-4 p-4">
    <div class="p-4 bg-green-50 rounded-lg text-center">
        <p class="text-2xl font-bold text-green-600">{{ $stats['available'] }}</p>
        <p class="text-sm text-green-800">Equipos Disponibles</p>
    </div>
    <div class="p-4 bg-blue-50 rounded-lg text-center">
        <p class="text-2xl font-bold text-blue-600">{{ $stats['in_use'] }}</p>
        <p class="text-sm text-blue-800">Equipos en Uso</p>
    </div>
    <div class="p-4 bg-yellow-50 rounded-lg text-center">
        <p class="text-2xl font-bold text-yellow-600">{{ $stats['maintenance'] }}</p>
        <p class="text-sm text-yellow-800">Equipos en Mantenimiento</p>
    </div>
</div>
