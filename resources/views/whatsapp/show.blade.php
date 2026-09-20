@extends('layouts.app')

@section('content')
<div class="min-h-[calc(100vh-5rem)] bg-[#dbe9df] px-3 py-3 sm:px-4 lg:px-6">
    <div class="mx-auto flex min-h-[calc(100vh-7rem)] max-w-7xl overflow-hidden rounded-[28px] border border-black/5 bg-[#f0f2f5] shadow-[0_18px_70px_rgba(16,24,40,0.12)]" style="background-image: radial-gradient(circle at top left, rgba(255,255,255,.65), transparent 32%), radial-gradient(circle at top right, rgba(255,255,255,.45), transparent 18%);">
        <aside class="hidden lg:flex w-[340px] shrink-0 flex-col border-r border-black/5 bg-white/90 backdrop-blur-sm">
            <div class="border-b border-black/5 px-5 py-4">
                <a href="{{ route('whatsapp.index') }}" class="text-sm font-semibold text-green-700 hover:text-green-900">← Volver a WhatsApp</a>
                <div class="mt-3 flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-600 text-sm font-bold text-white">{{ strtoupper(substr($conversationTitle, 0, 2)) }}</div>
                    <div>
                        <h1 class="text-lg font-bold text-gray-900 leading-tight">{{ $conversationTitle }}</h1>
                        <p class="text-xs text-gray-500">{{ $normalizedPhone }}</p>
                    </div>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto px-5 py-5">
                <div class="rounded-3xl bg-[#f7f8fa] p-4">
                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-400">Contacto</div>
                    <div class="mt-3 space-y-3 text-sm text-gray-700">
                        <div class="flex items-center justify-between gap-3 rounded-2xl bg-white px-4 py-3 shadow-sm">
                            <span class="text-gray-500">Cliente</span>
                            <span class="font-semibold text-gray-900">{{ $conversationUser?->name ?? 'No vinculado' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3 rounded-2xl bg-white px-4 py-3 shadow-sm">
                            <span class="text-gray-500">Bicicleta</span>
                            <span class="font-semibold text-gray-900">{{ $conversationBike?->nombre ?? 'No vinculada' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3 rounded-2xl bg-white px-4 py-3 shadow-sm">
                            <span class="text-gray-500">Cita</span>
                            <span class="font-semibold text-gray-900">{{ $conversationAppointment?->id ?? 'No vinculada' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3 rounded-2xl bg-white px-4 py-3 shadow-sm">
                            <span class="text-gray-500">Mensajes</span>
                            <span class="font-semibold text-gray-900"><span id="chat-message-count-sidebar">{{ $messages->count() }}</span></span>
                        </div>
                    </div>
                </div>

                <div class="mt-5 rounded-3xl bg-[#f7f8fa] p-4">
                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-400">Buscar</div>
                    <form method="GET" action="{{ route('whatsapp.show', ['phone' => $normalizedPhone]) }}" class="mt-3 space-y-3">
                        <input type="search" name="q" value="{{ $messageSearch }}" placeholder="Buscar en la conversación"
                            class="w-full rounded-2xl border-gray-300 bg-white shadow-sm focus:border-green-500 focus:ring-green-500">
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-[#00a884] px-4 py-3 text-sm font-semibold text-white hover:bg-[#0b8f72] transition-colors">
                            Buscar
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <section class="flex min-w-0 flex-1 flex-col bg-[#efeae2]" style="background-image: radial-gradient(circle at 20px 20px, rgba(255,255,255,.32) 0, rgba(255,255,255,.32) 2px, transparent 2px), radial-gradient(circle at 60px 60px, rgba(255,255,255,.18) 0, rgba(255,255,255,.18) 1px, transparent 1px); background-size: 80px 80px;">
            <div class="flex items-center justify-between gap-4 border-b border-black/5 bg-white/92 px-4 py-3 backdrop-blur-sm sm:px-5">
                <div class="flex min-w-0 items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-full bg-green-600 text-sm font-bold text-white">{{ strtoupper(substr($conversationTitle, 0, 2)) }}</div>
                    <div class="min-w-0">
                        <div class="truncate text-base font-semibold text-gray-900">{{ $conversationTitle }}</div>
                        <div class="truncate text-xs text-gray-500">{{ $normalizedPhone }} · <span id="chat-message-count">{{ $messages->count() }}</span> mensajes</div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="hidden sm:inline-flex rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700">WhatsApp Web</span>
                    <a href="{{ route('whatsapp.index') }}" class="inline-flex items-center rounded-full border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 lg:hidden">Chats</a>
                </div>
            </div>

            <div class="flex min-h-0 flex-1 flex-col">
                <div class="border-b border-black/5 bg-white/75 px-4 py-3 backdrop-blur-sm sm:px-5">
                    <form method="GET" action="{{ route('whatsapp.show', ['phone' => $normalizedPhone]) }}">
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <input type="search" name="q" value="{{ $messageSearch }}" placeholder="Buscar dentro de la conversación"
                                class="w-full rounded-2xl border-gray-300 bg-white shadow-sm focus:border-green-500 focus:ring-green-500">
                            <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white hover:bg-slate-800 transition-colors">
                                Buscar
                            </button>
                        </div>
                    </form>
                </div>

                <div id="chat-messages" class="flex-1 overflow-y-auto px-3 py-4 sm:px-5 sm:py-5">
                    @include('whatsapp.partials.messages', ['messages' => $messages])
                </div>

                <div class="border-t border-black/5 bg-[#f0f2f5] px-3 py-3 sm:px-5">
                    @if (session('success'))
                        <div class="mb-3 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('whatsapp.reply', ['phone' => $normalizedPhone]) }}" enctype="multipart/form-data" class="rounded-[28px] border border-black/5 bg-white px-3 py-3 shadow-sm sm:px-4">
                        @csrf

                        <div class="flex flex-col gap-3 lg:flex-row lg:items-end">
                            <div class="flex-1 min-w-0">
                                <textarea name="body" rows="3"
                                    class="w-full resize-none rounded-2xl border-gray-300 bg-[#f7f8fa] shadow-sm focus:border-green-500 focus:ring-green-500"
                                    placeholder="Escribe un mensaje o adjunta una imagen/PDF...">{{ old('body') }}</textarea>
                                @error('body')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex items-center gap-2">
                                <label class="inline-flex cursor-pointer items-center justify-center rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                    <input id="image" name="image" type="file" accept="image/jpeg,image/jpg,image/png,image/webp" class="hidden">
                                    Imagen
                                </label>
                                @error('image')
                                    <p class="text-xs text-red-600">{{ $message }}</p>
                                @enderror

                                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-[#00a884] px-5 py-3 font-semibold text-white hover:bg-[#0b8f72] transition-colors shadow-sm">
                                    Enviar
                                </button>
                            </div>
                        </div>

                        <div class="mt-3 flex items-center justify-between gap-3 text-xs text-gray-500">
                            <span>Texto, imagen o presupuesto PDF desde el chat.</span>
                            <span class="hidden sm:inline">Formato imagen: JPG, PNG, WEBP · 5MB</span>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const chatContainer = document.getElementById('chat-messages');
    const countEl = document.getElementById('chat-message-count');
    const countSidebarEl = document.getElementById('chat-message-count-sidebar');
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
            if (countSidebarEl && inner && inner.dataset.count) {
                countSidebarEl.textContent = inner.dataset.count;
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