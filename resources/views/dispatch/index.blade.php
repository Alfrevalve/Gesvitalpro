@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3">Despacho - Entregas</h1>
        </div>
        <div class="col text-end">
            <a href="{{ route('dispatch.kanban') }}" class="btn btn-outline-primary me-2">
                <i class="bi bi-kanban"></i> Ver Kanban
            </a>
            <a href="{{ route('dispatch.report') }}" class="btn btn-outline-secondary">
                <i class="bi bi-file-earmark-text"></i> Ver Reportes
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($deliveries->isEmpty())
                <div class="text-center py-4">
                    <i class="bi bi-truck display-4 text-muted"></i>
                    <p class="mt-3">No hay entregas pendientes</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cirugía</th>
                                <th>Institución</th>
                                <th>Fecha Preparación</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deliveries as $delivery)
                                <tr>
                                    <td>{{ $delivery->id }}</td>
                                    <td>
                                        <a href="{{ route('surgeries.show', $delivery->surgery) }}" class="text-decoration-none">
                                            {{ $delivery->surgery->description }}
                                        </a>
                                    </td>
                                    <td>{{ $delivery->surgery->line->name }}</td>
                                    <td>{{ $delivery->preparation->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $delivery->dispatch_status == 'pending' ? 'warning' : ($delivery->dispatch_status == 'in_transit' ? 'info' : 'success') }}">
                                            {{ ucfirst($delivery->dispatch_status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('dispatch.show', $delivery) }}" class="btn btn-sm btn-info">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @if($delivery->dispatch_status != 'delivered')
                                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#confirmDeliveryModal{{ $delivery->id }}">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>
                                            @endif
                                        </div>

                                        <!-- Modal para confirmar entrega -->
                                        <div class="modal fade" id="confirmDeliveryModal{{ $delivery->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('dispatch.confirm-delivery', $delivery) }}" method="POST" enctype="multipart/form-data">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Confirmar Entrega</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label for="recipient_name" class="form-label">Nombre del Receptor</label>
                                                                <input type="text" name="recipient_name" id="recipient_name" class="form-control" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="recipient_signature" class="form-label">Firma del Receptor</label>
                                                                <div id="signature-pad{{ $delivery->id }}" class="border rounded p-2 mb-2" style="height: 200px;"></div>
                                                                <input type="hidden" name="recipient_signature" id="signature{{ $delivery->id }}">
                                                                <button type="button" class="btn btn-sm btn-secondary" onclick="clearSignature{{ $delivery->id }}()">
                                                                    Limpiar Firma
                                                                </button>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="delivery_photo" class="form-label">Foto de Entrega</label>
                                                                <input type="file" name="delivery_photo" id="delivery_photo" class="form-control" accept="image/*" capture="environment">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="notes" class="form-label">Notas</label>
                                                                <textarea name="notes" id="notes" rows="3" class="form-control"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                            <button type="submit" class="btn btn-primary">Confirmar Entrega</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $deliveries->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
    @foreach($deliveries as $delivery)
        const signaturePad{{ $delivery->id }} = new SignaturePad(document.getElementById('signature-pad{{ $delivery->id }}'));

        function clearSignature{{ $delivery->id }}() {
            signaturePad{{ $delivery->id }}.clear();
        }

        document.getElementById('confirmDeliveryModal{{ $delivery->id }}').addEventListener('shown.bs.modal', function () {
            signaturePad{{ $delivery->id }}.clear();
        });

        document.querySelector('#confirmDeliveryModal{{ $delivery->id }} form').addEventListener('submit', function() {
            document.getElementById('signature{{ $delivery->id }}').value = signaturePad{{ $delivery->id }}.toDataURL();
        });
    @endforeach
</script>
@endpush
@endsection
