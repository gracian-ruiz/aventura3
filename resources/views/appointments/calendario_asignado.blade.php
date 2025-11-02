@extends('layouts.app')

@section('content')
<style>
    body {
        font-family: 'Poppins', sans-serif;
        margin: 40px;
        background-color: #f9fafb;
    }

    #calendar {
        margin: 0 auto;
        font-size: 16px;
    }

    /* 🎨 Estilos elegantes para eventos */
    .fc .fc-daygrid-event {
        font-size: 13px !important;
        font-weight: 600 !important;
        padding: 6px 4px;
        border: none !important;
        border-radius: 6px !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
    }

    /* 🔹 Eliminar el punto lateral de color */
    .fc-daygrid-event-dot {
        display: none !important;
    }

    /* 🔹 Títulos de día y mes */
    .fc .fc-col-header-cell-cushion {
        font-size: 16px !important;
        font-weight: 700;
        color: #374151;
    }

    .fc .fc-toolbar-title {
        font-size: 22px !important;
        font-weight: 700;
        color: #111827;
    }
</style>

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

        eventContent: function(arg) {
            let div = document.createElement('div');
            div.innerHTML = arg.event.title;

            // 🎨 Colores personalizados por prioridad o estado
            const color = arg.event.backgroundColor || arg.event.extendedProps.color || arg.event.color;
            div.style.backgroundColor = color;
            div.style.color = ['#D7263D', '#4A90E2', '#9B5DE5', '#00C49A'].includes(color) ? '#fff' : '#111';
            div.style.padding = '6px';
            div.style.borderRadius = '6px';
            div.style.boxShadow = '0 1px 3px rgba(0,0,0,0.15)';

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
