@extends('layouts.app')

@section('content')

<h3 class="mt-4 mb-3">Detalles:</h3>
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
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
            @endphp
            @foreach ($items as $item)
                @php
                    $precioConIVA = $item->total_precio;

                    $precioSinIVA = $precioConIVA / (1 + $iva / 100);
                    $ivaImporte = $precioConIVA - $precioSinIVA;

                    $totalSinIVA += $precioSinIVA;
                    $totalIVA += $ivaImporte;
                @endphp
                <tr>
                    <td>{{ str_contains(strtolower($item->componente_nombre), 'material') ? $item->texto : $item->componente_nombre }}</td>
                    <td>{{ number_format($precioSinIVA, 2) }}€</td>
                    <td class="text-success fw-bold">{{ $item->total_precio  }}€</td>
                    <td class="text-danger">-{{ $item->descuento }}€</td>
                    <td class="text-success fw-bold">{{ $item->total_precio - $item->descuento  }}€</td>
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
        @if ($presupuesto->descuento > 0)
            <p><strong>Descuento total aplicado:</strong> -{{ $presupuesto->descuento }}€</p>
        @endif
        <p class="fs-4 mt-3"><strong>Total a pagar:</strong> <span class="badge bg-success">{{ $presupuesto->precio_total - $presupuesto->descuento }}€</span></p>
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

<style>
    /* Estilos generales de la tabla */
.table-responsive {
    margin-top: 20px;
}

.table-striped tbody tr:nth-of-type(odd) {
    background-color: #f9f9f9;
}

/* Estilo para los encabezados */
.table-dark th {
    background-color: #343a40;
    color: white;
    text-align: center;
}

/* Estilo para las celdas de la tabla */
.table td, .table th {
    text-align: right;
    padding: 10px;
}

/* Resaltar precios */
.table .text-success {
    color: #28a745;
    font-weight: bold;
}

.table .text-danger {
    color: #dc3545;
    font-weight: bold;
}

/* Estilo para los totales al final */
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

/* Estilo para la fila de resumen de totales */
.row.mt-4 {
    margin-top: 30px;
    font-size: 1.2rem;
    padding-top: 20px;
}

.row.mt-4 p {
    font-size: 1rem;
    font-weight: normal;
}

.col-md-6 {
    font-size: 1rem;
}

.col-md-6 p {
    margin-bottom: 10px;
}

/* Resaltar precios totales y descuentos */
.row.mt-4 p strong {
    font-weight: bold;
    font-size: 1.1rem;
}

.text-success {
    color: #28a745;
}

.text-danger {
    color: #dc3545;
}

/* Estilo de los botones */
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

.fas {
    margin-right: 5px;
}

/* Estilo para los botones en fila */
.d-flex {
    display: flex;
    justify-content: center;
    gap: 15px;
}

/* Espaciado adicional para el contenedor */
.container {
    margin-top: 20px;
    padding: 20px;
}

/* Aseguramos que el texto esté bien alineado y tenga suficiente espacio */
.text-md-end {
    text-align: right;
}

</style>

@endsection
