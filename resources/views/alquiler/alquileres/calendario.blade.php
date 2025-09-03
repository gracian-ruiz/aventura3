@extends('layouts.app2')

@section('content')
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background-color: #f9fafb;
        }
        #calendar {
            max-width: 1000px;
            margin: 0 auto;
        }
    </style>
</head>
<body>

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
            eventClick: function(info) {
                window.open(info.event.url, '_blank');
                info.jsEvent.preventDefault();
            }
        });

        calendar.render();
    });
    </script>

</body>
@endsection


