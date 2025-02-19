// Configuración global para DataTables
$.extend(true, $.fn.dataTable.defaults, {
    language: {
        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
    },
    pageLength: 25,
    processing: true,
    serverSide: true,
    responsive: true,
    autoWidth: false,
    dom: '<"d-flex justify-content-between align-items-center mb-3"<"d-flex align-items-center"l><"d-flex"f>>t<"d-flex justify-content-between align-items-center"<"text-muted"i><"pagination-container"p>>',
    order: [[0, 'desc']],
});

// Configuración global para Select2
$.fn.select2.defaults.set('language', 'es');
$.fn.select2.defaults.set('theme', 'bootstrap4');

// Configuración global para SweetAlert2
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
});

// Función para mostrar notificaciones
function showNotification(type, message) {
    Toast.fire({
        icon: type,
        title: message
    });
}

// Función para confirmar acciones
function confirmAction(options) {
    return Swal.fire({
        title: options.title || '¿Estás seguro?',
        text: options.text || "Esta acción no se puede revertir",
        icon: options.icon || 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: options.confirmButtonText || 'Sí, continuar',
        cancelButtonText: options.cancelButtonText || 'Cancelar'
    });
}

// Función para manejar errores de Ajax
function handleAjaxError(xhr) {
    let message = 'Ha ocurrido un error';
    if (xhr.responseJSON && xhr.responseJSON.message) {
        message = xhr.responseJSON.message;
    }
    showNotification('error', message);
}

// Inicialización de componentes comunes
$(function() {
    // Inicializar tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // Inicializar popovers
    $('[data-toggle="popover"]').popover();

    // Manejar la subida de archivos con vista previa
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);

        // Si hay un elemento para vista previa
        let previewElement = $($(this).data('preview'));
        if (previewElement.length && this.files && this.files[0]) {
            let reader = new FileReader();
            reader.onload = function(e) {
                previewElement.attr('src', e.target.result);
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Manejar enlaces de eliminación con confirmación
    $(document).on('click', '.delete-link', function(e) {
        e.preventDefault();
        let url = $(this).attr('href') || $(this).data('url');

        confirmAction({
            title: '¿Eliminar registro?',
            text: "Esta acción no se puede revertir"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            showNotification('success', response.message);
                            if (typeof window.LaravelDataTables !== 'undefined') {
                                window.LaravelDataTables["dataTable"].ajax.reload();
                            } else {
                                window.location.reload();
                            }
                        }
                    },
                    error: handleAjaxError
                });
            }
        });
    });

    // Manejar cambios de estado con confirmación
    $(document).on('click', '.change-status', function(e) {
        e.preventDefault();
        let url = $(this).data('url');
        let newStatus = $(this).data('status');

        confirmAction({
            title: '¿Cambiar estado?',
            text: "¿Estás seguro de que deseas cambiar el estado?",
            icon: 'question'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        status: newStatus
                    },
                    success: function(response) {
                        if (response.success) {
                            showNotification('success', response.message);
                            if (typeof window.LaravelDataTables !== 'undefined') {
                                window.LaravelDataTables["dataTable"].ajax.reload();
                            } else {
                                window.location.reload();
                            }
                        }
                    },
                    error: handleAjaxError
                });
            }
        });
    });
});
