@extends('layouts.app')

@section('content')
<div class="container mx-auto px-3 sm:px-4 lg:px-6 py-4 sm:py-6">
    <div class="bg-white p-4 sm:p-6 lg:p-8 rounded-lg shadow-md">
        <h2 class="text-xl sm:text-2xl lg:text-3xl font-bold mb-4 sm:mb-6">Crear Presupuesto</h2>

    <form id="budget-form" action="{{ route('presupuestos.store') }}" method="POST">
        @csrf

        <!-- Selección de Bicicleta -->
        <div class="mb-4 sm:mb-5">
            <label class="block text-gray-700 font-medium mb-2 text-sm sm:text-base">Bicicleta</label>
            <select name="bike_id" id="bike-select" class="w-full border px-3 sm:px-4 py-2 rounded-md select2 text-sm sm:text-base">
                <option value="">Selecciona una bicicleta</option>
                @foreach($bikes as $bike)
                    <option value="{{ $bike->id }}">({{ $bike->marca }}) {{ $bike->nombre }} {{ $bike->color }}------km{{ $bike->kilometros }}</option>
                @endforeach
            </select>
            <p id="bike-error" class="text-red-500 text-sm mt-1 hidden">Debes seleccionar una bicicleta.</p>
        </div>
        <div class="mb-4 sm:mb-5 grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
            <!-- Campo Prioridad -->
            <div>
                <label for="prioridad" class="block text-gray-700 font-medium mb-2 text-sm sm:text-base">Prioridad</label>
                <select name="prioridad" id="prioridad" class="w-full border px-3 sm:px-4 py-2 rounded-md text-sm sm:text-base" required>
                    <option value="normal">Normal</option>
                    <option value="urgente">Urgente</option>
                    <option value="premium">Premium</option>
                </select>
            </div>

            <!-- Campo idprograma -->
            <div>
                <label for="idprograma" class="block text-gray-700 font-medium mb-2 text-sm sm:text-base">ID Programa</label>
                <input type="text" name="idprograma" id="idprograma" class="w-full border px-3 sm:px-4 py-2 rounded-md text-sm sm:text-base">
            </div>
        </div>
        <div class="mb-4 sm:mb-5">
            <label for="calendario" class="block text-gray-700 font-medium mb-2 text-sm sm:text-base">📅 Calendario</label>
            <input type="date" 
                name="calendario" 
                id="calendario" 
                class="w-full border px-3 sm:px-4 py-2 rounded-md text-sm sm:text-base" 
                value="">
        </div>
        <div class="mb-4 sm:mb-5">
            <label for="asignacion_taller" class="block text-gray-700 font-medium mb-2 text-sm sm:text-base">Asignar a Mecanico</label>
            <select name="asignacion_taller[]" id="asignacion_taller" class="w-full border px-3 sm:px-4 py-2 rounded-md text-sm sm:text-base" multiple>
                @foreach ($talleristas as $tallerista)
                    <option value="{{ $tallerista->id }}">{{ $tallerista->name }}</option>
                @endforeach
            </select>
            <small class="text-gray-500">Puedes seleccionar uno o varios usuarios</small>
        </div>
        
        <!-- Selección de Componentes con select -->
        <div class="mb-4 sm:mb-5">
            <label class="block text-gray-700 font-medium mb-2 text-sm sm:text-base">Selecciona un Componente</label>
            <select id="component-select" class="w-full border px-3 sm:px-4 py-2 rounded-md select2 text-sm sm:text-base">
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
            <button type="button" id="add-component" class="mt-2 w-full px-3 sm:px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 text-sm sm:text-base">
                + Añadir Componente
            </button>
            <p id="component-error" class="text-red-500 text-sm mt-1 hidden">Debes añadir al menos un componente.</p>
        </div>

        <!-- Botones para agregar componentes -->
        <div class="mb-4 sm:mb-5">
            <label class="block text-gray-700 font-medium mb-2 text-sm sm:text-base">Componentes Rápidos:</label>
            <div class="flex flex-wrap gap-2">
                @foreach ($components->filter(function($component) {
                    return $component->orden > 0; // Solo mostrar componentes con orden > 0
                })->sortBy('orden') as $component) <!-- Ordenar por 'orden' -->
                    <button type="button" class="add-component-btn bg-blue-500 text-white px-3 py-2 rounded-md hover:bg-blue-600 text-xs sm:text-sm" 
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
        <div class="mb-4 sm:mb-5 overflow-x-auto">
            <table class="w-full border-collapse border border-gray-300 text-xs sm:text-sm">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border px-2 sm:px-4 py-2">Nombre</th>
                        <th class="border px-2 sm:px-4 py-2">Minutos</th>
                        <th class="border px-2 sm:px-4 py-2">Precio Mano Obra</th>
                        <th class="border px-2 sm:px-4 py-2">Precio Material</th>
                        <th class="border px-2 sm:px-4 py-2">Desc.</th>
                        <th class="border px-2 sm:px-4 py-2">Descripción</th>
                        <th class="border px-2 sm:px-4 py-2">Acción</th>
                    </tr>
                </thead>
                <tbody id="component-list">
                    <!-- Componentes dinámicos -->
                </tbody>
            </table>
        </div>

        <!-- Botón de Guardar -->
        <div class="mt-4 sm:mt-6">
            <button type="submit" class="w-full bg-green-500 text-white px-4 py-2 sm:py-3 rounded-md hover:bg-green-600 text-sm sm:text-base font-semibold">
                Guardar Presupuesto
            </button>
        </div>
    </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />

