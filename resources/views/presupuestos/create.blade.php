@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-4">Crear Presupuesto</h2>

    <form id="budget-form" action="{{ route('presupuestos.store') }}" method="POST">
        @csrf

        <!-- Selección de Bicicleta -->
        <div class="mb-4">
            <label class="block text-gray-700">Bicicleta</label>
            <select name="bike_id" id="bike-select" class="w-full border px-4 py-2 rounded-md select2">
                <option value="">Selecciona una bicicleta</option>
                @foreach($bikes as $bike)
                    <option value="{{ $bike->id }}">{{ $bike->nombre }} ({{ $bike->marca }})------km{{ $bike->kilometros }}</option>
                @endforeach
            </select>
            <p id="bike-error" class="text-red-500 text-sm mt-1 hidden">Debes seleccionar una bicicleta.</p>
        </div>

        <!-- Selección de Componentes con select -->
        <div class="mb-4">
            <label class="block text-gray-700">Selecciona un Componente</label>
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
            <button type="button" id="add-component" class="mt-2 w-full px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                + Añadir Componente
            </button>
            <p id="component-error" class="text-red-500 text-sm mt-1 hidden">Debes añadir al menos un componente.</p>
        </div>

        <!-- Botones para agregar componentes -->
        <div class="mb-4">
            <div class="flex flex-wrap space-x-4">
                @foreach ($components->filter(function($component) {
                    return $component->orden > 0; // Solo mostrar componentes con orden > 0
                })->sortBy('orden') as $component) <!-- Ordenar por 'orden' -->
                    <button type="button" class="add-component-btn bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 mb-2" 
                            data-id="{{ $component->id }}" 
                            data-nombre="{{ $component->nombre }}"
                            data-horas="{{ $component->hora_taller }}"
                            data-precio="{{ $component->precio }}">
                        {{ $component->nombre }}
                    </button>
                @endforeach
            </div>
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
                    <!-- Componentes dinámicos -->
                </tbody>
            </table>
        </div>

        <!-- Botón de Guardar -->
        <div class="mt-4">
            <button type="submit" class="w-full bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600">
                Guardar Presupuesto
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />

<script>
$(document).ready(function() {
    $('.select2').select2({
        placeholder: "Selecciona una opción",
        allowClear: true
    });

    // Validar selección de bicicleta y componentes antes de enviar el formulario
    $('#budget-form').on('submit', function(event) {
        let bikeSelected = $('#bike-select').val();
        let componentsAdded = $('#component-list tr').length > 0;
        let valid = true;

        if (!bikeSelected) {
            $('#bike-error').removeClass('hidden');
            valid = false;
        } else {
            $('#bike-error').addClass('hidden');
        }

        if (!valid) {
            event.preventDefault(); // Evita el envío del formulario si hay errores
        }
    });

    // Función para añadir componentes (botón o select)
    function addComponent(componentId, nombre, horas, precio) {
        let tableBody = $('#component-list');

        // Verificar si el componente ya está en la tabla
        let exists = tableBody.find(`tr[data-id='${componentId}']`).length > 0;
        let materialExists = tableBody.find("tr[data-nombre='Material']").length > 0;

        // Bloquear más de un "Material"
        if (nombre === "Material" && materialExists) {
            alert('Solo puedes agregar un componente de tipo "Material".');
            return;
        }

        // Bloquear componentes repetidos (excepto "Material")
        if (nombre !== "Material" && exists) {
            alert('Este componente ya ha sido añadido.');
            return;
        }

        let newRow = `
            <tr data-id="${componentId}" data-nombre="${nombre}">
                <td class="border px-4 py-2">${nombre}</td>
                <td class="border px-4 py-2">
                    <input type="number" name="horas_trabajo[]" value="${horas}" min="0" step="0.1" class="w-20 border rounded px-2 py-1">
                </td>
                <td class="border px-4 py-2">
                    <input type="number" name="precios[]" value="${precio}" min="0" step="0.01" class="w-full border rounded px-2 py-1">
                </td>
                <td class="border px-4 py-2">
                    <input type="number" name="descuentos[]" value="0" min="0" step="1" class="w-full border rounded px-2 py-1" placeholder="Descuento €">
                </td>
                <td class="border px-4 py-2">
                    <textarea name="textos[]" placeholder="Descripción del trabajo" class="w-72 h-16 border rounded px-2 py-1 resize-y"></textarea>
                </td>
                <td class="border px-4 py-2">
                    <button type="button" class="remove-component px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
                        Eliminar
                    </button>
                </td>
                <input type="hidden" name="componentes[]" value="${componentId}">
            </tr>`;


        tableBody.append(newRow);

        // Ocultar error si se añade un componente
        $('#component-error').addClass('hidden');

        // Agregar evento de eliminación
        $('.remove-component').on('click', function() {
            $(this).closest('tr').remove();

            // Mostrar error si no hay componentes después de eliminar
            if ($('#component-list tr').length === 0) {
                $('#component-error').removeClass('hidden');
            }
        });
    }

    // Evento de añadir componente desde el select
    $('#add-component').on('click', function() {
        let select = $('#component-select');
        let selectedOption = select.find(':selected');

        if (!selectedOption.val()) return;

        let componentId = selectedOption.val();
        let nombre = selectedOption.data('nombre');
        let horas = selectedOption.data('horas') || 0;
        let precio = selectedOption.data('precio') || 0;

        addComponent(componentId, nombre, horas, precio);
        select.val(null).trigger('change');
    });

    // Evento de añadir componente desde el botón
    $('.add-component-btn').on('click', function() {
        let componentId = $(this).data('id');
        let nombre = $(this).data('nombre');
        let horas = $(this).data('horas') || 0;
        let precio = $(this).data('precio') || 0;

        addComponent(componentId, nombre, horas, precio);
    });
});
</script>
@endsection
