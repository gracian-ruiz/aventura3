@extends('layouts.app')

@section('content')
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background-color: #f9fafb;
        }
        #calendar {
            max-width: 1200px;
            margin: 0 auto;
            font-size: 16px;
        }

        .fc .fc-daygrid-event {
            font-size: 13px !important;
            font-weight: bold !important;
            padding: 6px 4px;
        }

        .fc .fc-col-header-cell-cushion {
            font-size: 16px !important;
            font-weight: bold;
        }

        .fc .fc-toolbar-title {
            font-size: 22px !important;
        }
    </style>

    <h2 style="text-align:center;">📅 Citas Asignadas por Día</h2>

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
                if (info.event.url) {
                    window.open(info.event.url, '_blank');
                    info.jsEvent.preventDefault();
                }
            }
        });

        calendar.render();
    });
    </script>
@endsection
