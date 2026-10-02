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
                <div style="font-size:12px; margin-top:4px; color: {{ !empty($conversationWindow['is_open']) ? '#166534' : '#92400e' }}; font-weight:600;">
                    @if(!empty($conversationWindow['is_open']))
                        Ventana 24h abierta hasta {{ optional($conversationWindow['expires_at'] ?? null)?->format('d/m H:i') }}
                    @else
                        Ventana 24h cerrada @if(!empty($conversationWindow['expires_at']))(cerro {{ optional($conversationWindow['expires_at'])?->format('d/m H:i') }})@endif
                    @endif
                </div>
            </div>

            <div class="p-3" style="background:#f0f2f5; border-bottom:1px solid #e5e7eb;">
                <div id="factura-chat-messages" class="rounded border bg-white p-3" style="height:42vh; min-height:380px; overflow-y:auto; width:100%;" data-partial-url="{{ route('whatsapp.show', ['phone' => $normalizedPhone, 'partial' => 1]) }}">
                    @include('whatsapp.partials.messages', ['messages' => $embeddedMessages])
                </div>
            </div>

            <form id="factura-chat-send-form" method="POST" action="{{ route('whatsapp.reply', ['phone' => $normalizedPhone]) }}" enctype="multipart/form-data" style="padding:14px; background:#fff;">
                @csrf
                @if(empty($conversationWindow['is_open']))
                    <div class="alert alert-warning" style="font-size:12px; margin-bottom:10px;">
                        La ventana de 24 horas esta cerrada. Para volver a abrir la conversacion debes enviar una plantilla.
                    </div>
                @endif
                <label for="factura-chat-body" style="display:block; font-weight:600; margin-bottom:6px;">Mensaje</label>
                <textarea id="factura-chat-body" name="body" rows="3" placeholder="Escribe un mensaje para este cliente..." style="width:100%; border:1px solid #d1d5db; border-radius:10px; padding:10px; resize:vertical;" {{ empty($conversationWindow['is_open']) ? 'disabled' : '' }}></textarea>

                <div style="display:flex; flex-wrap:wrap; gap:10px; align-items:end; margin-top:10px;">
                    <div style="flex:1 1 260px; min-width:220px;">
                        <label for="factura-chat-image" style="display:block; font-weight:600; margin-bottom:6px;">Imagen</label>
                        <input id="factura-chat-image" type="file" name="image" accept="image/jpeg,image/jpg,image/png,image/webp" style="width:100%;" {{ empty($conversationWindow['is_open']) ? 'disabled' : '' }}>
                    </div>
                    <div style="flex:0 0 auto;">
                        <button id="factura-chat-send-button" type="submit" class="app-btn bg-green-600 text-white hover:bg-green-700" {{ empty($conversationWindow['is_open']) ? 'disabled' : '' }}>
                            Enviar
                        </button>
                    </div>
                </div>

                <div class="mt-2 text-muted" style="font-size: 12px;">Puedes enviar texto o imagen (max 5MB).</div>
                <div id="factura-chat-send-status" class="mt-1" style="font-size:12px; color:#6b7280;"></div>
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

    document.addEventListener('DOMContentLoaded', function () {
        const chatBox = document.getElementById('factura-chat-messages');
        const sendForm = document.getElementById('factura-chat-send-form');
        const sendButton = document.getElementById('factura-chat-send-button');
        const sendStatus = document.getElementById('factura-chat-send-status');
        const bodyField = document.getElementById('factura-chat-body');
        const imageField = document.getElementById('factura-chat-image');
        if (!chatBox) {
            return;
        }

        const partialUrl = chatBox.dataset.partialUrl;
        if (!partialUrl) {
            return;
        }

        let refreshInFlight = false;
        let lastRenderedSignature = '';

        const getSignature = function (root) {
            const inner = root.querySelector('#chat-messages-inner');
            if (!inner) {
                return '';
            }

            const count = inner.dataset.count ?? '';
            const firstId = inner.dataset.firstId ?? '';
            const lastId = inner.dataset.lastId ?? '';

            return [count, firstId, lastId].join('|');
        };

        lastRenderedSignature = getSignature(chatBox);

        const refreshMessages = async function () {
            if (document.hidden || refreshInFlight) {
                return;
            }

            refreshInFlight = true;

            try {
                const url = new URL(partialUrl, window.location.origin);
                url.searchParams.set('_t', Date.now().toString());

                const response = await fetch(url.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    cache: 'no-store',
                });

                if (!response.ok) {
                    refreshInFlight = false;
                    return;
                }

                const html = await response.text();
                const parserHost = document.createElement('div');
                parserHost.innerHTML = html;
                const incomingSignature = getSignature(parserHost);

                if (incomingSignature === lastRenderedSignature) {
                    refreshInFlight = false;
                    return;
                }

                chatBox.innerHTML = html;
                lastRenderedSignature = incomingSignature;
            } catch (error) {
                // silencioso para reintentar en el siguiente ciclo
            } finally {
                refreshInFlight = false;
            }
        };

        refreshMessages();
        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) {
                refreshMessages();
            }
        });

        setInterval(refreshMessages, 1500);

        if (!sendForm) {
            return;
        }

        let sendInFlight = false;

        sendForm.addEventListener('submit', async function (event) {
            event.preventDefault();

            if (sendInFlight) {
                return;
            }

            sendInFlight = true;
            if (sendButton) {
                sendButton.disabled = true;
                sendButton.textContent = 'Enviando...';
            }
            if (sendStatus) {
                sendStatus.textContent = 'Enviando mensaje...';
                sendStatus.style.color = '#6b7280';
            }

            try {
                const formData = new FormData(sendForm);
                const response = await fetch(sendForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    let errorMessage = 'No se pudo enviar el mensaje.';
                    try {
                        const payload = await response.json();
                        if (payload && payload.message) {
                            errorMessage = payload.message;
                        }
                    } catch (parseError) {
                        // no-op
                    }
                    throw new Error(errorMessage);
                }

                if (bodyField) {
                    bodyField.value = '';
                }
                if (imageField) {
                    imageField.value = '';
                }

                if (sendStatus) {
                    sendStatus.textContent = 'Mensaje enviado.';
                    sendStatus.style.color = '#166534';
                }

                await refreshMessages();
            } catch (error) {
                if (sendStatus) {
                    sendStatus.textContent = error && error.message ? error.message : 'No se pudo enviar el mensaje.';
                    sendStatus.style.color = '#b91c1c';
                }
            } finally {
                sendInFlight = false;
                if (sendButton) {
                    sendButton.disabled = false;
                    sendButton.textContent = 'Enviar';
                }
            }
        });
    });
</script>



@endsection
