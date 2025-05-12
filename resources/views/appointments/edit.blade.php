@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-4">Editar Presupuesto</h2>

    <form action="{{ route('appointments.updatedos', $presupuesto->id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Selección de Bicicleta -->
        <div class="mb-4">
            <label class="block text-gray-700">Bicicleta</label>
            <select name="bike_id" class="w-full border px-4 py-2 rounded-md">
                <option value="">Selecciona una bicicleta</option>
                @foreach($bikes as $bike)
                    <option value="{{ $bike->id }}" 
                        {{ $presupuesto->bike_id == $bike->id ? 'selected' : '' }}>
                        {{ $bike->nombre }} ({{ $bike->marca }})
                    </option>
                @endforeach
            </select>
        </div>

                <!-- Prioridad de la Cita -->
         <div class="mb-4">
             <label for="prioridad" class="block text-gray-700">Prioridad</label>
             <select name="prioridad" class="w-full border px-4 py-2 rounded-md" required>
                 <option value="normal" {{ $presupuesto->prioridad == 'normal' ? 'selected' : '' }}>Normal</option>
                 <option value="urgente" {{ $presupuesto->prioridad == 'urgente' ? 'selected' : '' }}>Urgente</option>
             </select>
         </div>

         <div class="mb-4">
            <label for="asignacion_taller" class="block text-gray-700">Asignar a taller</label>
            <select name="asignacion_taller[]" id="asignacion_taller" class="w-full border px-4 py-2 rounded-md" multiple>
                @foreach($usuariosTaller as $usuario)
                    <option value="{{ $usuario->id }}"
                        @if(in_array($usuario->id, json_decode($presupuesto->asignacion_taller ?? '[]')))
                            selected
                        @endif>
                        {{ $usuario->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Tabla de Componentes -->
        <div class="mb-4">
            <table class="w-full border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border px-4 py-2">Nombre</th>
                        <th class="border px-4 py-2">Minutos Taller</th>
                        <th class="border px-4 py-2">Precio</th>
                        <th class="border px-4 py-2">Descuento</th>
                        <th class="border px-4 py-2">Descripción</th>
                        <th class="border px-4 py-2">Acción</th>
                    </tr>
                </thead>
                <tbody id="component-list">
                    @foreach($presupuesto_items as $item)
                    <tr data-id="{{ $item->componente_id }}">
                        <td class="border px-2 py-2">
                            <input type="hidden" name="componentes[]" value="{{ $item->componente_id }}">
                            {{ $item->componente_nombre }}
                        </td>
                    
                        <!-- Horas de trabajo: más estrecho -->
                        <td class="border px-2 py-2 w-20">
                            <input type="number" name="horas_trabajo[]" value="{{ $item->horas_trabajo }}" min="0" step="0.1"
                                class="w-full border rounded px-2 py-1 text-sm">
                        </td>
                    
                        <!-- Precio: más estrecho -->
                        <td class="border px-2 py-2 w-20">
                            <input type="number" name="precio[]" value="{{ old('precio.' . $loop->index, $item->total_precio) }}" min="0" step="0.01"
                                class="w-full border rounded px-2 py-1 text-sm">
                        </td>
                    
                        <!-- Descuento: campo añadido -->
                        <td class="border px-2 py-2 w-20">
                            <input type="number" name="descuento[]" value="{{ old('descuento.' . $loop->index, $item->descuento) }}" min="0" step="0.01"
                                class="w-full border rounded px-2 py-1 text-sm">
                        </td>
                    
                        <!-- Descripción del trabajo más amplia -->
                        <td class="border px-2 py-2 w-96">
                            <input type="text" name="textos[]" value="{{ $item->texto }}" placeholder="Descripción del trabajo"
                                class="w-full border rounded px-2 py-1 text-sm">
                        </td>
                    
                        <td class="border px-2 py-2">
                            <button type="button"
                                class="remove-component px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
                                Eliminar
                            </button>
                        </td>
                    </tr>                    
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Selección de Componentes (con Select2) -->
        <div class="mb-4">
            <label class="block text-gray-700">Componentes</label>
            <div class="flex items-center">
                <select id="component-select" class="w-full border px-4 py-2 rounded-md select2">
                    <option value="">Selecciona un componente</option>
                    @foreach($components as $component)
                        <option value="{{ $component->id }}" 
                                data-nombre="{{ $component->nombre }}" 
                                data-horas="{{ $component->hora_taller }}" 
                                data-precio="{{ $component->precio }}">
                            {{ $component->nombre }}
                        </option>
                    @endforeach
                </select>
                <button type="button" id="add-component" class="ml-2 px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                    + Añadir
                </button>
            </div>
        </div>

        <!-- Botón de Guardar -->
        <div class="mt-4">
            <button type="submit" class="w-full bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600">
                Guardar Cambios
            </button>
        </div>
    </form>
</div>

<!-- Agregar Select2 y lógica para manejar componentes -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

<script>
    $(document).ready(function() {
        $('.select2').select2({
            width: '100%',
            placeholder: "Selecciona un componente",
            allowClear: true
        });

        $('#add-component').on('click', function() {
            let selectedOption = $('#component-select option:selected');

            if (!selectedOption.val()) return;

            let componentId = selectedOption.val();
            let componentNombre = selectedOption.data('nombre');
            let componentHoras = selectedOption.data('horas') || 0;
            let componentPrecio = selectedOption.data('precio') || 0;

            // Verificar si el componente ya está agregado
            if ($(`tr[data-id="${componentId}"]`).length > 0) return;

            let newRow = `
                <tr data-id="${componentId}">
                    <td class="border px-4 py-2">
                        <input type="hidden" name="componentes[]" value="${componentId}">
                        ${componentNombre}
                    </td>
                    <td class="border px-4 py-2">
                        <input type="number" name="horas_trabajo[]" value="${componentHoras}" min="0" step="0.1" class="w-full border rounded px-2 py-1">
                    </td>
                    <td class="border px-4 py-2">
                        <input type="number" name="precio[]" value="${componentPrecio}" min="0" step="0.01" class="w-full border rounded px-2 py-1">
                    </td>
                    <td class="border px-4 py-2">
                        <input type="text" name="textos[]" placeholder="Descripción del trabajo" class="w-full border rounded px-2 py-1">
                    </td>
                    <td class="border px-4 py-2">
                        <button type="button" class="remove-component px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
                            Eliminar
                        </button>
                    </td>
                </tr>
            `;

            $('#component-list').append(newRow);

            // Evento para eliminar la fila agregada
            $('.remove-component').off('click').on('click', function() {
                $(this).closest('tr').remove();
            });
        });

        // Evento para eliminar filas existentes
        $('.remove-component').on('click', function() {
            $(this).closest('tr').remove();
        });
    });
</script>

@endsection
