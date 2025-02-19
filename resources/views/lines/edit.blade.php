<x-app-layout>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="text-primary">
                    <i class="fas fa-edit"></i> Editar Línea
                </h2>
                <a href="{{ route('lines.show', $line) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="{{ route('lines.update', $line) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $line->name) }}" 
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label">Descripción</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="3">{{ old('description', $line->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if(Auth::user()->isAdmin())
    <div class="row mt-4">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Zona de Peligro</h5>
                </div>
                <div class="card-body">
                    <h6>Eliminar Línea</h6>
                    <p class="text-muted">Esta acción no se puede deshacer. Se eliminarán todos los datos asociados a esta línea.</p>
                    <form action="{{ route('lines.destroy', $line) }}" method="POST" onsubmit="return confirm('¿Está seguro de eliminar esta línea? Esta acción no se puede deshacer.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>Eliminar Línea
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
</x-app-layout>

<style>
<style>
    .card {
        border: none;
        border-radius: 1rem;
    }
    .card.border-danger {
        border: 1px solid #dc3545 !important;
    }
    .btn-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }
    .btn-primary:hover {
        background-color: #0a2347;
        border-color: #0a2347;
    }
</style>
</style>
