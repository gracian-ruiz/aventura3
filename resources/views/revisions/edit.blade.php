@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['name' => 'Inicio', 'url' => route('dashboard')],
    ['name' => 'Revisiones', 'url' => route('revisions.index')],
    ['name' => 'Editar Componente']
]" />
<div class="container mx-auto max-w-2xl px-4 sm:px-6 lg:px-8 mt-8">
    <h1 class="text-2xl font-bold text-center mb-4">Editar Revisión</h1>

    <form action="{{ route('bikes.revisions.update', [$bike->id, $revision->id]) }}" method="POST" class="bg-white shadow-md rounded-lg p-6">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="componente_id" class="block text-gray-700">Componente</label>
            <select name="componente_id" id="componente_id" class="w-full border px-4 py-2 rounded-md" required>
                @foreach($componentes as $componente)
                    <option value="{{ $componente->id }}" {{ $revision->componente_id == $componente->id ? 'selected' : '' }}>
                        {{ $componente->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label for="fecha_revision" class="block text-gray-700">Fecha de Revisión</label>
            <input type="date" name="fecha_revision" id="fecha_revision" value="{{ $revision->fecha_revision }}" class="w-full border px-4 py-2 rounded-md" required>
        </div>

        <div class="mb-4">
            <label for="descripcion" class="block text-gray-700">Descripción</label>
            <textarea name="descripcion" id="descripcion" class="w-full border px-4 py-2 rounded-md" required>{{ $revision->descripcion }}</textarea>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                Guardar Cambios
            </button>
        </div>
    </form>
</div>
@endsection
