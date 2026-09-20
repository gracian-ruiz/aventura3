@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex items-center justify-between gap-4 mb-6">
        <div>
            <a href="{{ route('whatsapp.index') }}" class="text-sm text-green-700 hover:text-green-900">← Volver a WhatsApp</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-2">{{ $conversationTitle }}</h1>
            <div class="mt-1 flex flex-wrap items-center gap-3 text-sm text-gray-500">
                <span>{{ $normalizedPhone }}</span>
                @if($conversationBike)
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">{{ $conversationBike->marca ?? '' }} {{ $conversationBike->nombre }}</span>
                @endif
                @if($conversationAppointment)
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">Cita #{{ $conversationAppointment->id }}</span>
                @endif
            </div>
            <p class="text-sm text-gray-500 mt-1">Vista de hilo con mensajes del cliente, respuestas del taller y estados de entrega.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-200 bg-gradient-to-r from-green-50 via-white to-slate-50 flex items-center justify-between gap-4">
                <div>
                    <div class="font-semibold text-gray-900">Mensajes</div>
                    <div class="text-xs text-gray-500"><span id="chat-message-count">{{ $messages->count() }}</span> registros en la conversación</div>
                </div>
                <div class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                    WhatsApp
                </div>
            </div>

            <div class="px-5 py-4 border-b border-gray-100 bg-white">
                <form method="GET" action="{{ route('whatsapp.show', ['phone' => $normalizedPhone]) }}">
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <input type="search" name="q" value="{{ $messageSearch }}" placeholder="Buscar dentro de la conversación"
                            class="w-full rounded-2xl border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                        <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white hover:bg-slate-800 transition-colors">
                            Buscar
                        </button>
                    </div>
                </form>
            </div>

            <div id="chat-messages" class="p-4 sm:p-5 max-h-[70vh] overflow-y-auto bg-[linear-gradient(180deg,#f8fafc_0%,#ffffff_14%,#f8fafc_100%)]">
                @include('whatsapp.partials.messages', ['messages' => $messages])
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900">Responder</h2>
            <p class="text-sm text-gray-600 mt-1">Envía un mensaje rápido al cliente.</p>

            @if (session('success'))
                <div class="mt-4 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('whatsapp.reply', ['phone' => $normalizedPhone]) }}" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf
                <textarea name="body" rows="7"
                    class="w-full rounded-2xl border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                    placeholder="Escribe tu respuesta (opcional si adjuntas imagen)...">{{ old('body') }}</textarea>

                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-2">Imagen (opcional)</label>
                    <input id="image" name="image" type="file" accept="image/jpeg,image/jpg,image/png,image/webp"
                        class="block w-full text-sm text-gray-700 file:mr-3 file:rounded-xl file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200">
                    <p class="mt-1 text-xs text-gray-500">Formatos: JPG, PNG, WEBP. Tamaño máximo: 5MB.</p>
                </div>

                @error('body')
                    <p class="text-xs text-red-600">{{ $message }}</p>
                @enderror
                @error('image')
                    <p class="text-xs text-red-600">{{ $message }}</p>
                @enderror

                <button type="submit"
                    class="w-full inline-flex items-center justify-center rounded-2xl bg-green-600 px-4 py-3 font-semibold text-white hover:bg-green-700 transition-colors shadow-sm">
                    Enviar mensaje / imagen
                </button>
            </form>

            <div class="mt-6 rounded-2xl bg-slate-50 border border-slate-200 p-4 text-sm text-gray-600 space-y-3">
                <div><span class="font-semibold text-gray-900">Cliente:</span> {{ $conversationUser?->name ?? 'No vinculado' }}</div>
                <div><span class="font-semibold text-gray-900">Bicicleta:</span> {{ $conversationBike?->nombre ?? 'No vinculada' }}</div>
                <div><span class="font-semibold text-gray-900">Cita:</span> {{ $conversationAppointment?->id ?? 'No vinculada' }}</div>
                <div><span class="font-semibold text-gray-900">Teléfono:</span> {{ $normalizedPhone }}</div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const chatContainer = document.getElementById('chat-messages');
    const countEl = document.getElementById('chat-message-count');
    const bodyField = document.querySelector('textarea[name="body"]');
    const imageField = document.querySelector('input[name="image"]');
    if (!chatContainer) {
        return;
    }

    // Arranca con scroll al final del hilo.
    chatContainer.scrollTop = chatContainer.scrollHeight;

    const baseUrl = new URL(window.location.href);
    const pollUrl = new URL(baseUrl.toString());
    pollUrl.searchParams.set('partial', '1');

    const refreshMessages = async function () {
        if (document.hidden) {
            return;
        }

        // Evita saltos mientras se escribe o se prepara una imagen para enviar.
        if ((bodyField && document.activeElement === bodyField) || (imageField && imageField.files && imageField.files.length > 0)) {
            return;
        }

        const distanceToBottom = chatContainer.scrollHeight - chatContainer.scrollTop - chatContainer.clientHeight;
        const shouldStickBottom = distanceToBottom < 120;

        try {
            const response = await fetch(pollUrl.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                return;
            }

            const html = await response.text();
            chatContainer.innerHTML = html;

            const inner = chatContainer.querySelector('#chat-messages-inner');
            if (countEl && inner && inner.dataset.count) {
                countEl.textContent = inner.dataset.count;
            }

            if (shouldStickBottom) {
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }
        } catch (error) {
            // Silencioso: el siguiente ciclo volvera a intentar.
        }
    };

    setInterval(refreshMessages, 7000);
});
</script>
@endsection