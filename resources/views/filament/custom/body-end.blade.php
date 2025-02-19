<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.location.pathname === '/admin') {
            console.log('¡Bienvenido al Panel de Administración de GesBio!');
        }
    });
</script>

<style>
    /* Colores personalizados */
    :root {
        --primary-color: #0F3061;
        --info-color: #0097CD;
        --success-color: #65D7CA;
        --warning-color: #AAE9E2;
    }

    /* Estilos de la barra lateral */
    .filament-sidebar-nav {
        background: linear-gradient(to bottom, var(--primary-color), #0c264e);
    }

    .filament-sidebar-nav .filament-sidebar-item {
        margin: 4px 0;
    }

    /* Estilos de la barra superior */
    .filament-main-topbar {
        background: linear-gradient(to right, var(--primary-color), #0c264e);
    }

    /* Estilos del modo oscuro */
    .dark .filament-main {
        background-color: #091d3a;
    }

    .dark .filament-main-content {
        background-color: #0c264e;
    }

    /* Estilos de los badges */
    .filament-tables-badge {
        &.success {
            background-color: var(--success-color);
        }
        &.info {
            background-color: var(--info-color);
        }
        &.warning {
            background-color: var(--warning-color);
        }
    }

    /* Estilos de los botones */
    .filament-button {
        &.primary {
            background-color: var(--primary-color);
        }
        &.info {
            background-color: var(--info-color);
        }
        &.success {
            background-color: var(--success-color);
        }
        &.warning {
            background-color: var(--warning-color);
        }
    }
</style>
