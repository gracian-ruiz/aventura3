@extends('layouts.app')

@section('content')
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background-color: #f9fafb;
        }
        #calendar {
            max-width: 1200px; /* 🔹 más ancho que antes */
            margin: 0 auto;
            font-size: 16px;   /* 🔹 aumenta el tamaño base */
        }

        /* 🔹 Texto de los eventos */
        .fc .fc-daygrid-event {
            font-size: 13px !important; /* más grande */
            font-weight: bold !important;
            padding: 6px 4px;
        }

        /* 🔹 Ajustar tamaño de los títulos de los días */
        .fc .fc-col-header-cell-cushion {
            font-size: 16px !important;
            font-weight: bold;
        }

        /* 🔹 Ajustar tamaño del título del mes */
        .fc .fc-toolbar-title {
            font-size: 22px !important;
        }
    </style>

    <h2 style="text-align:center;">📅 Alquileres por Usuario y Bicicleta</h2>

    <div id="calendar"></div>

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

        // 🔹 Permitir mostrar HTML (cliente + bicicleta en 2 líneas)
        eventContent: function(arg) {
            let div = document.createElement('div');
            div.innerHTML = arg.event.title;
            return { domNodes: [div] };
        },

        eventClick: function(info) {
            window.open(info.event.url, '_blank');
            info.jsEvent.preventDefault();
        }
    });

    calendar.render();
});
</script>


@endsection
