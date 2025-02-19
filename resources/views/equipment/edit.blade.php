<x-app-layout>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Editar Equipo') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('equipment.update', $equipment) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="line_id" class="form-label">{{ __('Línea') }}</label>
                            <select id="line_id" name="line_id" class="form-control @error('line_id') is-invalid @enderror" required>
                                <option value="">{{ __('Seleccione una línea') }}</option>
                                @foreach($lines as $line)
                                    <option value="{{ $line->id }}" {{ old('line_id', $equipment->line_id) == $line->id ? 'selected' : '' }}>
                                        {{ $line->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('line_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('Nombre del Equipo') }}</label>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" 
                                   name="name" value="{{ old('name', $equipment->name) }}" required autocomplete="name" autofocus>
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label">{{ __('Tipo de Equipo') }}</label>
                            <input id="type" type="text" class="form-control @error('type') is-invalid @enderror" 
                                   name="type" value="{{ old('type', $equipment->type) }}" required>
                            @error('type')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="serial_number" class="form-label">{{ __('Número de Serie') }}</label>
                            <input id="serial_number" type="text" class="form-control @error('serial_number') is-invalid @enderror" 
                                   name="serial_number" value="{{ old('serial_number', $equipment->serial_number) }}" required>
                            @error('serial_number')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">{{ __('Estado') }}</label>
                            <select id="status" name="status" class="form-control @error('status') is-invalid @enderror" required>
                                <option value="available" {{ old('status', $equipment->status) == 'available' ? 'selected' : '' }}>
                                    Disponible
                                </option>
                                <option value="in_use" {{ old('status', $equipment->status) == 'in_use' ? 'selected' : '' }}>
                                    En Uso
                                </option>
                                <option value="maintenance" {{ old('status', $equipment->status) == 'maintenance' ? 'selected' : '' }}>
                                    En Mantenimiento
                                </option>
                            </select>
                            @error('status')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-0">
                            <button type="submit" class="btn btn-primary">
                                {{ __('Actualizar Equipo') }}
                            </button>
                            <a href="{{ route('equipment.show', $equipment) }}" class="btn btn-secondary">
                                {{ __('Cancelar') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</x-app-layout>
