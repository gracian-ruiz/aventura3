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

        <!-- Selección de Componente -->
        <div class="mb-4">
            <label for="componente_id" class="block text-gray-700">Componente (Opcional)</label>
            <select name="componente_id" id="componente_id" class="w-full border px-4 py-2 rounded-md">
                <option value="">Sin Componente</option>
                @foreach($componentes as $componente)
                    <option value="{{ $componente->id }}">{{ $componente->nombre }}</option>
                @endforeach
            </select>
        </div>

        <script>
            $(document).ready(function() {
                $('#componente_id').select2({
                    placeholder: "Buscar componente...",
                    allowClear: true,
                    width: '100%'
                });
            });
        </script>

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

        <!-- Campo de fecha opcional (se muestra solo si elige "opcional") -->
        <div id="fechaOpcionalContainer" class="hidden">
            <label for="proxima_revision" class="block text-gray-700">Selecciona una fecha:</label>
            <input type="date" name="proxima_revision" id="proxima_revision" class="w-full border px-4 py-2 rounded-md">
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">
                Guardar Revisión
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const tipoFechaInputs = document.querySelectorAll('input[name="tipo_fecha"]');
    const fechaOpcionalContainer = document.getElementById("fechaOpcionalContainer");
    const proximaRevisionInput = document.getElementById("proxima_revision");
    const componenteSelect = document.getElementById("componente_id");
    const fechaRevisionInput = document.getElementById("fecha_revision");

    // Manejar el cambio de tipo de fecha
    tipoFechaInputs.forEach(input => {
        input.addEventListener("change", function() {
            if (this.value === "opcional") {
                fechaOpcionalContainer.classList.remove("hidden");
            } else {
                fechaOpcionalContainer.classList.add("hidden");
                calcularFechaFija();
            }
        });
    });

    // Manejar el cambio de componente
    componenteSelect.addEventListener("change", calcularFechaFija);

    function calcularFechaFija() {
        const selectedOption = componenteSelect.options[componenteSelect.selectedIndex];
        const intervaloMeses = selectedOption.dataset.fechaRevision;

        if (intervaloMeses && tipoFechaInputs[0].checked) {
            let fechaActual = new Date(fechaRevisionInput.value);
            fechaActual.setMonth(fechaActual.getMonth() + parseInt(intervaloMeses));
            proximaRevisionInput.value = fechaActual.toISOString().split("T")[0];
        }
    }
});
</script>
@endsection
