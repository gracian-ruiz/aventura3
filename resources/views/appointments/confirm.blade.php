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

    <!-- Checklist de Componentes Revisados -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <h2 class="text-lg font-semibold mb-2">Checklist de Componentes Revisados:</h2>
        <ul class="list-none pl-0 text-gray-700">
            @foreach($appointment->componentes as $componente)
                <li class="mb-2 flex items-center">
                    <input type="checkbox" name="componentes_revisados[]" value="{{ $componente->id }}" class="mr-2" required>
                    <span>{{ $componente->nombre }} - {{ $componente->pivot->texto }}</span>
                </li>
            @endforeach
        </ul>
    </div>

    <!-- Revisiones a Generar -->
    <form action="{{ route('appointments.complete', $appointment->id) }}" method="POST" class="bg-white shadow-md rounded-lg p-6" id="appointment-form">
        @csrf
        @method('PUT')

        <h2 class="text-lg font-semibold mb-2">Revisiones a Generar:</h2>
        <ul class="list-disc pl-5 text-gray-700">
            @foreach($appointment->componentes as $componente)
                @php
                    $proximaRevision = now()->addDays(30)->format('d/m/Y');
                @endphp
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

        <!-- Botones -->
        <div class="flex justify-between mt-6">
            <a href="{{ route('appointments.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                Volver
            </a>
            <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600" id="submit-button">
                Confirmar Finalización
            </button>
        </div>
    </form>
</div>

<!-- Script para manejar la visualización de la fecha opcional y validación de checkboxes -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const tipoFechaRadios = document.querySelectorAll('input[type="radio"][name^="tipo_fecha"]');
    const submitButton = document.getElementById('submit-button');
    const checkboxes = document.querySelectorAll('input[name="componentes_revisados[]"]');

    tipoFechaRadios.forEach(radio => {
        radio.addEventListener("change", function() {
            const componenteId = this.name.replace("tipo_fecha[", "").replace("]", "");
            const fechaOpcionalContainer = document.querySelector(`input[name="proxima_revision[${componenteId}]"]`).closest('.fecha-opcional-container');

            if (this.value === "opcional") {
                fechaOpcionalContainer.classList.remove("hidden");
            } else {
                fechaOpcionalContainer.classList.add("hidden");
            }
        });
    });

    // Validación antes de enviar el formulario
    document.getElementById('appointment-form').addEventListener('submit', function(event) {
        let allChecked = true;
        checkboxes.forEach(function(checkbox) {
            if (!checkbox.checked) {
                allChecked = false;
            }
        });

        if (!allChecked) {
            event.preventDefault(); // Previene el envío del formulario
            alert('Por favor, marca todos los componentes antes de continuar.');
        }
    });
});
</script>
@endsection
