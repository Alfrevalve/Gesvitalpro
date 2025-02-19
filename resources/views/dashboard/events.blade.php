<div class="row">
    <!-- Próximos Eventos -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Próximos Eventos</h5>
            </div>
            <div class="card-body">
                @if($upcoming_events->isEmpty())
                    <p class="text-muted text-center mb-0">No hay eventos próximos programados.</p>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($upcoming_events as $event)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ $event['title'] }}</h6>
                                        <small class="text-muted">
                                            {{ $event['date']->format('d/m/Y H:i') }}
                                        </small>
                                    </div>
                                    <span class="badge bg-{{ $event['type'] === 'surgery' ? 'primary' : 'info' }}">
                                        {{ $event['type'] === 'surgery' ? 'Cirugía' : 'Visita' }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Alertas del Sistema -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Alertas del Sistema</h5>
            </div>
            <div class="card-body">
                @if($alerts->isEmpty())
                    <p class="text-muted text-center mb-0">No hay alertas pendientes.</p>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($alerts as $alert)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ $alert['message'] }}</h6>
                                        <small class="text-muted">
                                            @if(isset($alert['details']['date']))
                                                {{ \Carbon\Carbon::parse($alert['details']['date'])->format('d/m/Y H:i') }}
                                            @endif
                                        </small>
                                    </div>
                                    <span class="badge bg-{{ $alert['level'] }}">
                                        {{ ucfirst($alert['type']) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
