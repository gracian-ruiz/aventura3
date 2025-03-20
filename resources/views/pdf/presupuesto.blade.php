<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura del Presupuesto #{{ $presupuesto->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; }
        h2, h3 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
        .total { font-weight: bold; text-align: right; }
    </style>
</head>
<body>

    <h2>Factura del Presupuesto #{{ $presupuesto->id }}</h2>
    
    <p><strong>Cliente:</strong> {{ $presupuesto->usuario_nombre }}</p>
    <p><strong>Teléfono:</strong> {{ $presupuesto->usuario_telefono }}</p>
    
    <p><strong>Bicicleta:</strong> {{ $presupuesto->bicicleta_nombre }} (ID: {{ $presupuesto->bicicleta_id }})</p>
    <p><strong>Fecha:</strong> {{ date('d/m/Y', strtotime($presupuesto->created_at)) }}</p>

    <h3>Detalles del Presupuesto</h3>
    <table>
        <thead>
            <tr>
                <th>Componente</th>
                <th>Minutos de Trabajo</th>
                <th>Precio</th>
                <th>Descripción</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
            <tr>
                <td>{{ $item->componente_nombre }}</td>
                <td>{{ $item->horas_trabajo }}</td>
                <td>{{ number_format($item->total_precio, 2) }}€</td>
                <td>{{ $item->texto }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p class="total"><strong>Total a Pagar:</strong> {{ number_format($presupuesto->precio_total, 2) }}€</p>

</body>
</html>
