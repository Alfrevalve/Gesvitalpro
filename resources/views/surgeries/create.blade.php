@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3">
                <a href="{{ route('surgeries.index') }}" class="text-decoration-none">
                    <i class="bi bi-arrow-left"></i>
                </a>
                Nueva Cirugía
            </h1>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('surgeries.store') }}" method="POST">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="line_id" class="form-label">Línea</label>
                        <select name="line_id" id="line_id" class="form-select @error('line_id') is-invalid @enderror" required>
                            <option value="">Seleccione una línea</option>
                            @foreach($lines as $line)
                                <option value="{{ $line->id }}" {{ old('line_id') == $line->id ? 'selected' : '' }}>
                                    {{ $line->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('line_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror" required>{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="notes" class="form-label">Notas Adicionales</label>
                        <textarea name="notes" id="notes" rows="2" class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Equipamiento</label>
                        <div class="card">
                            <div class="card-body" style="max-height: 200px; overflow-y: auto;">
                                @foreach($equipment as $item)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="equipment_ids[]"
                                            value="{{ $item->id }}" id="equipment_{{ $item->id }}"
                                            {{ in_array($item->id, old('equipment_ids', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="equipment_{{ $item->id }}">
                                            {{ $item->name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @error('equipment_ids')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Personal</label>
                        <div class="card">
                            <div class="card-body" style="max-height: 200px; overflow-y: auto;">
                                @foreach($instrumentistas as $staff)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="staff_ids[]"
                                            value="{{ $staff->id }}" id="staff_{{ $staff->id }}"
                                            {{ in_array($staff->id, old('staff_ids', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="staff_{{ $staff->id }}">
                                            {{ $staff->name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @error('staff_ids')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Guardar Cirugía
                        </button>
                        <a href="{{ route('surgeries.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
