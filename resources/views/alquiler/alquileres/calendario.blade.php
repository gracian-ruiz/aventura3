@extends('layouts.app2')

@section('content')
    <div class="w-full px-4 sm:px-6 lg:px-10 mt-6">
        <h2 class="app-title text-center mb-4">Alquileres por Usuario y Bicicleta</h2>
        <div class="app-calendar-shell">
            <div id="calendar" class="app-calendar"></div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('calendar');

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'es',
            height: 'auto',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,listWeek'
            },
            events: @json($eventos),
            eventClick: function(info) {
                window.open(info.event.url, '_blank');
                info.jsEvent.preventDefault();
            }
        });

        calendar.render();
    });
    </script>

@endsection


