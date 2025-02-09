@extends('layouts.bootstrap')

@section('content')
<div class="container">
    <h2 class="mb-4">Dashboard</h2>
    <p>Aquí puedes gestionar las visitas, pacientes, inventarios, cirugías y más.</p>
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Gráfico de Cirugías Mensuales</h5>
                    <canvas id="cirugiasMensualesChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Gráfico de Visitas</h5>
                    <canvas id="visitasChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
