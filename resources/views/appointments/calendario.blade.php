@extends('layouts.app')

@section('content')
    <div class="w-full px-4 sm:px-6 lg:px-10 mt-6">
        <h1 class="app-title text-center mb-4">Calendario de Citas</h1>
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
