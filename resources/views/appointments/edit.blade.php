@extends('layouts.app')

@section('content')
<div class="container mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 mt-8">
    <h1 class="text-2xl font-bold text-center mb-4">Editar Cita</h1>

    <form action="{{ route('appointments.updatedos', $appointment->id) }}" method="POST" class="bg-white shadow-md rounded-lg p-6">
        @csrf
        @method('PUT')

        <!-- Bicicleta y Usuario -->
        <div class="mb-4">
            <label class="block text-gray-700">Bicicleta:</label>
            <p class="text-gray-900 font-semibold">{{ $appointment->bike->nombre }} - {{ $appointment->bike->user->name }}</p>
        </div>

        <!-- Prioridad de la Cita -->
        <div class="mb-4">
            <label for="prioridad" class="block text-gray-700">Prioridad</label>
            <select name="prioridad" class="w-full border px-4 py-2 rounded-md" required>
                <option value="normal" {{ $appointment->prioridad == 'normal' ? 'selected' : '' }}>Normal</option>
                <option value="urgente" {{ $appointment->prioridad == 'urgente' ? 'selected' : '' }}>Urgente</option>
            </select>
        </div>

        <!-- Descripción del Problema -->
        <div class="mb-4">
            <label for="descripcion_problema" class="block text-gray-700">Descripción del Problema</label>
            <textarea name="descripcion_problema" class="w-full border px-4 py-2 rounded-md" required>{{ $appointment->descripcion_problema }}</textarea>
        </div>

        <!-- Tiempo Estimado -->
        <div class="mb-4">
            <label for="tiempo_estimado" class="block text-gray-700">Tiempo Estimado (minutos)</label>
            <input type="number" name="tiempo_estimado" value="{{ $appointment->tiempo_estimado }}" class="w-full border px-4 py-2 rounded-md" required>
        </div>

        <!-- Tabla de Componentes -->
        <div class="mb-4">
            <table class="w-full border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border px-4 py-2">Nombre</th>
                        <th class="border px-4 py-2">Horas Taller</th>
                        <th class="border px-4 py-2">Precio</th>
                        <th class="border px-4 py-2">Descripción</th>
                        <th class="border px-4 py-2">Acción</th>
                    </tr>
                </thead>
                <tbody id="component-list">
                    @foreach($appointment_items as $item)
                        <tr data-id="{{ $item->componente_id }}">
                            <td class="border px-4 py-2">
                                <input type="hidden" name="componentes[]" value="{{ $item->componente_id }}">
                                {{ $item->componente_nombre }}
                            </td>
                            <td class="border px-4 py-2">
                                <input type="number" name="horas_trabajo[]" value="{{ $item->horas_trabajo }}" min="0" step="0.1" class="w-full border rounded px-2 py-1">
                            </td>
                            <td class="border px-4 py-2">
                                <input type="number" name="precio[]" value="{{ old('precio.' . $loop->index, $item->total_precio) }}" min="0" step="0.01" class="w-full border rounded px-2 py-1">
                            </td>
                            <td class="border px-4 py-2">
                                <input type="text" name="textos[]" value="{{ $item->texto }}" placeholder="Descripción del trabajo" class="w-full border rounded px-2 py-1">
                            </td>
                            <td class="border px-4 py-2">
                                <button type="button" class="remove-component px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">Eliminar</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Selección de Componentes -->
        <div class="mb-4">
            <label class="block text-gray-700">Componentes</label>
            <div class="flex items-center">
                <select id="component-select" class="w-full border px-4 py-2 rounded-md select2">
                    <option value="">Selecciona un componente</option>
                    @foreach($componentes as $component)
                        <option value="{{ $component->id }}" data-nombre="{{ $component->nombre }}" data-horas="{{ $component->hora_taller }}" data-precio="{{ $component->precio }}">
                            {{ $component->nombre }}
                        </option>
                    @endforeach
                </select>
                <button type="button" id="add-component" class="ml-2 px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">+ Añadir</button>
            </div>
        </div>

        <!-- Botón de Guardar -->
        <div class="mt-4">
            <button type="submit" class="w-full bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600">Guardar Cambios</button>
        </div>
    </form>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

<script>
    $(document).ready(function() {
        $('.select2').select2({ width: '100%', placeholder: "Selecciona un componente", allowClear: true });
        $('#add-component').on('click', function() {
            let selectedOption = $('#component-select option:selected');
            if (!selectedOption.val()) return;
            let componentId = selectedOption.val();
            let componentNombre = selectedOption.data('nombre');
            let componentHoras = selectedOption.data('horas') || 0;
            let componentPrecio = selectedOption.data('precio') || 0;
            if ($(`tr[data-id="${componentId}"]`).length > 0) return;
            let newRow = `<tr data-id="${componentId}"><td class="border px-4 py-2"><input type="hidden" name="componentes[]" value="${componentId}">${componentNombre}</td><td class="border px-4 py-2"><input type="number" name="horas_trabajo[]" value="${componentHoras}" min="0" step="0.1" class="w-full border rounded px-2 py-1"></td><td class="border px-4 py-2"><input type="number" name="precio[]" value="${componentPrecio}" min="0" step="0.01" class="w-full border rounded px-2 py-1"></td><td class="border px-4 py-2"><input type="text" name="textos[]" placeholder="Descripción del trabajo" class="w-full border rounded px-2 py-1"></td><td class="border px-4 py-2"><button type="button" class="remove-component px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">Eliminar</button></td></tr>`;
            $('#component-list').append(newRow);
            $('.remove-component').on('click', function() { $(this).closest('tr').remove(); });
        });
    });
</script>
@endsection
