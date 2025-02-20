@extends('layouts.app')

@section('content')

<x-breadcrumbs :items="[
    ['name' => 'Inicio', 'url' => route('dashboard')],
    ['name' => 'Componentes', 'url' => route('components.index')],
    ['name' => 'Editar Componente']
]" />


<h1 class="text-2xl font-bold text-center mb-4">Editar Componente</h1>

<div class="max-w-lg mx-auto bg-white p-6 rounded-lg shadow-md">
    <form action="{{ route('components.update', $component->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="nombre" class="block text-gray-700 font-bold mb-2">Nombre del Componente</label>
            <input type="text" id="nombre" name="nombre" value="{{ $component->nombre }}" 
                   class="w-full p-2 border rounded-md focus:ring focus:ring-blue-300">
        </div>

        <div class="mb-4">
            <label for="fecha_preaviso" class="block text-gray-700 font-bold mb-2">Fecha de Preaviso</label>
            <input type="number" id="fecha_preaviso" name="fecha_preaviso" value="{{ $component->fecha_preaviso }}" 
                   class="w-full p-2 border rounded-md focus:ring focus:ring-blue-300">
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">
                Guardar Cambios
            </button>
        </div>
    </form>
</div>

@endsection
