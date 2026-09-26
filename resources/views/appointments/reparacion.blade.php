@extends('layouts.app')

@section('content')
<div class="container mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mt-8">
    <h1 class="text-2xl font-bold text-center mb-8">Reparación de Cita - {{ $appointment->bike->nombre }}</h1>
    @php
        $customerPhoneSource = (string) (($data->first()->user_telefono ?? '') ?: optional(optional($appointment->bike)->user)->telefono);
        $customerPhone = preg_replace('/\D+/', '', $customerPhoneSource);
    @endphp

    <form action="{{ route('appointments.updateReparacion', array_merge(['appointment' => $appointment->id, 'return_url' => ($returnUrl ?? request('return_url'))], $indexContext ?? [])) }}" method="POST">
        @csrf
        @method('PUT')
        <input type="hidden" name="return_page" value="{{ ($indexContext['page'] ?? '') }}">
        <input type="hidden" name="return_search" value="{{ ($indexContext['search'] ?? '') }}">
        <input type="hidden" name="return_filtro" value="{{ ($indexContext['filtro'] ?? '') }}">
        <input type="hidden" name="return_url" value="{{ $returnUrl ?? request('return_url') }}">

        <div class="space-y-6">
            <div class="mt-6">
                <p class="text-sm font-semibold mt-4 text-gray-600">
                    ID SISTEMA: {{ $appointment->idprograma }}
                </p>
                <h1 class="text-2xl font-bold text-gray-900 mt-1">
                    Cliente: {{ $data->first()->user_name ?? 'Sin asignar' }}
                </h1>
                <h1 class="block text-700 text-lg font-semibold mb-1">
                    Descripción anterior del problema:
                </h1>
                <span class="block mb-2 text-red-700 text-lg font-medium">
                    {{ $appointment->descripcion_problema ?? 'No registrada' }}
                </span>
                <br>
                <label for="descripcion_problema" class="block text-700 text-lg mb-1 font-semibold">
                    Descripción actual del problema
                </label>
                <textarea name="descripcion_problema" id="descripcion_problema"
                          class="w-full border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Descripción actual del problema, si escribes la palabra -  nada - se quitaria el incidencia y ya no estaria en rojo(quitandose el problema que hbiera de la descripcion a vacio)">{{ old('descripcion_problema') }}</textarea>
            </div>
            
            <h3 class="text-xl font-semibold text-gray-800">Componentes de la Cita</h3>

            <!-- Iterar a través de los componentes de la cita -->
            @foreach ($data as $item)
            <label class="block cursor-pointer">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between bg-white p-4 rounded-md shadow-md mb-4 border border-gray-200 peer-checked:border-green-500 peer-checked:bg-green-50 transition-all">
                    <!-- Checkbox visible y grande -->
                    <div class="flex items-start mr-4">
                        <input type="checkbox" name="componentes[{{ $item->componente_id }}][checked]" value="1"
                               @if($item->checked) checked @endif
                               class="h-6 w-6 text-green-600 border-gray-300 rounded-md focus:ring-green-500 mr-3">
                        <input type="hidden" name="componentes[{{ $item->componente_id }}][id]" value="{{ $item->componente_id }}">
                    </div>
        
                    <!-- Información del componente -->
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-800">{{ $item->component_nombre }}</h3>
                        <p class="text-gray-500 text-sm italic mt-1">{{ $item->texto }}</p>
                        <p class="text-gray-500 text-sm italic">Duración estimada: {{ $item->horas_trabajo }} minutos</p>
                    </div>
        
                    <!-- Info del usuario asignado -->
                    <div class="text-sm text-right mt-2 sm:mt-0 sm:ml-4 text-gray-600">
                        Usuario asignado: {{ $item->usuario_taller_id }}
                    </div>
                </div>
            </label>
        @endforeach 
        </div>
        <div class="mt-6">
            <label class="block text-gray-700 font-semibold mb-1">Kilómetros anteriores:</label>
            <span class="block mb-2 text-blue-700 font-medium">
                {{ $appointment->bike->kilometros ?? 'No registrados' }} km
            </span>
        
            <label for="kilometros" class="block text-gray-700 mb-1">Kilómetros actuales de la bicicleta</label>
            <input type="number" name="kilometros" id="kilometros"
                   class="w-full border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                   placeholder="Introduce los kilómetros actuales"
                   min="0" value="{{ old('kilometros') }}">
        </div>

        <div class="mt-6">
            <button type="submit" class="px-6 py-3 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400">
                Actualizar Reparación
            </button>
        </div>
    </form>
    <br><br>
    <div class="mt-4 rounded-2xl border border-blue-100 bg-blue-50/70 p-4 sm:p-5">
        <div class="text-sm font-semibold uppercase tracking-[0.14em] text-blue-700">WhatsApp: problema detectado</div>
        <p class="mt-1 text-sm text-blue-900/80">Tienes dos plantillas separadas: una con imagen y otra sin imagen.</p>

        @if (session('success'))
            <div class="mt-3 rounded-xl border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-700">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="mt-3 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{{ session('error') }}</div>
        @endif

        @if (!empty($customerPhone))
            <form method="POST" action="{{ route('whatsapp.template', ['phone' => $customerPhone]) }}" enctype="multipart/form-data" class="mt-3 rounded-xl border border-blue-200 bg-white p-3">
                @csrf
                <input type="hidden" name="template_key" value="conversacion_problema_taller">

                <div class="text-xs font-semibold uppercase tracking-[0.08em] text-blue-700">Plantilla con imagen</div>
                <p class="mt-1 rounded-lg bg-blue-100/70 px-2 py-2 text-xs text-blue-900">Mensaje tipo plantilla: "Hola [NOMBRE_CLIENTE], hemos detectado un problema en tu bicicleta en [ZONA_PROBLEMA]. Te paso la imagen para que lo veas y nos digas [RESPUESTA_ESPERADA], dinos si seguimos adelante."</p>

                <div class="grid gap-2 sm:grid-cols-2">
                    <input
                        type="text"
                        name="issue_area"
                        value="{{ old('issue_area') }}"
                        class="w-full rounded-xl border-blue-200 text-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Problema detectado en... (ej: freno delantero)"
                    >

                    <input
                        type="text"
                        name="customer_reply_request"
                        value="{{ old('customer_reply_request') }}"
                        class="w-full rounded-xl border-blue-200 text-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Que nos diga... (ej: si autoriza cambiar la pieza)"
                    >
                </div>

                <div class="mt-2 flex flex-wrap items-center gap-2">
                    <label class="inline-flex cursor-pointer items-center justify-center rounded-xl border border-blue-200 bg-white px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100 transition-colors">
                        <input name="template_image" type="file" accept="image/jpeg,image/jpg,image/png,image/webp" class="hidden">
                        Adjuntar imagen
                    </label>

                    <button type="submit" class="inline-flex items-center rounded-xl border border-blue-200 bg-white px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100 transition-colors">
                        Enviar plantilla con imagen
                    </button>
                </div>
            </form>

            <form method="POST" action="{{ route('whatsapp.template', ['phone' => $customerPhone]) }}" class="mt-3 rounded-xl border border-blue-200 bg-white p-3">
                @csrf
                <input type="hidden" name="template_key" value="conversacion_problema_taller_sin_imagen">

                <div class="text-xs font-semibold uppercase tracking-[0.08em] text-blue-700">Plantilla sin imagen</div>
                <p class="mt-1 rounded-lg bg-blue-100/70 px-2 py-2 text-xs text-blue-900">Mensaje tipo plantilla: "Hola [NOMBRE_CLIENTE], hemos detectado un problema en tu bicicleta en [ZONA_PROBLEMA]. Y habra que [RESPUESTA_ESPERADA], dinos si seguimos adelante."</p>

                <div class="mt-3 grid gap-2 sm:grid-cols-2">
                    <input
                        type="text"
                        name="issue_area"
                        value="{{ old('issue_area') }}"
                        class="w-full rounded-xl border-blue-200 text-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Problema detectado en... (ej: freno delantero)"
                    >

                    <input
                        type="text"
                        name="customer_reply_request"
                        value="{{ old('customer_reply_request') }}"
                        class="w-full rounded-xl border-blue-200 text-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Que nos diga... (ej: si autoriza cambiar la pieza)"
                    >
                </div>

                <div class="mt-2 flex flex-wrap items-center gap-2">
                    <button type="submit" class="inline-flex items-center rounded-xl border border-blue-200 bg-white px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100 transition-colors">
                        Enviar plantilla sin imagen
                    </button>
                </div>
            </form>
        @else
            <div class="mt-3 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-700">
                El cliente no tiene teléfono registrado. Añádelo para poder enviar la plantilla.
            </div>
        @endif
    </div>
    <br>
    <!-- Botones -->
    <div class="mt-4 d-flex justify-content-center gap-3">
        <a href="{{ route('presupuestos.pdf', $appointment->id) }}" class="app-btn app-btn-pdf px-6 mr-5 py-3 focus:outline-none focus:ring-2 focus:ring-blue-400">
            <i class="fas fa-file-pdf"></i> Descargar PDF Ultimo Presupuesto
        </a>
        <a href="{{ route('appointments.edit', array_merge(['appointment' => $appointment->id, 'return_url' => ($returnUrl ?? request('return_url'))], $indexContext ?? [])) }}" 
            class="px-6 py-3 bg-green-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400">
            Editar Presupuesto
        </a>            
    </div>
    <br>
    <br>
</div>
@endsection
