<x-app-layout>
<div class="container">
    <h1>Actualizar Estado de Equipo</h1>
    <form action="{{ route('equipment.update-status', $equipment) }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="status" class="form-label">Estado</label>
            <select class="form-control" id="status" name="status" required>
                <option value="available" {{ $equipment->status == 'available' ? 'selected' : '' }}>Disponible</option>
                <option value="in_use" {{ $equipment->status == 'in_use' ? 'selected' : '' }}>En Uso</option>
                <option value="maintenance" {{ $equipment->status == 'maintenance' ? 'selected' : '' }}>En Mantenimiento</option>
            </select>
        </div>
        <button type="submit" class="btn btn-info">Actualizar Estado</button>
    </form>
    <a href="{{ route('equipment.show', $equipment) }}" class="btn btn-secondary mt-2">Volver a Detalles</a>
</div>
</x-app-layout>
