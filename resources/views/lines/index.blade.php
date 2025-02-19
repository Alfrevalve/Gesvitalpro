@extends('layouts.sneat')

@section('title', 'Líneas Quirúrgicas')

@section('content')
<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Líneas Quirúrgicas</h5>
                @if(Auth::user()->isAdmin() || Auth::user()->isGerente())
                <a href="{{ route('lines.create') }}" class="btn btn-primary">
                    <i class="bx bx-plus me-1"></i> Nueva Línea
                </a>
                @endif
            </div>
        </div>
    </div>

    @forelse($lines as $line)
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ $line->name }}</h5>
                @if(Auth::user()->isAdmin() || Auth::user()->isGerente())
                <a href="{{ route('lines.edit', $line) }}" class="btn btn-icon btn-primary btn-sm">
                    <i class="bx bx-edit"></i>
                </a>
                @endif
            </div>
            <div class="card-body">
                <p class="card-text text-muted mb-4">{{ $line->description ?? 'Sin descripción' }}</p>

                <div class="row g-2 g-sm-3 mb-4">
                    <div class="col-4">
                        <div class="card card-statistics h-100 mb-0">
                            <div class="card-body p-2 p-sm-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar flex-shrink-0 me-2 me-sm-3">
                                        <span class="avatar-initial rounded bg-label-primary">
                                            <i class="bx bx-wrench"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fs-7 fs-sm-6">Equipos</h6>
                                        <small>{{ $line->equipment_count ?? 0 }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="card card-statistics h-100 mb-0">
                            <div class="card-body p-2 p-sm-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar flex-shrink-0 me-2 me-sm-3">
                                        <span class="avatar-initial rounded bg-label-success">
                                            <i class="bx bx-plus-medical"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fs-7 fs-sm-6">Cirugías</h6>
                                        <small>{{ $line->surgeries_count ?? 0 }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="card card-statistics h-100 mb-0">
                            <div class="card-body p-2 p-sm-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar flex-shrink-0 me-2 me-sm-3">
                                        <span class="avatar-initial rounded bg-label-warning">
                                            <i class="bx bx-group"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fs-7 fs-sm-6">Personal</h6>
                                        <small>{{ $line->staff_count ?? 0 }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('lines.show', $line) }}" class="btn btn-primary w-100 w-sm-auto">
                        <i class="bx bx-show me-1"></i> Ver Detalles
                    </a>
                    @if(Auth::user()->canManageLine($line))
                    <a href="{{ route('lines.dashboard', $line) }}" class="btn btn-info w-100 w-sm-auto">
                        <i class="bx bx-line-chart me-1"></i> Dashboard
                    </a>
                    <a href="{{ route('lines.schedule', $line) }}" class="btn btn-success w-100 w-sm-auto">
                        <i class="bx bx-calendar me-1"></i> Agenda
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="alert alert-primary d-flex align-items-center" role="alert">
                    <i class="bx bx-info-circle me-2 fs-4"></i>
                    <div>
                        <h6 class="alert-heading mb-1">No hay líneas registradas</h6>
                        <span>Actualmente no hay líneas quirúrgicas en el sistema.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforelse
</div>

@if($lines->hasPages())
<div class="d-flex justify-content-end mt-3">
    {{ $lines->links() }}
</div>
@endif
@endsection
