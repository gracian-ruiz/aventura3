<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Presupuesto #{{ $presupuesto->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; }
        h2, h3 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
        .total { font-weight: bold; text-align: right; }
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .header-info {
            flex: 1;
            text-align: center;
        }
        .logo {
            height: 100px;
            width: auto;
        }
    </style>
</head>
<body>

    <div class="header-container">
        <div class="logo-container">
            <img src="{{ public_path('images/logo_taller_1.png') }}" alt="Logo Taller" class="logo">
        </div>
        <div class="header-info">
            <h2>AVENTURA BIKE PK S.L.</h2>
            <p>Cif/Nif: B-19781212</p>
            <p>Avenida del Mediterráneo, 222, 04006 - Almería</p>
            <p>Tel/Fax: 950 01 34 48</p>
            <p>Correo: aventurabikepk@gmail.com</p>
        </div>
        <div style="width: 100px;"><!-- espacio vacío para alinear bien --></div>
    </div>

    <h2>Presupuesto #{{ $presupuesto->id }}</h2>
    
    <p><strong>Cliente:</strong> {{ $presupuesto->usuario_nombre }}</p>
    <p><strong>Bicicleta:</strong>{{ $presupuesto->marca }}  {{ $presupuesto->bicicleta_nombre }}</p>
    <p><strong>Fecha:</strong> {{ date('d/m/Y', strtotime($presupuesto->created_at)) }}</p>

    <h3>Presupuesto</h3>
    <table>
        <thead>
            <tr>
                <th>Componente</th>
                <th>Precio sin IVA</th>
                <th>precio con IVA</th>
                <th>Descuento</th>
                <th>Total Final</th>
                <th>Descripción</th>
            </tr>
        </thead>
        <tbody>
            @php
                $iva = 21;
                $totalSinIVA = 0;
                $totalIVA = 0;
                $totalDescuento = 0;
                $precioTotal = $presupuesto->precio_total;
                $descuentoTotal = $presupuesto->descuento ?? 0;
                $sumaPreciosConIVA = $items->sum('total_precio');
                $precioproducto=0;
                $precioproductototal=0;
                $preciodescuentotototal=0;
            @endphp
            @foreach ($items as $item)
                @php
                    $precioConIVA = $item->total_precio;

                    $precioSinIVA = is_numeric($precioConIVA) ? $precioConIVA / (1 + $iva / 100) : 0;

                    $ivaImporte = $precioConIVA - $precioSinIVA;

                    $totalSinIVA += $precioSinIVA;
                    $totalIVA += $ivaImporte;
                    $precioproducto = number_format($item->total_precio - ($item->total_precio * $item->descuento / 100), 2);
                    $precioproductototal += $precioproducto;
                    $preciodescuentotototal += $item->total_precio - $precioproducto;

                @endphp
                <tr>
                    <td>{{ str_contains(strtolower($item->componente_nombre), 'material') ? $item->texto : $item->componente_nombre }}</td>
                    <td>{{ number_format($precioSinIVA, 2) }}€</td>
                    <td class="text-success fw-bold">{{ $item->total_precio  }}€</td>
                    <td class="text-danger">-{{ $item->descuento }}%</td>
                    <td class="text-success fw-bold">
                        {{-- Aplicar el descuento como porcentaje al precio total --}}
                        {{ $precioproducto }}€
                    </td>
                    <td>{{ $item->texto }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="total"><strong>Total sin IVA:</strong> {{ number_format($totalSinIVA, 2) }}€</p>
    <p class="total"><strong>Total IVA ({{ $iva }}%):</strong> {{ number_format($totalIVA, 2) }}€</p>
    @if ($presupuesto->descuento > 0)
        <p class="total"><strong>Descuento total aplicado:</strong> -{{ $preciodescuentotototal }}€</p>
        @endif
    <p class="total"><strong>Total a Pagar:</strong> {{ $precioproductototal }}€</p>


</body>
</html>
