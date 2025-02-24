@extends('layouts.app')

@section('content')
<div class="container mx-auto max-w-2xl px-4 sm:px-6 lg:px-8 mt-8">
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

        <div class="flex justify-end">
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                Guardar Cambios
            </button>
        </div>
    </form>
</div>
@endsection
