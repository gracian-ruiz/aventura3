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
                    <div class="text-xs text-gray-500">{{ $messages->count() }} registros en la conversación</div>
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

            <div class="p-4 sm:p-5 space-y-4 max-h-[70vh] overflow-y-auto bg-[linear-gradient(180deg,#f8fafc_0%,#ffffff_14%,#f8fafc_100%)]">
                @forelse($messages as $message)
                    @php
                        $isStatus = $message->message_type === 'status';
                        $isDocument = $message->message_type === 'document';
                        $timestamp = optional($message->received_at ?? $message->sent_at)->format('d/m/Y H:i');
                        $directionLabel = $message->direction === 'outbound' ? 'Taller' : 'Cliente';
                        $statusTone = match ($message->status ?? $message->body) {
                            'sent' => 'bg-slate-100 text-slate-700 border-slate-200',
                            'delivered' => 'bg-blue-100 text-blue-700 border-blue-200',
                            'read' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                            'failed' => 'bg-red-100 text-red-700 border-red-200',
                            default => 'bg-gray-100 text-gray-700 border-gray-200',
                        };
                    @endphp

                    @if($isStatus)
                        <div class="flex justify-center">
                            <div class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-xs font-semibold {{ $statusTone }} shadow-sm">
                                <span class="uppercase tracking-[0.2em]">{{ $message->status ?? $message->body ?? 'status' }}</span>
                                <span class="text-[11px] opacity-80">{{ $timestamp }}</span>
                            </div>
                        </div>
                    @else
                        <div class="flex {{ $message->direction === 'outbound' ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[88%] sm:max-w-[78%] rounded-[22px] px-4 py-3 shadow-sm border {{ $isDocument ? 'bg-amber-50 text-amber-950 border-amber-200' : ($message->direction === 'outbound' ? 'bg-green-600 text-white border-green-500' : 'bg-white text-gray-900 border-gray-200') }}">
                                <div class="flex items-center justify-between gap-3 mb-2">
                                    <span class="text-[11px] font-semibold uppercase tracking-[0.18em] {{ $isDocument ? 'text-amber-700' : ($message->direction === 'outbound' ? 'text-green-100' : 'text-gray-400') }}">
                                        {{ $directionLabel }}
                                    </span>
                                    <span class="text-[11px] {{ $isDocument ? 'text-amber-700' : ($message->direction === 'outbound' ? 'text-green-100/90' : 'text-gray-400') }}">
                                        {{ $timestamp }}
                                    </span>
                                </div>
                                @if($isDocument)
                                    <div class="mb-2 inline-flex items-center rounded-full bg-amber-200 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-800">
                                        PDF enviado
                                    </div>
                                @endif
                                <div class="text-sm leading-6 whitespace-pre-line">{{ $message->body ?? 'Sin contenido' }}</div>
                            </div>
                        </div>
                    @endif
                @empty
                    <div class="text-sm text-gray-500 bg-white border border-dashed border-gray-300 rounded-2xl p-6 text-center">No hay mensajes para este número todavía.</div>
                @endforelse
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

            <form method="POST" action="{{ route('whatsapp.reply', ['phone' => $normalizedPhone]) }}" class="mt-4 space-y-4">
                @csrf
                <textarea name="body" rows="8" required
                    class="w-full rounded-2xl border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                    placeholder="Escribe tu respuesta...">{{ old('body') }}</textarea>

                <button type="submit"
                    class="w-full inline-flex items-center justify-center rounded-2xl bg-green-600 px-4 py-3 font-semibold text-white hover:bg-green-700 transition-colors shadow-sm">
                    Enviar mensaje
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
@endsection