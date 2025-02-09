@extends('layouts.bootstrap')

@section('content')
<div class="container">
    <h1 class="mb-4">Gestión de Pacientes</h1>
    
    <form id="addPatientForm" method="POST" action="{{ route('pacientes.store') }}">
        @csrf
        <div class="form-group">
            <label for="patientName">Nombre</label>
            <input type="text" class="form-control" id="patientName" name="name" required>
        </div>
        <div class="form-group">
            <label for="patientEmail">Email</label>
            <input type="email" class="form-control" id="patientEmail" name="email" required>
        </div>
        <button type="submit" class="btn btn-primary">Guardar</button>
    </form>

    <table class="table table-striped mt-4">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="patientTableBody">
            <!-- Aquí se llenarán los datos de los pacientes -->
        </tbody>
    </table>
</div>

<script>
    $(document).ready(function() {
        fetchPatients();

        function fetchPatients() {
            $.ajax({
                url: '{{ route('pacientes.index') }}',
                method: 'GET',
                success: function(response) {
                    let rows = '';
                    response.data.forEach(function(patient) {
                        rows += `
                            <tr>
                                <td>${patient.id}</td>
                                <td>${patient.name}</td>
                                <td>${patient.email}</td>
                                <td>
                                    <button class="btn btn-warning btn-sm">Editar</button>
                                    <button class="btn btn-danger btn-sm">Eliminar</button>
                                </td>
                            </tr>
                        `;
                    });
                    $('#patientTableBody').html(rows);
                },
                error: function(xhr) {
                    alert('Hubo un error al cargar los pacientes.');
                }
            });
        }
    });
</script>
@endsection
</create_file>
