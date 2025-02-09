@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Kambas de Cirugías</h1>
    <div class="row">
        <div class="col">
            <h2>Programadas</h2>
            <div class="card">
                <div class="card-body">
                    <!-- Aquí se pueden agregar las cirugías programadas -->
                </div>
            </div>
        </div>
        <div class="col">
            <h2>En Proceso</h2>
            <div class="card">
                <div class="card-body">
                    <!-- Aquí se pueden agregar las cirugías en proceso -->
                </div>
            </div>
        </div>
        <div class="col">
            <h2>Completadas</h2>
            <div class="card">
                <div class="card-body">
                    <!-- Aquí se pueden agregar las cirugías completadas -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
