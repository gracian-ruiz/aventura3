<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Presupuesto #{{ $presupuesto->id }}</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            font-size: 10px;
            margin: 10px;
        }
        h2, h3 { text-align: center; margin: 10px 0; }
        h2 { font-size: 16px; }
        h3 { font-size: 14px; }
        p { margin: 5px 0; font-size: 10px; }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 15px;
            table-layout: fixed;
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 5px; 
            text-align: left;
            word-wrap: break-word;
            overflow-wrap: break-word;
            font-size: 9px;
            vertical-align: top;
        }
        th { 
            background-color: #f4f4f4; 
            font-weight: bold;
            font-size: 9px;
        }
        /* Anchos específicos para cada columna */
        th:nth-child(1), td:nth-child(1) { width: 20%; } /* Componente */
        th:nth-child(2), td:nth-child(2) { width: 11%; text-align: right; } /* Precio sin IVA */
        th:nth-child(3), td:nth-child(3) { width: 11%; text-align: right; } /* Precio con IVA */
        th:nth-child(4), td:nth-child(4) { width: 10%; text-align: center; } /* Descuento */
        th:nth-child(5), td:nth-child(5) { width: 12%; text-align: right; font-weight: bold; } /* Total Final */
        th:nth-child(6), td:nth-child(6) { width: 36%; } /* Descripción */
        
        .total { 
            font-weight: bold; 
            text-align: right; 
            margin: 5px 0;
            font-size: 11px;
        }
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .header-info {
            flex: 1;
            text-align: center;
        }
        .header-info p {
            margin: 2px 0;
            font-size: 9px;
        }
        .logo {
            height: 80px;
            width: auto;
        }
        .spacer {
            width: 80px;
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
        <div style="width: 100px;"></div>
    </div>

    <h2>Presupuesto #{{ $presupuesto->id }}</h2>
    
    <p><strong>Cliente:</strong> {{ $presupuesto->usuario_nombre }}</p>
    <p><strong>Bicicleta:</strong> {{ $presupuesto->marca }} {{ $presupuesto->bicicleta_nombre }}</p>
    <p><strong>Fecha:</strong> {{ date('d/m/Y', strtotime($presupuesto->created_at)) }}</p>

    <h3>Detalle del Presupuesto</h3>
    <table>
        <thead>
            <tr>
                <th>Componente</th>
                <th>Precio sin IVA</th>
                <th>Precio con IVA</th>
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
                $totalConDescuento = 0;
            @endphp

            @foreach ($items as $item)
                @php
                    $precioConIVA = (float) ($item->total_precio ?? 0);
                    $descuento = (float) ($item->descuento ?? 0);

                    // Precio sin IVA
                    $precioSinIVA = $precioConIVA / (1 + $iva / 100);

                    // IVA de la línea
                    $ivaImporte = $precioConIVA - $precioSinIVA;

                    // Precio final con descuento
                    $precioFinal = $precioConIVA - ($precioConIVA * $descuento / 100);

                    // Acumular totales
                    $totalSinIVA += $precioSinIVA;
                    $totalIVA += $ivaImporte;
                    $totalDescuento += ($precioConIVA - $precioFinal);
                    $totalConDescuento += $precioFinal;
                @endphp
                <tr>
                    <td>{{ str_contains(strtolower($item->componente_nombre), 'material') ? $item->texto : $item->componente_nombre }}</td>
                    <td style="text-align: right;">{{ number_format($precioSinIVA, 2, ',', '.') }}€</td>
                    <td style="text-align: right;">{{ number_format($precioConIVA, 2, ',', '.') }}€</td>
                    <td style="text-align: center;">-{{ number_format($descuento, 2, ',', '.') }}%</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($precioFinal, 2, ',', '.') }}€</td>
                    <td style="font-size: 8px;">{{ $item->texto }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="total"><strong>Total sin IVA:</strong> {{ number_format($totalSinIVA, 2, ',', '.') }}€</p>
    <p class="total"><strong>Total IVA ({{ $iva }}%):</strong> {{ number_format($totalIVA, 2, ',', '.') }}€</p>
    @if ($totalDescuento > 0)
        <p class="total"><strong>Descuento total aplicado:</strong> -{{ number_format($totalDescuento, 2, ',', '.') }}€</p>
    @endif
    <p class="total"><strong>Total a pagar:</strong> {{ number_format($totalConDescuento, 2, ',', '.') }}€</p>

</body>
</html>
