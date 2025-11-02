@extends('layouts.app')

@section('content')
<div class="container mx-auto max-w-2xl px-4 sm:px-6 lg:px-8 mt-8">
    <h1 class="text-2xl font-bold text-center mb-4">Nueva Cita</h1>

    <form action="{{ route('mecanico.store') }}" method="POST" class="bg-white shadow-md rounded-lg p-6">
        @csrf

        <!-- Selección de Bicicleta -->
        <div class="mb-4">
            <label for="bike_id" class="block text-gray-700">Bicicleta</label>
            <select name="bike_id" id="bike_id" class="w-full border px-4 py-2 rounded-md" required>
                <option value="">Selecciona una bicicleta</option>
                @foreach($bikes as $bike)
                    <option value="{{ $bike->id }}">{{ $bike->nombre }} - {{ $bike->user->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Selección de Múltiples Componentes -->
        <div class="mb-4" id="component-container">
            <label class="block text-gray-700">Componentes</label>
            <div class="flex items-center component-group">
                <select name="componentes[]" class="w-full border px-4 py-2 rounded-md" required>
                    <option value="">Selecciona un componente</option>
                    @foreach($componentes as $componente)
                        <option value="{{ $componente->id }}">{{ $componente->nombre }}</option>
                    @endforeach
                </select>
                <button type="button" id="add-component" class="ml-2 px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                    + Añadir
                </button>
            </div>
        </div>

        <!-- Prioridad -->
        <div class="mb-4">
            <label for="prioridad" class="block text-gray-700">Prioridad</label>
            <select name="prioridad" id="prioridad" class="w-full border px-4 py-2 rounded-md" required>
                <option value="normal">Normal</option>
                <option value="urgente">Urgente</option>
                <option value="premium">Premium</option>
            </select>
        </div>

        <!-- Descripción del Problema -->
        <div class="mb-4">
            <label for="descripcion_problema" class="block text-gray-700">Descripción del Problema</label>
            <textarea name="descripcion_problema" id="descripcion_problema" class="w-full border px-4 py-2 rounded-md" rows="3" required></textarea>
        </div>

        <!-- Estimación de Reparación -->
        <div class="mb-4">
            <label for="estimacion_reparacion" class="block text-gray-700">Estimación de Reparación</label>
            <textarea name="estimacion_reparacion" id="estimacion_reparacion" class="w-full border px-4 py-2 rounded-md" rows="3" required></textarea>
        </div>

        <!-- Tiempo Estimado en Minutos -->
        <div class="mb-4">
            <label for="tiempo_estimado" class="block text-gray-700">Tiempo Estimado (en minutos)</label>
            <input type="number" name="tiempo_estimado" id="tiempo_estimado" class="w-full border px-4 py-2 rounded-md" min="1" required>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">
                Guardar Cita
            </button>
        </div>
    </form>
</div>

<!-- Agregar Select2 para búsqueda en los selects y lógica para múltiples componentes -->
@section('scripts')
<script>
    $(document).ready(function() {
        $('#bike_id').select2({
            placeholder: "Selecciona una bicicleta...",
            allowClear: true,
            width: '100%'
        });
    });

    document.getElementById('add-component').addEventListener('click', function() {
        let container = document.getElementById('component-container');
        let newGroup = document.createElement('div');
        newGroup.classList.add('flex', 'items-center', 'mt-2', 'component-group');

        let select = document.querySelector('.component-group select').cloneNode(true);
        select.value = "";
        newGroup.appendChild(select);

        let removeButton = document.createElement('button');
        removeButton.type = "button";
        removeButton.classList.add('ml-2', 'px-4', 'py-2', 'bg-red-500', 'text-white', 'rounded-md', 'hover:bg-red-600');
        removeButton.innerText = "Eliminar";
        removeButton.addEventListener('click', function() {
            container.removeChild(newGroup);
        });
        newGroup.appendChild(removeButton);

        container.appendChild(newGroup);
    });
</script>
@endsection
@endsection
