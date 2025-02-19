<x-app-layout>
<div class="container">
    <h1>Detalles del Equipo</h1>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Información del Equipo</h5>
            <p><strong>ID:</strong> {{ $equipment->id }}</p>
            <p><strong>Nombre:</strong> {{ $equipment->name }}</p>
            <p><strong>Tipo:</strong> {{ $equipment->type }}</p>
            <p><strong>Línea:</strong> {{ $equipment->line->name }}</p>
            <p><strong>Número de Serie:</strong> {{ $equipment->serial_number }}</p>
            <p><strong>Estado:</strong> {{ $equipment->status }}</p>
            <p><strong>Último Mantenimiento:</strong> {{ $equipment->last_maintenance ? $equipment->last_maintenance->format('d/m/Y') : 'No registrado' }}</p>
            <p><strong>Próximo Mantenimiento:</strong> {{ $equipment->next_maintenance ? $equipment->next_maintenance->format('d/m/Y') : 'No programado' }}</p>
        </div>
    </div>

    <a href="{{ route('equipment.edit', $equipment) }}" class="btn btn-warning">Editar Equipo</a>
    <form action="{{ route('equipment.update-status', $equipment) }}" method="POST" class="d-inline">
        @csrf
        @method('PATCH')
        <button type="submit" class="btn btn-info">Actualizar Estado</button>
    </form>
    <a href="{{ route('equipment.index') }}" class="btn btn-secondary">Volver a la Lista</a>
</div>
</x-app-layout>
