@extends('layouts.bootstrap')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <h5>{{ __('You are logged in!') }}</h5>
                    <p>Bienvenido al sistema de gestión de pacientes. Aquí puedes gestionar toda la información relacionada con los pacientes, visitas y cirugías.</p>
                    <div class="mt-4">
                        <a href="{{ route('pacientes.index') }}" class="btn btn-primary">Gestionar Pacientes</a>
                        <a href="{{ route('visitas.index') }}" class="btn btn-success">Gestionar Visitas</a>
                        <a href="{{ route('cirugias.index') }}" class="btn btn-warning">Gestionar Cirugías</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
