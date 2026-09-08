@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex items-center justify-between gap-4 mb-6">
        <div>
            <a href="{{ route('whatsapp.index') }}" class="text-sm text-green-700 hover:text-green-900">← Volver a WhatsApp</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-2">Conversación {{ $normalizedPhone }}</h1>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 font-semibold text-gray-800">Mensajes</div>
            <div class="p-4 space-y-3 max-h-[70vh] overflow-y-auto">
                @forelse($messages as $message)
                    <div class="flex {{ $message->direction === 'outbound' ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[80%] rounded-2xl px-4 py-3 {{ $message->direction === 'outbound' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-900' }}">
                            <div class="text-sm whitespace-pre-line">{{ $message->body ?? $message->status ?? 'Sin contenido' }}</div>
                            <div class="mt-2 text-[11px] opacity-75 flex items-center justify-between gap-3">
                                <span>{{ strtoupper($message->direction) }}</span>
                                <span>{{ optional($message->received_at ?? $message->sent_at)->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-sm text-gray-500">No hay mensajes para este número todavía.</div>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900">Responder</h2>
            <p class="text-sm text-gray-600 mt-1">Envía un mensaje rápido al cliente.</p>

            <form method="POST" action="{{ route('whatsapp.reply', ['phone' => $normalizedPhone]) }}" class="mt-4 space-y-4">
                @csrf
                <textarea name="body" rows="8" required
                    class="w-full rounded-xl border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                    placeholder="Escribe tu respuesta...">{{ old('body') }}</textarea>

                <button type="submit"
                    class="w-full inline-flex items-center justify-center rounded-xl bg-green-600 px-4 py-3 font-semibold text-white hover:bg-green-700 transition-colors">
                    Enviar mensaje
                </button>
            </form>

            <div class="mt-6 text-sm text-gray-600 space-y-2">
                <div><span class="font-semibold text-gray-900">Cliente:</span> {{ $messages->first()?->user?->name ?? 'No vinculado' }}</div>
                <div><span class="font-semibold text-gray-900">Bicicleta:</span> {{ $messages->first()?->bike?->nombre ?? 'No vinculada' }}</div>
                <div><span class="font-semibold text-gray-900">Cita:</span> {{ $messages->first()?->appointment?->id ?? 'No vinculada' }}</div>
            </div>
        </div>
    </div>
</div>
@endsection