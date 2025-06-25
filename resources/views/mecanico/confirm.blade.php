@extends('layouts.app')

@section('content')
<div class="container mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 mt-8">
    <h1 class="text-2xl font-bold text-center mb-4">Detalles de la Cita</h1>

    <!-- Información de la Bicicleta -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <h2 class="text-lg font-semibold mb-2">Bicicleta:</h2>
        <p class="text-gray-700"><strong>{{ $appointment->bike->nombre }}</strong> - {{ $appointment->bike->user->name }}</p>

        <h2 class="text-lg font-semibold mt-4">Descripción del Problema:</h2>
        <p class="text-gray-700">{{ $appointment->descripcion_problema }}</p>

        <h2 class="text-lg font-semibold mt-4">Tiempo Estimado:</h2>
        <p class="text-gray-700">{{ $appointment->tiempo_estimado }} min</p>

        <h2 class="text-lg font-semibold mt-4">ID SISTEMA</h2>
        <p class="text-gray-700">{{ $appointment->idprograma }}</p>
    </div>

    <!-- Componentes a Revisar -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <h2 class="text-lg font-semibold mb-2">Componentes a Revisar:</h2>
        <ul class="list-disc pl-5 text-gray-700">
            @foreach($appointment->componentes as $componente)
                <li>{{ $componente->nombre }}</li>
            @endforeach
        </ul>
    </div>

    <!-- Botón copiar mensaje -->
    @if (!$faltanComponentes)
        <button type="button" id="btn-copiar-mensaje"
            class="btn btn-sm btn-outline-primary fixed top-4 right-4 bg-red-600 text-white px-4 py-2 rounded shadow-md z-50 hover:bg-red-700 transition">
            📋 Copiar mensaje al cliente
        </button>
    @endif

    <!-- Revisiones a Generar -->
    <form action="{{ route('mecanico.complete', $appointment->id) }}" method="POST" class="bg-white shadow-md rounded-lg p-6" id="appointment-form">
        @csrf
        @method('PUT')

        <h2 class="text-lg font-semibold mb-2">Revisiones a Generar:</h2>
        <ul class="list-disc pl-5 text-gray-700">
            @foreach($appointment->componentes as $componente)
                <li class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="revisiones[]" value="{{ $componente->id }}" class="mr-2" checked>
                        Revisión de <strong>{{ $componente->nombre }}</strong>
                    </label>

                    <!-- Tipo de Próxima Revisión -->
                    <div class="mt-2">
                        <label class="block text-gray-700">Tipo de Próxima Revisión</label>
                        <div class="flex items-center space-x-4">
                            <label>
                                <input type="radio" name="tipo_fecha[{{ $componente->id }}]" value="fija" class="mr-2" checked>
                                Fecha Fija (según componente)
                            </label>
                            <label>
                                <input type="radio" name="tipo_fecha[{{ $componente->id }}]" value="opcional" class="mr-2">
                                Fecha Opcional
                            </label>
                        </div>
                    </div>

                    <!-- Campo de fecha opcional (se muestra solo si elige "opcional") -->
                    <div class="mt-2 hidden fecha-opcional-container">
                        <label class="block text-gray-700">Selecciona una fecha:</label>
                        <input type="date" name="proxima_revision[{{ $componente->id }}]" class="w-full border px-4 py-2 rounded-md">
                    </div>

                    <!-- Descripción de la revisión -->
                    <textarea name="descripcion_revisiones[{{ $componente->id }}]" 
                        class="w-full border px-4 py-2 rounded-md mt-2"
                        placeholder="Descripción de la revisión..." required>{{ $componente->nombre }}</textarea>
              
                </li>
            @endforeach
        </ul>

        @if ($faltanComponentes)
            <div class="bg-red-100 text-red-700 border border-red-400 px-4 py-3 rounded-md mb-4">
                ⚠️ <strong>No puedes finalizar la cita</strong>: Faltan componentes por reparar. Debes completar todos antes de continuar.
            </div>
        @endif

        @if (!$faltanComponentes)
            <div class="bg-green-100 border border-green-400 text-green-800 px-6 py-4 rounded-lg mt-8">
                <h2 class="text-lg font-semibold mb-2">✅ Mensaje de confirmación para el cliente:</h2>
                <p id="mensaje-cliente" class="whitespace-pre-line">{{ $mensaje }}</p>

                <p class="mt-2">📞 <strong>Teléfono:</strong> {{ $telefono }}</p>
                <p>👤 <strong>Cliente:</strong> {{ $nombre }}</p>
            </div>
        @endif

        <!-- Botones -->
        <div class="flex justify-between mt-6">
            <a href="{{ route('mecanico.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                Volver
            </a>

            <button class="px-6 py-2 text-white font-semibold rounded-md transition duration-300
                @if($faltanComponentes) bg-gray-400 cursor-not-allowed @else bg-green-600 hover:bg-green-700 @endif" 
                @if($faltanComponentes) disabled @endif>
                Confirmar finalización
            </button>
        </div>
    </form>
</div>

<!-- Script para mostrar campos de fecha opcional -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const tipoFechaRadios = document.querySelectorAll('input[type="radio"][name^="tipo_fecha"]');

    tipoFechaRadios.forEach(radio => {
        radio.addEventListener("change", function() {
            const componenteId = this.name.replace("tipo_fecha[", "").replace("]", "");
            const fechaInput = document.querySelector(`input[name="proxima_revision[${componenteId}]"]`);
            if (fechaInput) {
                const container = fechaInput.closest('.fecha-opcional-container');
                if (this.value === "opcional") {
                    container.classList.remove("hidden");
                } else {
                    container.classList.add("hidden");
                }
            }
        });
    });

    // Copiar mensaje al cliente
    const btnCopiar = document.getElementById('btn-copiar-mensaje');
    if (btnCopiar) {
        btnCopiar.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const mensaje = document.getElementById('mensaje-cliente')?.innerText;
            if (mensaje) {
                navigator.clipboard.writeText(mensaje)
                    .then(() => console.log("✅ Mensaje copiado al portapapeles"))
                    .catch(err => alert("❌ No se pudo copiar el mensaje"));
            }
        });
    }
});
</script>
@endsection
