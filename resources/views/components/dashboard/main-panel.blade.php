<div class="dashboard-container {{ $darkMode ? 'dark-mode' : '' }}"
     x-data="{
        activeTab: 'overview',
        refreshDashboard() {
            this.$dispatch('refresh-widgets')
        }
     }">

    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0 {{ $darkMode ? 'text-white' : 'text-gray-800' }}">Panel de Control</h1>
                <div class="btn-toolbar">
                    <button class="btn btn-sm {{ $darkMode ? 'btn-dark' : 'btn-light' }} me-2"
                            @click="$dispatch('toggle-dark-mode')">
                        <i class="fas" :class="{'fa-moon': !darkMode, 'fa-sun': darkMode}"></i>
                    </button>
                    <button class="btn btn-sm btn-primary" @click="refreshDashboard()">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        @foreach($stats as $key => $stat)
        <div class="col-xl-3 col-md-6 mb-4">
            <x-dashboard.stats-widget
                :title="$stat['title']"
                :value="$stat['value']"
                :icon="$stat['icon']"
                :color="$stat['color']"
                :trend="$stat['trend']['direction'] ?? 'neutral'"
                :trend-value="$stat['trend']['value'] ?? null"
                :refresh-interval="$stat['refresh_interval']"
            />
        </div>
        @endforeach
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card {{ $darkMode ? 'bg-dark text-white' : 'bg-white' }}">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs">
                        <li class="nav-item">
                            <a class="nav-link"
                               :class="{ 'active': activeTab === 'overview' }"
                               @click.prevent="activeTab = 'overview'"
                               href="#">
                                Vista General
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link"
                               :class="{ 'active': activeTab === 'performance' }"
                               @click.prevent="activeTab = 'performance'"
                               href="#">
                                Rendimiento
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div x-show="activeTab === 'overview'">
                        <canvas id="mainChart"
                                x-init="
                                    const ctx = document.getElementById('mainChart').getContext('2d');
                                    new Chart(ctx, {
                                        type: 'line',
                                        data: @json($chartData),
                                        options: {
                                            responsive: true,
                                            maintainAspectRatio: false,
                                            scales: {
                                                y: {
                                                    beginAtZero: true,
                                                    grid: {
                                                        color: '{{ $darkMode ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)' }}'
                                                    }
                                                },
                                                x: {
                                                    grid: {
                                                        color: '{{ $darkMode ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)' }}'
                                                    }
                                                }
                                            },
                                            plugins: {
                                                legend: {
                                                    labels: {
                                                        color: '{{ $darkMode ? '#fff' : '#666' }}'
                                                    }
                                                }
                                            }
                                        }
                                    });
                                "
                                style="height: 300px;">
                        </canvas>
                    </div>

                    <div x-show="activeTab === 'performance'" x-cloak>
                        <div class="row">
                            @foreach($performance as $category => $metrics)
                            <div class="col-md-6 mb-4">
                                <h5 class="mb-3">{{ ucfirst($category) }}</h5>
                                <div class="table-responsive">
                                    <table class="table {{ $darkMode ? 'table-dark' : 'table-light' }}">
                                        <tbody>
                                            @foreach($metrics as $metric => $value)
                                            <tr>
                                                <td>{{ ucfirst(str_replace('_', ' ', $metric)) }}</td>
                                                <td class="text-end">
                                                    @if(is_numeric($value))
                                                        {{ number_format($value, 2) }}
                                                    @else
                                                        {{ $value }}
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(count($alerts['maintenance']) > 0 || count($alerts['performance']) > 0)
    <div class="row">
        <div class="col-12">
            <div class="card {{ $darkMode ? 'bg-dark text-white' : 'bg-white' }} mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Alertas</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach(array_merge($alerts['maintenance'], $alerts['performance']) as $alert)
                        <div class="list-group-item {{ $darkMode ? 'bg-dark text-white border-light' : '' }}">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">
                                    <i class="fas fa-exclamation-triangle text-{{ $alert['type'] }} me-2"></i>
                                    {{ $alert['message'] }}
                                </h6>
                                <small>{{ Carbon\Carbon::parse($alert['date'])->diffForHumans() }}</small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('styles')
<style>
    .dashboard-container {
        transition: background-color 0.3s ease;
    }

    .dark-mode {
        background-color: #1a1a1a;
    }

    .nav-tabs .nav-link {
        cursor: pointer;
    }

    .dark-mode .nav-tabs .nav-link {
        color: rgba(255,255,255,0.8);
    }

    .dark-mode .nav-tabs .nav-link.active {
        background-color: #2d2d2d;
        border-color: #444;
        color: white;
    }

    [x-cloak] {
        display: none !important;
    }

    .list-group-item {
        transition: background-color 0.3s ease;
    }

    @if($animations['enabled'])
    .dashboard-container {
        animation: fadeIn 0.5s ease-in-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    @endif
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('toggle-dark-mode', function() {
        document.body.classList.toggle('dark-mode');
    });

    document.addEventListener('refresh-widgets', function() {
        window.location.reload();
    });
</script>
@endpush
