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

@if(empty($normalizedPhone))
    <div class="alert alert-warning mt-4">
        El cliente no tiene telefono valido para WhatsApp.
    </div>
@endif

<div class="alert alert-light border mt-4">
    <strong>Cliente:</strong> {{ $presupuesto->usuario_nombre }}<br>
    <strong>Telefono WhatsApp:</strong> {{ $normalizedPhone ?: 'No disponible' }}<br>
    <strong>Bicicleta del presupuesto:</strong> {{ trim(($presupuesto->bicicleta_marca ?? '') . ' ' . ($presupuesto->bicicleta_nombre ?? '')) }}
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

@if(!empty($normalizedPhone))
    <div class="mt-5">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <h4 class="app-title mb-0">Chat de WhatsApp del cliente</h4>
        </div>

        <div class="rounded border bg-white overflow-hidden" style="width:100%;">
            <div class="px-3 py-2" style="background:#e7f7ee; border-bottom:1px solid #d1f0df;">
                <div style="font-weight:700; color:#166534;">{{ $presupuesto->usuario_nombre }}</div>
                <div style="font-size:12px; color:#4b5563;">{{ $normalizedPhone }} · {{ trim(($presupuesto->bicicleta_marca ?? '') . ' ' . ($presupuesto->bicicleta_nombre ?? '')) }}</div>
            </div>

            <div class="p-3" style="background:#f0f2f5; border-bottom:1px solid #e5e7eb;">
                <div class="rounded border bg-white p-3" style="height:42vh; min-height:380px; overflow-y:auto; width:100%;">
                    @include('whatsapp.partials.messages', ['messages' => $embeddedMessages])
                </div>
            </div>

            <form method="POST" action="{{ route('whatsapp.reply', ['phone' => $normalizedPhone]) }}" enctype="multipart/form-data" style="padding:14px; background:#fff;">
                @csrf
                <label for="factura-chat-body" style="display:block; font-weight:600; margin-bottom:6px;">Mensaje</label>
                <textarea id="factura-chat-body" name="body" rows="3" placeholder="Escribe un mensaje para este cliente..." style="width:100%; border:1px solid #d1d5db; border-radius:10px; padding:10px; resize:vertical;"></textarea>

                <div style="display:flex; flex-wrap:wrap; gap:10px; align-items:end; margin-top:10px;">
                    <div style="flex:1 1 260px; min-width:220px;">
                        <label for="factura-chat-image" style="display:block; font-weight:600; margin-bottom:6px;">Imagen</label>
                        <input id="factura-chat-image" type="file" name="image" accept="image/jpeg,image/jpg,image/png,image/webp" style="width:100%;">
                    </div>
                    <div style="flex:0 0 auto;">
                        <button type="submit" class="app-btn bg-green-600 text-white hover:bg-green-700">
                            Enviar
                        </button>
                    </div>
                </div>

                <div class="mt-2 text-muted" style="font-size: 12px;">Puedes enviar texto o imagen (max 5MB).</div>
            </form>
        </div>

        <div class="mt-4">
            <form method="POST" action="{{ route('whatsapp.template', ['phone' => $normalizedPhone]) }}" class="d-inline-block mb-3">
                @csrf
                <input type="hidden" name="template_key" value="saludo">
                <button type="submit" class="app-btn bg-blue-600 text-white hover:bg-blue-700">
                    <i class="fab fa-whatsapp"></i> Saludos
                </button>
            </form>

            <div>
                <a href="{{ route('presupuesto.enviar', ['clienteId' => $presupuesto->usuario_id, 'presupuestoId' => $presupuesto->id]) }}" class="app-btn bg-green-600 text-white hover:bg-green-700">
                <i class="fab fa-whatsapp"></i> Enviar presupuesto por WhatsApp
                </a>
            </div>
        </div>
    </div>
@endif

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
