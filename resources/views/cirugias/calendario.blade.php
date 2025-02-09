@extends('layouts.bootstrap')

@section('content')
<div class="container">
    <h1 class="mb-4">Calendario de Cirugías</h1>
    <div id="calendar"></div>
</div>

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            events: [
                // Aquí se pueden agregar eventos de cirugías
                // Ejemplo: { title: 'Cirugía 1', start: '2023-10-01' }
            ]
        });
        calendar.render();
    });
</script>
@endsection
@endsection
