@extends('layouts.bootstrap')

@section('content')
<div class="container mt-4">
    @include('components.user-panel') <!-- Incluir el componente de panel de usuario -->
    <h1 class="mb-4">Dashboard</h1>
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-users"></i> Total Pacientes</h5>
                    <p class="card-text">{{ $data['total_patients'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-calendar-check"></i> Total Visitas</h5>
                    <p class="card-text">{{ $data['total_visits'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-surgery"></i> Total Cirugías</h5>
                    <p class="card-text">{{ $data['total_surgeries'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-user"></i> Total Usuarios</h5>
                    <p class="card-text">{{ $data['total_users'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Gráfico de Visitas</h5>
                    <canvas id="visitsChart"></canvas> <!-- Gráfico de visitas -->
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Gráfico de Cirugías</h5>
                    <canvas id="surgeriesChart"></canvas> <!-- Gráfico de cirugías -->
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctxVisits = document.getElementById('visitsChart').getContext('2d');
    const visitsChart = new Chart(ctxVisits, {
        type: 'bar',
        data: {
            labels: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo'],
            datasets: [{
                label: 'Visitas',
                data: [12, 19, 3, 5, 2],
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    const ctxSurgeries = document.getElementById('surgeriesChart').getContext('2d');
    const surgeriesChart = new Chart(ctxSurgeries, {
        type: 'line',
        data: {
            labels: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo'],
            datasets: [{
                label: 'Cirugías',
                data: [5, 10, 15, 20, 25],
                fill: false,
                borderColor: 'rgba(255, 99, 132, 1)',
                tension: 0.1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
@endsection
