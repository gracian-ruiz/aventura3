@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-10 mt-6">
    <h1 class="app-title text-center mb-4">Calendario Asignado</h1>
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
