@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-4">Crear Presupuesto</h2>

    <form action="{{ route('presupuestos.store') }}" method="POST">
        @csrf

        <!-- Selección de Bicicleta -->
        <div class="mb-4">
            <label class="block text-gray-700">Bicicleta</label>
            <select name="bike_id" class="w-full border px-4 py-2 rounded-md">
                <option value="">Selecciona una bicicleta</option>
                @foreach($bikes as $bike)
                    <option value="{{ $bike->id }}">{{ $bike->nombre }} ({{ $bike->marca }})</option>
                @endforeach
            </select>
        </div>

        <!-- Selección de Componentes -->
        <div class="mb-4">
            <label class="block text-gray-700">Componentes</label>
            <div class="flex items-center">
                <select id="component-select" class="w-full border px-4 py-2 rounded-md">
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
                    <!-- Aquí se añadirán los componentes dinámicamente -->
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
<script>
document.getElementById('add-component').addEventListener('click', function() {
    let select = document.getElementById('component-select');
    let selectedOption = select.options[select.selectedIndex];

    if (selectedOption.value === "") return;

    let componentId = selectedOption.value;
    let nombre = selectedOption.getAttribute('data-nombre');
    let horas = selectedOption.getAttribute('data-horas') || 0;
    let precio = selectedOption.getAttribute('data-precio') || 0;
    
    let tableBody = document.getElementById('component-list');

    // Permitir repetir solo el componente "Material"
    if (nombre !== "Material" && document.querySelector(`#component-list tr[data-id="${componentId}"]`)) {
        alert('Este componente ya ha sido añadido.');
        return;
    }

    // Crear la fila de la tabla con inputs editables
    let newRow = document.createElement('tr');
    newRow.innerHTML = `
        <td class="border px-4 py-2">${nombre}</td>
        <td class="border px-4 py-2">
            <input type="number" name="horas_trabajo[]" value="${horas}" min="0" step="0.1" class="w-full border rounded px-2 py-1">
        </td>
        <td class="border px-4 py-2">
            <input type="number" name="precios[]" value="${precio}" min="0" step="0.01" class="w-full border rounded px-2 py-1">
        </td>
        <td class="border px-4 py-2">
            <input type="text" name="textos[]" placeholder="Descripción del trabajo" class="w-full border rounded px-2 py-1">
        </td>
        <td class="border px-4 py-2">
            <button type="button" class="remove-component px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
                Eliminar
            </button>
        </td>
        <input type="hidden" name="componentes[]" value="${componentId}">
    `;

    // Asegurar que solo "Material" se puede repetir
    if (nombre !== "Material") {
        newRow.setAttribute('data-id', componentId);
    }

    tableBody.appendChild(newRow);

    // Agregar evento de eliminación
    newRow.querySelector('.remove-component').addEventListener('click', function() {
        tableBody.removeChild(newRow);
    });

    select.value = "";
});
</script>
@endsection
