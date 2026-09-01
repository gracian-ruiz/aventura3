@extends('layouts.app')

@section('content')

<h3 class="app-title mt-4 mb-3">Detalles:</h3>
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>Componente</th>
                <th>Precio sin IVA</th>
                <th>Mano de Obra</th>
                <th>Material</th>
                <th>Descuento (%)</th>
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
                    $manoObra = (float) ($item->total_precio ?? 0);
                    $material = (float) ($item->precio_material ?? 0);
                    $descuentoPct = (float) ($item->descuento ?? 0);
                    $precioBruto = $manoObra + $material;
                    $precioSinIVA = $precioBruto / (1 + $iva / 100);
                    $ivaImporte = $precioBruto - $precioSinIVA;
                    $descuentoImporte = round($precioBruto * ($descuentoPct / 100), 2);
                    $precioConDescuento = max($precioBruto - $descuentoImporte, 0);

                    // Acumular valores para totales
                    $totalSinIVA += $precioSinIVA;
                    $totalIVA += $ivaImporte;
                    $totalDescuento += $descuentoImporte;
                    $totalConDescuento += $precioConDescuento;
                @endphp
                <tr>
                    <td>{{ str_contains(strtolower($item->componente_nombre), 'material') ? $item->texto : $item->componente_nombre }}</td>
                    <td>{{ number_format($precioSinIVA, 2) }}€</td>
                    <td class="text-success fw-bold">{{ number_format($manoObra, 2) }}€</td>
                    <td class="text-success fw-bold">{{ number_format($material, 2) }}€</td>
                    <td class="text-danger">{{ number_format($descuentoPct, 2) }}% (-{{ number_format($descuentoImporte, 2) }}€)</td>
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
<div class="mt-4 d-flex justify-content-center gap-3 flex-wrap">
    <a href="{{ route('presupuestos.index', $indexContext ?? []) }}" class="app-btn bg-gray-500 text-white hover:bg-gray-600">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
    <a href="{{ route('presupuestos.pdf', $presupuesto->id) }}" class="app-btn app-btn-pdf">
        <i class="fas fa-file-pdf"></i> Descargar PDF
    </a>            
</div>

<br>
<button onclick="copiarMensaje()" class="app-btn app-btn-danger app-floating-action">
    COPIAR TEXTO AQUI PARA PONERSELO AL CLIENTE
</button>
<!-- Mensaje para enviar al cliente -->
<div class="alert alert-info mt-5 p-4 relative app-note-panel">
    <strong>Mensaje para enviar al cliente:</strong><br><br>
    <div id="mensaje-cliente">
        {!! nl2br(e($mensaje)) !!}
    </div>
    <!-- Botón copiar -->
</div>

<!-- Script para copiar -->
<script>
    function copiarMensaje() {
        const mensaje = document.getElementById('mensaje-cliente').innerText;

        navigator.clipboard.writeText(mensaje)
            .then(() => {
               
            })
            .catch(err => {
                console.error('Error al copiar: ', err);
                alert('No se pudo copiar el mensaje ❌');
            });
    }
</script>



@endsection
