@extends('layouts.app')

@section('content')

<h3 class="mt-4 mb-3">Detalles:</h3>
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
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
                    // Calcular el precio con IVA y sin IVA
                    $precioConIVA = $item->total_precio;
                    $precioSinIVA = $precioConIVA / (1 + $iva / 100);
                    $ivaImporte = $precioConIVA - $precioSinIVA;

                    // Calcular el descuento sobre el precio con IVA
                    $descuento = $item->descuento ?? 0; // Descuento en porcentaje
                    $descuentoMonto = ($precioConIVA * $descuento) / 100;
                    $precioConDescuento = $precioConIVA - $descuentoMonto;

                    // Acumular valores para totales
                    $totalSinIVA += $precioSinIVA;
                    $totalIVA += $ivaImporte;
                    $totalDescuento += $descuentoMonto;
                    $totalConDescuento += $precioConDescuento;
                @endphp
                <tr>
                    <td>{{ str_contains(strtolower($item->componente_nombre), 'material') ? $item->texto : $item->componente_nombre }}</td>
                    <td>{{ number_format($precioSinIVA, 2) }}€</td>
                    <td class="text-success fw-bold">{{ number_format($precioConIVA, 2) }}€</td>
                    <td class="text-danger">-{{ number_format($descuento, 2) }}%</td>
                    <td class="text-success fw-bold">{{ number_format($precioConDescuento, 2) }}€</td>
                    <td>{{ $item->texto }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Resumen final -->
<div class="row mt-4">
    <div class="col-md-6 offset-md-3">
        <p><strong>Total sin IVA:</strong> {{ number_format($totalSinIVA, 2) }}€</p>
        <p><strong>Total IVA ({{ $iva }}%):</strong> {{ number_format($totalIVA, 2) }}€</p>
        <p><strong>Descuento total aplicado:</strong> -{{ number_format($totalDescuento, 2) }}€</p>
        <p class="fs-4 mt-3"><strong>Total a pagar:</strong> <span class="badge bg-success">{{ number_format($totalConDescuento, 2) }}€</span></p>
    </div>
</div>

<!-- Botones -->
<div class="mt-4 d-flex justify-content-center gap-3">
    <a href="{{ route('presupuestos.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
    <a href="{{ route('presupuestos.pdf', $presupuesto->id) }}" class="btn text-white" style="background-color: #E1251B;">
        <i class="fas fa-file-pdf"></i> Descargar PDF
    </a>            
</div>

<br>
<!-- Mensaje para enviar al cliente -->
<div class="alert alert-info mt-5 p-4" style="font-size: 1.3rem; line-height: 1.8; background-color: #e9f7fe; border-left: 6px solid #17a2b8;">
    <strong>Mensaje para enviar al cliente:</strong><br><br>
    {!! nl2br(e($mensaje)) !!}
</div>


<style>
    .table-responsive {
        margin-top: 20px;
    }

    .table-striped tbody tr:nth-of-type(odd) {
        background-color: #f9f9f9;
    }

    .table-dark th {
        background-color: #343a40;
        color: white;
        text-align: center;
    }

    .table td, .table th {
        text-align: right;
        padding: 10px;
    }

    .text-success {
        color: #28a745;
        font-weight: bold;
    }

    .text-danger {
        color: #dc3545;
        font-weight: bold;
    }

    .fs-4 {
        font-size: 1.25rem;
        font-weight: bold;
    }

    .badge.bg-success {
        background-color: #28a745;
        color: white;
        font-size: 1.2rem;
        padding: 3px 15px;
        border-radius: 10px;
    }

    .row.mt-4 {
        margin-top: 30px;
        font-size: 1.2rem;
        padding-top: 20px;
    }

    .col-md-6 p {
        margin-bottom: 10px;
    }

    .btn {
        font-size: 1rem;
        padding: 10px 20px;
        border-radius: 5px;
    }

    .btn-secondary {
        background-color: #6c757d;
        color: white;
    }

    .btn-secondary:hover {
        background-color: #5a6268;
    }

    .btn.text-white {
        background-color: #E1251B;
        color: white;
    }

    .btn.text-white:hover {
        background-color: #c70000;
    }

    .d-flex {
        display: flex;
        justify-content: center;
        gap: 15px;
    }
</style>

@endsection
