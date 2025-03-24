<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presupuesto</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
        }
        .details p {
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .total {
            margin-top: 20px;
            text-align: right;
            font-size: 1.2em;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Presupuesto</h1>
        <div class="details">
            <p><strong>Cliente:</strong> {{ $presupuesto->cliente_nombre ?? 'N/A' }}</p>
            <p><strong>Bicicleta:</strong> {{ $presupuesto->bike_nombre ?? 'N/A' }}</p>
            <p><strong>Fecha:</strong> {{ $presupuesto->fecha ?? now()->format('d-m-Y') }}</p>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th>Tiempo Estimado</th>
                    <th>Costo</th>
                </tr>
            </thead>

        </table>
        
        
    </div>
</body>
</html>
