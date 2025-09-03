<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Calendario de Alquileres</title>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
        }
        #calendar {
            max-width: 900px;
            margin: 0 auto;
        }
    </style>
</head>
<body>

    <h2 style="text-align: center;">📅 Calendario de Alquileres con Fechas de Inicio y Fin</h2>
    <div id="calendar"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var calendarEl = document.getElementById('calendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                events: [
                    {
                        title: 'Alquiler: Bicicleta 1',
                        start: '2025-09-02',
                        end: '2025-09-05'
                    },
                    {
                        title: 'Alquiler: Bicicleta 2',
                        start: '2025-09-06',
                        end: '2025-09-08'
                    },
                    {
                        title: 'Alquiler: Bicicleta 3',
                        start: '2025-09-10',
                        end: '2025-09-13'
                    }
                ]
            });

            calendar.render();
        });
    </script>

</body>
</html>
