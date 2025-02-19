<!-- Modal para confirmar entrega -->
<div class="modal fade" id="confirmDeliveryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('dispatch.confirm-delivery', $surgeryRequest) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Entrega</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="recipient_name" class="form-label">Nombre del Receptor</label>
                        <input type="text" name="recipient_name" id="recipient_name" class="form-control @error('recipient_name') is-invalid @enderror" required>
                        @error('recipient_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="signature-pad" class="form-label">Firma del Receptor</label>
                        <div id="signature-pad" class="border rounded p-2 mb-2" style="height: 200px; background-color: white;"></div>
                        <input type="hidden" name="recipient_signature" id="signature">
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-secondary" onclick="clearSignature()">
                                <i class="bi bi-eraser"></i> Limpiar Firma
                            </button>
                        </div>
                        @error('recipient_signature')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="delivery_photo" class="form-label">
                            Foto de Entrega
                            <small class="text-muted">(Opcional)</small>
                        </label>
                        <div class="input-group">
                            <input type="file" name="delivery_photo" id="delivery_photo"
                                class="form-control @error('delivery_photo') is-invalid @enderror"
                                accept="image/*"
                                capture="environment">
                            <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('delivery_photo').value = ''">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </div>
                        @error('delivery_photo')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                        <div id="photo-preview" class="mt-2 d-none">
                            <img src="" alt="Vista previa" class="img-fluid rounded">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">
                            Notas
                            <small class="text-muted">(Opcional)</small>
                        </label>
                        <textarea name="notes" id="notes" rows="3"
                            class="form-control @error('notes') is-invalid @enderror"
                            placeholder="Agregar notas o comentarios sobre la entrega..."></textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Confirmar Entrega
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
    // Inicializar pad de firma
    const canvas = document.getElementById('signature-pad');
    const signaturePad = new SignaturePad(canvas, {
        backgroundColor: 'rgb(255, 255, 255)'
    });

    // Función para limpiar firma
    function clearSignature() {
        signaturePad.clear();
    }

    // Ajustar tamaño del canvas cuando se abre el modal
    document.getElementById('confirmDeliveryModal').addEventListener('shown.bs.modal', function () {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext("2d").scale(ratio, ratio);
        signaturePad.clear();
    });

    // Guardar firma en campo oculto antes de enviar
    document.querySelector('#confirmDeliveryModal form').addEventListener('submit', function(e) {
        if (signaturePad.isEmpty()) {
            e.preventDefault();
            alert('Por favor, capture la firma del receptor');
            return false;
        }
        document.getElementById('signature').value = signaturePad.toDataURL();
    });

    // Vista previa de foto
    document.getElementById('delivery_photo').addEventListener('change', function(e) {
        const preview = document.getElementById('photo-preview');
        const previewImg = preview.querySelector('img');

        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                preview.classList.remove('d-none');
            }
            reader.readAsDataURL(this.files[0]);
        } else {
            preview.classList.add('d-none');
        }
    });

    // Ajustar tamaño del canvas cuando cambia el tamaño de la ventana
    window.addEventListener('resize', function() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext("2d").scale(ratio, ratio);
    });
</script>
@endpush

@push('styles')
<style>
    #signature-pad {
        touch-action: none;
    }
</style>
@endpush
