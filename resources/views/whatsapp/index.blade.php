@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">WhatsApp</h1>
            <p class="text-sm text-gray-600">Conversaciones recientes con clientes y mecánica de respuesta rápida.</p>
        </div>

        <form method="GET" class="w-full sm:w-96">
            <input type="search" name="search" value="{{ $search }}" placeholder="Buscar por teléfono, nombre o mensaje"
                class="w-full rounded-xl border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1 bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 font-semibold text-gray-800">Conversaciones</div>
            <div class="divide-y divide-gray-100 max-h-[70vh] overflow-y-auto">
                @forelse($conversations as $conversation)
                    @php($phone = $conversation['phone'] ?? '')
                    <a href="{{ route('whatsapp.show', ['phone' => $phone]) }}" class="block px-4 py-4 hover:bg-green-50 transition-colors">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="font-semibold text-gray-900">
                                    {{ $conversation['user']->name ?? $phone }}
                                </div>
                                <div class="text-sm text-gray-500">{{ $phone }}</div>
                            </div>
                            <span class="text-xs rounded-full bg-green-100 text-green-700 px-2 py-1 whitespace-nowrap">
                                {{ $conversation['count'] }} mensajes
                            </span>
                        </div>
                        <p class="mt-2 text-sm text-gray-600 line-clamp-2">
                            {{ $conversation['last_message']->body ?? 'Sin contenido' }}
                        </p>
                        <p class="mt-2 text-xs text-gray-400">
                            {{ optional($conversation['last_message']->received_at ?? $conversation['last_message']->sent_at)->format('d/m/Y H:i') }}
                        </p>
                    </a>
                @empty
                    <div class="p-6 text-sm text-gray-500">Todavía no hay conversaciones registradas.</div>
                @endforelse
            </div>
        </div>

        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-200 p-6 flex items-center justify-center min-h-[50vh]">
            <div class="text-center max-w-md">
                <div class="text-5xl mb-4">💬</div>
                <h2 class="text-xl font-semibold text-gray-900 mb-2">Abre una conversación</h2>
                <p class="text-sm text-gray-600">Selecciona un cliente para ver el hilo completo y responder desde el panel.</p>
            </div>
        </div>
    </div>
</div>
@endsection