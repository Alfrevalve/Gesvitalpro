import './bootstrap';
import '../css/app.css';

// Importar Chart.js
import Chart from 'chart.js/auto';
window.Chart = Chart;

// Configuración de Chart.js
Chart.defaults.color = '#666';
Chart.defaults.font.family = "'Nunito', 'sans-serif'";

// Funciones para el Dashboard
window.initDashboardCharts = function() {
    // Gráfico de Cirugías
    if (document.getElementById('cirugiasPorEstadoChart')) {
        fetch('/dashboard/chart-data?tipo=cirugias&periodo=mes')
            .then(response => response.json())
            .then(data => {
                const ctx = document.getElementById('cirugiasPorEstadoChart');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: data.datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                    }
                });
            });
    }

    // Gráfico de Inventario
    if (document.getElementById('inventarioChart')) {
        fetch('/dashboard/chart-data?tipo=inventario')
            .then(response => response.json())
            .then(data => {
                const ctx = document.getElementById('inventarioChart');
                new Chart(ctx, {
                    type: 'doughnut',
                    data: data.valorPorCategoria,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                    }
                });
            });
    }
};

// Función para actualizar gráficos
window.updateDashboardChart = function(tipo, periodo) {
    fetch(`/dashboard/chart-data?tipo=${tipo}&periodo=${periodo}`)
        .then(response => response.json())
        .then(data => {
            const chartId = tipo === 'cirugias' ? 'cirugiasPorEstadoChart' : 'inventarioChart';
            const chart = Chart.getChart(chartId);
            if (chart) {
                chart.data.labels = data.labels;
                chart.data.datasets = data.datasets;
                chart.update();
            }
        });
};

// Función para exportar dashboard
window.exportarDashboard = function(formato) {
    window.location.href = `/dashboard/exportar?formato=${formato}`;
};

// Inicializar tooltips de Bootstrap
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Inicializar los gráficos del dashboard si estamos en esa página
    if (document.querySelector('.dashboard-charts')) {
        initDashboardCharts();
    }

    // Event listeners para filtros del dashboard
    const periodoSelect = document.getElementById('periodoCircugias');
    if (periodoSelect) {
        periodoSelect.addEventListener('change', function() {
            updateDashboardChart('cirugias', this.value);
        });
    }
});

// Función para mostrar/ocultar loading spinner
window.toggleLoading = function(show = true) {
    const spinner = document.getElementById('loadingSpinner');
    if (spinner) {
        spinner.style.display = show ? 'flex' : 'none';
    }
};

// Interceptor para Axios
window.axios.interceptors.request.use(function(config) {
    toggleLoading(true);
    return config;
}, function(error) {
    toggleLoading(false);
    return Promise.reject(error);
});

window.axios.interceptors.response.use(function(response) {
    toggleLoading(false);
    return response;
}, function(error) {
    toggleLoading(false);
    return Promise.reject(error);
});
