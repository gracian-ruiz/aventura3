@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['name' => 'Inicio', 'url' => route('dashboard')],
    ['name' => 'Revisiones', 'url' => route('revisions.index')],
    ['name' => 'Crear Revisión']
]" />

<div class="container mx-auto max-w-2xl px-4 sm:px-6 lg:px-8 mt-8">
    <h1 class="text-2xl font-bold text-center mb-4">Nueva Revisión para {{ $bike->nombre }}</h1>

    <form action="{{ route('bikes.revisions.store', $bike->id) }}" method="POST" class="bg-white shadow-md rounded-lg p-6">
        @csrf

        <!-- Selección de Componentes (Múltiples) -->
        <div class="mb-4">
            <label for="componentes" class="block text-gray-700">Componentes</label>
            <div id="componentes-container">
                <div class="componente-select mb-2">
                    <select name="componentes[]" class="componente_id w-full border px-4 py-2 rounded-md">
                        <option value="">Selecciona un componente</option>
                        @foreach($componentes as $componente)
                            <option value="{{ $componente->id }}">{{ $componente->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <button type="button" id="addComponente" class="mt-2 px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                + Agregar otro componente
            </button>
        </div>

        <div class="mb-4">
            <label for="fecha_revision" class="block text-gray-700">Fecha de Revisión</label>
            <input type="date" name="fecha_revision" id="fecha_revision" class="w-full border px-4 py-2 rounded-md" required>
        </div>

        <div class="mb-4">
            <label for="descripcion" class="block text-gray-700">Descripción</label>
            <textarea name="descripcion" id="descripcion" class="w-full border px-4 py-2 rounded-md" required></textarea>
        </div>

        <!-- Selección de tipo de fecha -->
        <div class="mb-4">
            <label class="block text-gray-700">Tipo de Próxima Revisión</label>
            <div class="flex items-center space-x-4">
                <label>
                    <input type="radio" name="tipo_fecha" value="fija" class="mr-2" checked>
                    Fecha Fija (según componente)
                </label>
                <label>
                    <input type="radio" name="tipo_fecha" value="opcional" class="mr-2">
                    Fecha Opcional
                </label>
            </div>
        </div>

        <!-- Campo de fecha opcional -->
        <div id="fechaOpcionalContainer" class="hidden">
            <label for="proxima_revision" class="block text-gray-700">Selecciona una fecha:</label>
            <input type="date" name="proxima_revision" id="proxima_revision" class="w-full border px-4 py-2 rounded-md">
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">
                Guardar Revisiones
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const tipoFechaInputs = document.querySelectorAll('input[name="tipo_fecha"]');
    const fechaOpcionalContainer = document.getElementById("fechaOpcionalContainer");
    const proximaRevisionInput = document.getElementById("proxima_revision");
    const fechaRevisionInput = document.getElementById("fecha_revision");
    const addComponenteBtn = document.getElementById("addComponente");
    const componentesContainer = document.getElementById("componentes-container");

    // Manejar el cambio de tipo de fecha
    tipoFechaInputs.forEach(input => {
        input.addEventListener("change", function() {
            if (this.value === "opcional") {
                fechaOpcionalContainer.classList.remove("hidden");
            } else {
                fechaOpcionalContainer.classList.add("hidden");
            }
        });
    });

    // Agregar más select de componentes dinámicamente
    addComponenteBtn.addEventListener("click", function() {
        const nuevoSelect = document.createElement("div");
        nuevoSelect.classList.add("componente-select", "mb-2");
        nuevoSelect.innerHTML = `
            <select name="componentes[]" class="componente_id w-full border px-4 py-2 rounded-md">
                <option value="">Selecciona un componente</option>
                @foreach($componentes as $componente)
                    <option value="{{ $componente->id }}">{{ $componente->nombre }}</option>
                @endforeach
            </select>
        `;
        componentesContainer.appendChild(nuevoSelect);
    });
});
</script>
@endsection
