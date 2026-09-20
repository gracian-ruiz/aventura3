<div
    id="chat-messages-inner"
    data-count="{{ $messages->count() }}"
    data-first-id="{{ optional($messages->first())->id }}"
    data-last-id="{{ optional($messages->last())->id }}"
    class="space-y-3"
>
    @forelse($messages as $message)
        @php
            $isStatus = $message->message_type === 'status';
            $isDocument = $message->message_type === 'document';
            $isImage = $message->message_type === 'image';
            $isPdfTemplate = $message->message_type === 'template' && (
                data_get($message->payload, 'document_kind') === 'pdf'
                || data_get($message->payload, 'document_public_url')
            );
            $showDocument = $isDocument || $isPdfTemplate;
            $timestamp = optional($message->received_at ?? $message->sent_at)->format('d/m/Y H:i');
            $directionLabel = $message->direction === 'outbound' ? 'Taller' : 'Cliente';
            $hasDocumentMedia = (bool) data_get($message->payload, 'document.id')
                || (bool) data_get($message->payload, 'local_document_path')
                || (bool) data_get($message->payload, 'document_public_url');
            $documentUrl = $showDocument
                ? (
                    $isPdfTemplate
                        ? (string) data_get($message->payload, 'document_public_url', '')
                        : route('whatsapp.media', ['message' => $message->id])
                )
                : null;
            $documentFilename = $showDocument
                ? (string) (data_get($message->payload, 'document_filename') ?: data_get($message->payload, 'document.filename') ?: 'documento.pdf')
                : null;
            $statusTone = match ($message->status ?? $message->body) {
                'sent' => 'bg-slate-100 text-slate-700 border-slate-200',
                'delivered' => 'bg-blue-100 text-blue-700 border-blue-200',
                'read' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                'failed' => 'bg-red-100 text-red-700 border-red-200',
                default => 'bg-gray-100 text-gray-700 border-gray-200',
            };
            $hasImageMedia = (bool) data_get($message->payload, 'image.id') || (bool) data_get($message->payload, 'local_image_path');
            $imageUrl = ($isImage && $hasImageMedia) ? route('whatsapp.media', ['message' => $message->id]) : null;
            $caption = $isImage ? preg_replace('/^\[imagen\]\s*/i', '', (string) ($message->body ?? '')) : null;
        @endphp

        @if($isStatus)
            <div class="flex justify-center" data-message-id="{{ $message->id }}" data-message-type="{{ $message->message_type }}" data-direction="{{ $message->direction }}">
                <div class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-xs font-semibold {{ $statusTone }} shadow-sm backdrop-blur-sm">
                    <span class="uppercase tracking-[0.2em]">{{ $message->status ?? $message->body ?? 'status' }}</span>
                    <span class="text-[11px] opacity-80">{{ $timestamp }}</span>
                </div>
            </div>
        @else
            <div class="flex {{ $message->direction === 'outbound' ? 'justify-end' : 'justify-start' }}" data-message-id="{{ $message->id }}" data-message-type="{{ $message->message_type }}" data-direction="{{ $message->direction }}">
                <div class="max-w-[88%] sm:max-w-[78%] rounded-[18px] px-4 py-3 shadow-sm border {{ $isDocument ? 'bg-amber-50 text-amber-950 border-amber-200' : ($message->direction === 'outbound' ? 'bg-[#d9fdd3] text-gray-900 border-[#d9fdd3]' : 'bg-white text-gray-900 border-gray-200') }}">
                    <div class="flex items-center justify-between gap-3 mb-2">
                        <span class="text-[11px] font-semibold uppercase tracking-[0.18em] {{ $isDocument ? 'text-amber-700' : ($message->direction === 'outbound' ? 'text-[#0b7f5f]' : 'text-gray-400') }}">
                            {{ $directionLabel }}
                        </span>
                        <span class="text-[11px] {{ $isDocument ? 'text-amber-700' : ($message->direction === 'outbound' ? 'text-[#0b7f5f]/90' : 'text-gray-400') }}">
                            {{ $timestamp }}
                        </span>
                    </div>
                    @if($showDocument && ($isPdfTemplate || $hasDocumentMedia))
                        <a href="{{ $documentUrl }}" target="_blank" rel="noopener noreferrer" class="mb-2 inline-flex items-center rounded-full bg-amber-200 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-800 hover:bg-amber-300">
                            PDF enviado: {{ $documentFilename }}
                        </a>
                        <div class="mt-2 overflow-hidden rounded-2xl border border-amber-200 bg-white shadow-inner">
                            <iframe src="{{ $documentUrl }}" class="h-72 w-full" title="Vista previa PDF"></iframe>
                        </div>
                    @elseif($showDocument)
                        <div class="mb-2 inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-600">
                            PDF no disponible
                        </div>
                    @endif

                    @if($isImage && $imageUrl)
                        <a href="{{ $imageUrl }}" target="_blank" rel="noopener noreferrer" class="block mb-2">
                            <img src="{{ $imageUrl }}" alt="Imagen de WhatsApp" class="rounded-xl max-h-72 w-auto border border-black/10 object-contain bg-white">
                        </a>
                    @elseif($isImage)
                        <div class="mb-2 inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-600">
                            Imagen no disponible
                        </div>
                    @endif

                    @if(!$isImage || ($caption !== null && trim($caption) !== ''))
                        <div class="text-sm leading-6 whitespace-pre-line">{{ $isImage ? $caption : ($message->body ?? 'Sin contenido') }}</div>
                    @endif
                </div>
            </div>
        @endif
    @empty
        <div class="text-sm text-gray-500 bg-white border border-dashed border-gray-300 rounded-2xl p-6 text-center">No hay mensajes para este número todavía.</div>
    @endforelse
</div>
