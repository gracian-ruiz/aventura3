@extends('layouts.app')

@section('content')
<div class="container mx-auto max-w-2xl px-4 sm:px-6 lg:px-8 mt-8">
    <h1 class="text-2xl font-bold text-center mb-4">Nuevo Componente</h1>

    <form action="{{ route('components.store') }}" method="POST" class="bg-white shadow-md rounded-lg p-6">
        @csrf

        <div class="mb-4">
            <label for="nombre" class="block text-gray-700">Nombre</label>
            <input type="text" name="nombre" id="nombre" class="w-full border px-4 py-2 rounded-md" required>
        </div>

        <div class="mb-4">
            <label for="fecha_preaviso" class="block text-gray-700">Fecha de Preaviso (opcional)</label>
            <input type="number" name="fecha_preaviso" id="fecha_preaviso" class="w-full border px-4 py-2 rounded-md">
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">
                Guardar Componente
            </button>
        </div>
    </form>
</div>
@endsection