<style>
    /* Hacer select2 responsive */
    .select2-container {
        width: 100% !important;
    }
    
    .select2-container .select2-selection--single {
        height: 42px !important;
        padding: 8px 12px !important;
        border: 1px solid #d1d5db !important;
        border-radius: 0.375rem !important;
    }
    
    .select2-container .select2-selection--single .select2-selection__rendered {
        line-height: 26px !important;
        padding-left: 0 !important;
    }
    
    .select2-container .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
        right: 8px !important;
    }
    
    .select2-container .select2-selection--multiple {
        min-height: 42px !important;
        padding: 4px 8px !important;
        border: 1px solid #d1d5db !important;
        border-radius: 0.375rem !important;
    }
    
    /* Responsive para móvil */
    @media (max-width: 640px) {
        .select2-container .select2-selection--single {
            height: 38px !important;
            padding: 6px 10px !important;
            font-size: 0.875rem !important;
        }
        
        .select2-container .select2-selection--single .select2-selection__rendered {
            font-size: 0.875rem !important;
        }
        
        .select2-container .select2-selection--multiple {
            font-size: 0.875rem !important;
        }
    }
</style>

<script>
$(document).ready(function() {
    $('.select2').select2({
        placeholder: "Selecciona una opción",
        allowClear: true,
        width: '100%'
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
                <td class="border px-2 sm:px-4 py-2 text-xs sm:text-sm">${nombre}</td>
                <td class="border px-2 sm:px-4 py-2">
                    <input type="number" name="horas_trabajo[]" value="${horas}" min="0" step="0.1" class="w-16 sm:w-20 border rounded px-1 sm:px-2 py-1 text-xs sm:text-sm">
                </td>
                <td class="border px-2 sm:px-4 py-2">
                    <input type="number" name="precios[]" value="${precio}" min="0" step="0.01" class="w-20 sm:w-full border rounded px-1 sm:px-2 py-1 text-xs sm:text-sm">
                </td>
                <td class="border px-2 sm:px-4 py-2">
                    <input type="number" name="precio_materiales[]" value="0" min="0" step="0.01" class="w-20 sm:w-full border rounded px-1 sm:px-2 py-1 text-xs sm:text-sm">
                </td>
                <td class="border px-2 sm:px-4 py-2">
                    <input type="number" name="descuentos[]" value="0" min="0" step="1" class="w-20 sm:w-full border rounded px-1 sm:px-2 py-1 text-xs sm:text-sm" placeholder="Desc €">
                </td>
                <td class="border px-2 sm:px-4 py-2">
                    <textarea name="textos[]" placeholder="Descripción" class="w-40 sm:w-72 h-12 sm:h-16 border rounded px-1 sm:px-2 py-1 resize-y text-xs sm:text-sm"></textarea>
                </td>
                <td class="border px-2 sm:px-4 py-2">
                    <button type="button" class="remove-component px-2 sm:px-4 py-1 sm:py-2 bg-red-500 text-white rounded-md hover:bg-red-600 text-xs sm:text-sm">
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
