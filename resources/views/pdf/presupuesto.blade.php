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
        .header { text-align: center; margin-bottom: 20px; }
    </style>
</head>
<body>

    <div class="header">
        <h2>AVENTURA BIKE PK S.L.</h2>
        <p>Cif/Nif: B-19781212</p>
        <p>Avenida del Mediterráneo, 222, 04006 - Almería</p>
        <p>Tel/Fax: 950 01 34 48</p>
        <p>Correo: aventurabikepk@gmail.com</p>
    </div>

    <h2>Factura del Presupuesto #{{ $presupuesto->id }}</h2>
    
    <p><strong>Cliente:</strong> {{ $presupuesto->usuario_nombre }}</p>
    <p><strong>Bicicleta:</strong> {{ $presupuesto->bicicleta_nombre }}</p>
    <p><strong>Fecha:</strong> {{ date('d/m/Y', strtotime($presupuesto->created_at)) }}</p>

    <h3>Presupuesto</h3>
    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Descripción</th>
                <th>Cant.</th>
                <th>Precio</th>
                <th>IVA</th>
                <th>Importe</th>
                <th>% DTO</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
            <tr>
                <td>{{ $item->componente_id }}</td>
                <td>{{ $item->componente_nombre }}</td>
                <td>1</td>
                <td>{{ number_format($item->total_precio, 2) }}€</td>
                <td>21%</td>
                <td>{{ number_format($item->total_precio * 0.21, 2) }}€</td>
                <td>0%</td>
                <td>{{ number_format($item->total_precio * 1.21, 2) }}€</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p class="total"><strong>Subtotal:</strong> {{ number_format($presupuesto->precio_total, 2) }}€</p>
    <p class="total"><strong>Total IVA (21%):</strong> {{ number_format($presupuesto->precio_total * 0.21, 2) }}€</p>
    <p class="total"><strong>Total a Pagar:</strong> {{ number_format($presupuesto->precio_total * 1.21, 2) }}€</p>

</body>
</html>
