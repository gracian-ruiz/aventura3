@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-4">Editar Presupuesto</h2>

    <form action="{{ route('presupuestos.update', $presupuesto->id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Selección de Bicicleta -->
        <div class="mb-4">
            <label class="block text-gray-700">Bicicleta</label>
            <select name="bike_id" class="w-full border px-4 py-2 rounded-md" required>
                <option value="">Selecciona una bicicleta</option>
                @foreach($bikes as $bike)
                    <option value="{{ $bike->id }}" 
                        {{ $presupuesto->bike_id == $bike->id ? 'selected' : '' }}>
                        {{ $bike->nombre }} ({{ $bike->marca }})
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
                        <th class="border px-4 py-2">Horas Taller</th>
                        <th class="border px-4 py-2">Precio (€)</th>
                        <th class="border px-4 py-2">Descripción</th>
                        <th class="border px-4 py-2">Acción</th>
                    </tr>
                </thead>
                <tbody id="component-list">
                    @foreach($presupuesto_items as $item)
                        <tr data-id="{{ $item->componente_id }}">
                            <td class="border px-4 py-2">
                                <input type="hidden" name="componentes[]" value="{{ $item->componente_id }}">
                                {{ $item->componente_nombre }}
                            </td>
                            <td class="border px-4 py-2">
                                <input type="number" name="horas_trabajo[]" value="{{ $item->horas_trabajo }}" min="0" step="0.1" class="w-full border rounded px-2 py-1" required>
                            </td>
                            <td class="border px-4 py-2">
                                <input type="number" name="precio[]" value="{{ $item->precio }}" min="0" step="0.01" class="w-full border rounded px-2 py-1" required>
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

        <!-- Botón para agregar un nuevo componente -->
        <div class="mb-4">
            <label class="block text-gray-700">Añadir Componente</label>
            <select id="add-component" class="w-full border px-4 py-2 rounded-md">
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
            <button type="button" id="add-component-btn" class="mt-2 bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">Agregar Componente</button>
        </div>

        <!-- Botón de Guardar -->
        <div class="mt-4">
            <button type="submit" class="w-full bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600">Guardar Cambios</button>
        </div>
    </form>
</div>

<!-- Script para manejar la adición y eliminación de componentes -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const addComponentBtn = document.getElementById('add-component-btn');
        const componentList = document.getElementById('component-list');
        const addComponentSelect = document.getElementById('add-component');
    
        addComponentBtn.addEventListener('click', function() {
            const selectedOption = addComponentSelect.options[addComponentSelect.selectedIndex];
    
            if (!selectedOption.value) return;
    
            const componentId = selectedOption.value;
            const componentNombre = selectedOption.dataset.nombre;
            const componentHoras = selectedOption.dataset.horas || 0;
            const componentPrecio = selectedOption.dataset.precio || 0;
    
            if (document.querySelector(`tr[data-id="${componentId}"]`)) return;
    
            const newRow = document.createElement('tr');
            newRow.setAttribute('data-id', componentId);
            newRow.innerHTML = `
                <td class="border px-4 py-2">
                    <input type="hidden" name="componentes[]" value="${componentId}">
                    ${componentNombre}
                </td>
                <td class="border px-4 py-2">
                    <input type="number" name="horas_trabajo[]" value="${componentHoras}" min="0" step="0.1" class="w-full border rounded px-2 py-1" required>
                </td>
                <td class="border px-4 py-2">
                    <input type="number" name="precio[]" value="${componentPrecio}" min="0" step="0.01" class="w-full border rounded px-2 py-1" required>
                </td>
                <td class="border px-4 py-2">
                    <input type="text" name="textos[]" placeholder="Descripción del trabajo" class="w-full border rounded px-2 py-1">
                </td>
                <td class="border px-4 py-2">
                    <button type="button" class="remove-component px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">Eliminar</button>
                </td>
            `;
    
            componentList.appendChild(newRow);
    
            newRow.querySelector('.remove-component').addEventListener('click', function() {
                newRow.remove();
            });
        });
    
        document.querySelectorAll('.remove-component').forEach(button => {
            button.addEventListener('click', function() {
                this.closest('tr').remove();
            });
        });
    });
</script>
@endsection
