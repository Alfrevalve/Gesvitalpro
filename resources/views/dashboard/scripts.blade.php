<script>
    // Datos de los últimos 6 meses desde el controlador
    const months = @json($monthly_stats['labels']);
    const surgeryData = @json($monthly_stats['surgeries']);
    const revenueData = @json($monthly_stats['revenue']);
    const distributionLabels = @json($monthly_stats['distribution']['labels']);
    const distributionData = @json($monthly_stats['distribution']['data']);
    const distributionColors = @json($monthly_stats['distribution']['colors']);

    // Configuración común para los gráficos
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
            }
        }
    };

    // Gráfico de Cirugías
    const surgeryCtx = document.getElementById('surgeryChart').getContext('2d');
    new Chart(surgeryCtx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: 'Cirugías Realizadas',
                data: surgeryData,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1,
                fill: false
            }]
        },
        options: {
            ...commonOptions,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 10
                    }
                }
            }
        }
    });

    // Gráfico de Ingresos
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'bar',
        data: {
            labels: months,
            datasets: [{
                label: 'Ingresos ($)',
                data: revenueData,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgb(54, 162, 235)',
                borderWidth: 1
            }]
        },
        options: {
            ...commonOptions,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Gráfico de Distribución de Cirugías
    const distributionCtx = document.getElementById('surgeryDistributionChart').getContext('2d');
    new Chart(distributionCtx, {
        type: 'doughnut',
        data: {
            labels: distributionLabels,
            datasets: [{
                data: distributionData,
                backgroundColor: distributionLabels.map(label => distributionColors[label]),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
</script>
