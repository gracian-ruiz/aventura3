@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['name' => 'Inicio', 'url' => route('dashboard')],
    ['name' => 'Revisiones', 'url' => route('revisions.index')],
    ['name' => 'Crear Componente']
]" />
<div class="container mx-auto max-w-2xl px-4 sm:px-6 lg:px-8 mt-8">
    <h1 class="text-2xl font-bold text-center mb-4">Nueva Revisión para {{ $bike->nombre }}</h1>

    <form action="{{ route('bikes.revisions.store', $bike->id) }}" method="POST" class="bg-white shadow-md rounded-lg p-6">
        @csrf

        <div class="mb-4">
            <label for="componente_id" class="block text-gray-700">Componente</label>
            <select name="componente_id" id="componente_id" class="w-full border px-4 py-2 rounded-md" required>
                @foreach($componentes as $componente)
                    <option value="{{ $componente->id }}">{{ $componente->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label for="fecha_revision" class="block text-gray-700">Fecha de Revisión</label>
            <input type="date" name="fecha_revision" id="fecha_revision" class="w-full border px-4 py-2 rounded-md" required>
        </div>

        <div class="mb-4">
            <label for="descripcion" class="block text-gray-700">Descripción</label>
            <textarea name="descripcion" id="descripcion" class="w-full border px-4 py-2 rounded-md" required></textarea>
        </div>

        <div class="mb-4">
            <label for="proxima_revision" class="block text-gray-700">Próxima Revisión (opcional)</label>
            <input type="date" name="proxima_revision" id="proxima_revision" class="w-full border px-4 py-2 rounded-md">
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">
                Guardar Revisión
            </button>
        </div>
    </form>
</div>
@endsection
