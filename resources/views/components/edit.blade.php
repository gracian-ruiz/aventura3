@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-10 mt-8">
    <h1 class="text-2xl font-bold text-center mb-4">Editar Componente</h1>

    <form action="{{ route('components.update', $component->id) }}" method="POST" class="bg-white shadow-md rounded-lg p-6">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="nombre" class="block text-gray-700">Nombre del Componente</label>
            <input type="text" name="nombre" id="nombre" class="w-full border px-4 py-2 rounded-md" value="{{ $component->nombre }}" required>
        </div>

        <div class="mb-4">
            <label for="fecha_preaviso" class="block text-gray-700">Preaviso antes de revisión (en días)</label>
            <input type="number" name="fecha_preaviso" id="fecha_preaviso" class="w-full border px-4 py-2 rounded-md" value="{{ $component->fecha_preaviso }}" required>
        </div>

        <div class="mb-4">
            <label for="fecha_revision" class="block text-gray-700">Intervalo de Revisión (en días)</label>
            <input type="number" name="fecha_revision" id="fecha_revision" class="w-full border px-4 py-2 rounded-md" value="{{ $component->fecha_revision }}" required>
        </div>

        <div class="mb-4">
            <label for="hora_taller" class="block text-gray-700">Minutos de Taller</label>
            <input type="number" step="0.1" name="hora_taller" id="hora_taller" class="w-full border px-4 py-2 rounded-md" value="{{ $component->hora_taller }}" required>
        </div>

        <div class="mb-4">
            <label for="precio" class="block text-gray-700">Precio (€)</label>
            <input type="number" name="precio" id="precio" class="w-full border px-4 py-2 rounded-md" value="{{ $component->precio }}" required>
        </div>

        <div class="mb-4">
            <label for="orden" class="block text-gray-700">Orden</label>
            <input type="number" name="orden" id="orden" class="w-full border px-4 py-2 rounded-md" value="{{ $component->orden }}" placeholder="Ej: 1">
        </div>
        <div class="mb-4">
            <label for="descripcion" class="block text-gray-700">Descripción</label>
            <textarea 
                name="descripcion" 
                id="descripcion" 
                class="w-full border px-4 py-2 rounded-md" 
                rows="4" 
                placeholder="Escribe aquí la descripción...">{{ $component->descripcion }}</textarea>
        </div>
        <div class="flex justify-end">
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                Guardar Cambios
            </button>
        </div>
    </form>
</div>
@endsection
