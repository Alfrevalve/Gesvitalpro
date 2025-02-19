<div class="btn-group">
    @can('view', $item)
    <a href="{{ route($route_prefix . '.show', $item) }}" class="btn btn-sm btn-info" title="Ver">
        <i class="fas fa-eye"></i>
    </a>
    @endcan

    @can('update', $item)
    <a href="{{ route($route_prefix . '.edit', $item) }}" class="btn btn-sm btn-primary" title="Editar">
        <i class="fas fa-edit"></i>
    </a>
    @endcan

    @can('delete', $item)
    <button type="button" class="btn btn-sm btn-danger delete-item"
            data-id="{{ $item->id }}"
            data-url="{{ route($route_prefix . '.destroy', $item) }}"
            title="Eliminar">
        <i class="fas fa-trash"></i>
    </button>
    @endcan

    @if(method_exists($item, 'trashed') && $item->trashed())
        @can('restore', $item)
        <button type="button" class="btn btn-sm btn-success restore-item"
                data-id="{{ $item->id }}"
                data-url="{{ route($route_prefix . '.restore', $item) }}"
                title="Restaurar">
            <i class="fas fa-trash-restore"></i>
        </button>
        @endcan
    @endif
</div>

@once
@push('js')
<script>
$(function() {
    // Eliminar item
    $('.delete-item').click(function() {
        const url = $(this).data('url');
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción no se puede revertir",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire(
                                '¡Eliminado!',
                                response.message,
                                'success'
                            ).then(() => {
                                window.LaravelDataTables["dataTable"].ajax.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Error',
                            'Hubo un error al eliminar el registro',
                            'error'
                        );
                    }
                });
            }
        });
    });

    // Restaurar item
    $('.restore-item').click(function() {
        const url = $(this).data('url');
        Swal.fire({
            title: '¿Restaurar registro?',
            text: "El registro volverá a estar activo",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, restaurar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire(
                                '¡Restaurado!',
                                response.message,
                                'success'
                            ).then(() => {
                                window.LaravelDataTables["dataTable"].ajax.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Error',
                            'Hubo un error al restaurar el registro',
                            'error'
                        );
                    }
                });
            }
        });
    });
});
</script>
@endpush
@endonce
