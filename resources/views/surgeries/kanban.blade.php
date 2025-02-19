@extends('layouts.app')

@section('title', 'Tablero Kanban de Cirugías')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col d-flex justify-content-between align-items-center">
            <h2>
                <i class="bi bi-kanban me-2"></i>
                Tablero Kanban de Cirugías
            </h2>
            <a href="{{ route('surgeries.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nueva Cirugía
            </a>
        </div>
    </div>

    <div class="row g-4">
        @foreach($columns as $status => $surgeries)
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card h-100 border-{{ $columnColors[$status] }}">
                    <div class="card-header bg-{{ $columnColors[$status] }} text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ $columnTitles[$status] }}</h5>
                        <span class="badge bg-white text-{{ $columnColors[$status] }}">{{ $surgeries->count() }}</span>
                    </div>
                    <div class="card-body p-2" style="height: calc(100vh - 200px); overflow-y: auto;">
                        @foreach($surgeries as $surgery)
                            <div class="card mb-2 surgery-card border-{{ $columnColors[$status] }}">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="card-title mb-0 text-truncate">
                                            {{ $surgery->description }}
                                        </h6>
                                        <span class="ms-2">
                                            @if($surgery->surgery_date)
                                                <i class="bi bi-calendar-event text-muted" title="Fecha de Cirugía"></i>
                                                <small>{{ $surgery->surgery_date->format('d/m/Y') }}</small>
                                            @endif
                                        </span>
                                    </div>

                                    @if($surgery->institucion)
                                        <p class="card-text small mb-1">
                                            <i class="bi bi-hospital text-muted"></i>
                                            <span class="text-truncate">{{ $surgery->institucion->nombre }}</span>
                                        </p>
                                    @endif

                                    @if($surgery->medico)
                                        <p class="card-text small mb-1">
                                            <i class="bi bi-person-badge text-muted"></i>
                                            <span class="text-truncate">{{ $surgery->medico->nombre }}</span>
                                        </p>
                                    @endif

                                    @if($surgery->line)
                                        <p class="card-text small mb-1">
                                            <i class="bi bi-tools text-muted"></i>
                                            <span class="text-truncate">{{ $surgery->line->name }}</span>
                                        </p>
                                    @endif

                                    <div class="mt-2 d-flex gap-1">
                                        <a href="{{ route('surgeries.show', $surgery) }}"
                                           class="btn btn-sm btn-outline-secondary"
                                           title="Ver detalles">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        @if($status !== 'completed' && $status !== 'cancelled')
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-{{ $columnColors[$status] }} dropdown-toggle"
                                                        type="button"
                                                        data-bs-toggle="dropdown"
                                                        title="Cambiar estado">
                                                    <i class="bi bi-arrow-left-right"></i>
                                                </button>
                                                <form action="{{ route('surgeries.update-status', $surgery) }}"
                                                      method="POST"
                                                      class="dropdown-menu dropdown-menu-end status-form">
                                                    @csrf
                                                    @method('PATCH')
                                                    @if($status !== 'pending')
                                                        <button type="submit" name="status" value="pending" class="dropdown-item">
                                                            <i class="bi bi-clock text-warning"></i> Pendiente
                                                        </button>
                                                    @endif
                                                    @if($status !== 'in_progress')
                                                        <button type="submit" name="status" value="in_progress" class="dropdown-item">
                                                            <i class="bi bi-play-circle text-info"></i> En Progreso
                                                        </button>
                                                    @endif
                                                    <button type="submit" name="status" value="completed" class="dropdown-item">
                                                        <i class="bi bi-check-circle text-success"></i> Completada
                                                    </button>
                                                    <button type="submit" name="status" value="cancelled" class="dropdown-item">
                                                        <i class="bi bi-x-circle text-danger"></i> Cancelada
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

@push('styles')
<style>
.surgery-card {
    transition: all 0.2s ease-in-out;
}
.surgery-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.card-text {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.dropdown-item:hover {
    background-color: #f8f9fa;
}
.status-form.loading button {
    opacity: 0.7;
    pointer-events: none;
}
.status-form.loading button::after {
    content: "";
    width: 1rem;
    height: 1rem;
    display: inline-block;
    margin-left: 0.5rem;
    vertical-align: middle;
    border: 2px solid currentColor;
    border-right-color: transparent;
    border-radius: 50%;
    animation: spin 0.75s linear infinite;
}
@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle status update forms
    document.querySelectorAll('.status-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            // Add loading state
            this.classList.add('loading');

            // Disable all buttons in the form
            this.querySelectorAll('button').forEach(button => button.disabled = true);

            fetch(this.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    _method: 'PATCH',
                    status: this.querySelector('button[type="submit"]:focus').value
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3';
                    alert.innerHTML = `
                        ${data.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    document.body.appendChild(alert);

                    // Reload the page after a short delay
                    setTimeout(() => window.location.reload(), 500);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Remove loading state
                this.classList.remove('loading');
                this.querySelectorAll('button').forEach(button => button.disabled = false);

                // Show error message
                const alert = document.createElement('div');
                alert.className = 'alert alert-danger alert-dismissible fade show position-fixed top-0 end-0 m-3';
                alert.innerHTML = `
                    Ha ocurrido un error al actualizar el estado de la cirugía
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.body.appendChild(alert);
            });
        });
    });

    // Auto-remove alerts after 5 seconds
    setInterval(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            if (alert.querySelector('.btn-close')) {
                alert.querySelector('.btn-close').click();
            }
        });
    }, 5000);
});
</script>
@endpush
@endsection
