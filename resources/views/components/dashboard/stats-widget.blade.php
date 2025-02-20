<div class="card h-100 {{ $darkMode ? 'bg-dark text-white' : 'bg-white' }}"
     x-data="{
        value: @json($value),
        refreshInterval: @json($refreshInterval),
        init() {
            if (this.refreshInterval > 0) {
                setInterval(() => this.refreshData(), this.refreshInterval * 1000)
            }
        },
        async refreshData() {
            try {
                const response = await fetch(window.location.pathname + '?partial=stats-widget')
                const data = await response.json()
                this.value = data.value
            } catch (error) {
                console.error('Error refreshing widget data:', error)
            }
        }
     }"
     @if($animations['enabled'])
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform scale-90"
     x-transition:enter-end="opacity-100 transform scale-100"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100 transform scale-100"
     x-transition:leave-end="opacity-0 transform scale-90"
     @endif>

    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <div class="icon-circle bg-{{ $color }}-light">
                        <i class="fas fa-{{ $icon }} text-{{ $color }}"></i>
                    </div>
                </div>
                <div>
                    <h6 class="card-title mb-0 {{ $darkMode ? 'text-white-50' : 'text-muted' }}">
                        {{ $title }}
                    </h6>
                </div>
            </div>

            @if($refreshInterval > 0)
            <div class="dropdown">
                <button class="btn btn-link {{ $darkMode ? 'text-white' : 'text-dark' }} dropdown-toggle"
                        type="button"
                        data-bs-toggle="dropdown">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu {{ $darkMode ? 'dropdown-menu-dark' : '' }}">
                    <li>
                        <a class="dropdown-item" href="#" @click.prevent="refreshData()">
                            <i class="fas fa-sync-alt me-2"></i> Actualizar ahora
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#" @click.prevent="refreshInterval = 0">
                            <i class="fas fa-pause me-2"></i> Pausar actualizaciones
                        </a>
                    </li>
                </ul>
            </div>
            @endif
        </div>

        <div class="mt-4">
            <h2 class="mb-1 {{ $darkMode ? 'text-white' : '' }}" x-text="formatValue(value)"></h2>

            @if($trendValue !== null)
            <div class="d-flex align-items-center mt-2">
                <span class="{{ $getTrendClass() }} me-2">
                    <i class="fas fa-{{ $getTrendIcon() }} me-1"></i>
                    {{ $trendValue }}%
                </span>
                <span class="{{ $darkMode ? 'text-white-50' : 'text-muted' }} small">
                    vs mes anterior
                </span>
            </div>
            @endif
        </div>

        @if(isset($chart))
        <div class="mt-4" style="height: 100px;">
            <canvas x-ref="chart"
                    x-init="
                        const ctx = $refs.chart.getContext('2d');
                        new Chart(ctx, @json($chartConfig));
                    ">
            </canvas>
        </div>
        @endif
    </div>

    @if($shouldComponentUpdate())
    <div class="card-footer bg-warning text-dark py-1">
        <small>
            <i class="fas fa-exclamation-triangle me-1"></i>
            Rendimiento degradado detectado
        </small>
    </div>
    @endif
</div>

@push('scripts')
<script>
    function formatValue(value) {
        if (typeof value === 'number') {
            return new Intl.NumberFormat('es-ES').format(value);
        }
        return value;
    }
</script>
@endpush

@once
@push('styles')
<style>
    .icon-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .bg-primary-light { background-color: rgba(var(--bs-primary-rgb), 0.1); }
    .bg-success-light { background-color: rgba(var(--bs-success-rgb), 0.1); }
    .bg-warning-light { background-color: rgba(var(--bs-warning-rgb), 0.1); }
    .bg-danger-light { background-color: rgba(var(--bs-danger-rgb), 0.1); }
    .bg-info-light { background-color: rgba(var(--bs-info-rgb), 0.1); }

    [x-cloak] { display: none !important; }

    .dark-mode {
        .card {
            border-color: rgba(255, 255, 255, 0.1);
        }

        .icon-circle {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .text-muted {
            color: rgba(255, 255, 255, 0.6) !important;
        }
    }
</style>
@endpush
@endonce
