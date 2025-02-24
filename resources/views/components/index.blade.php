@extends('layouts.app')

@section('content')
<div class="container mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mt-8">
    <h1 class="text-2xl font-bold text-center mb-4">Gestión de Componentes</h1>

    <!-- Botón para Añadir Nuevo Componente -->
    <div class="flex justify-end mb-4">
        <a href="{{ route('components.create') }}" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">
            + Nuevo Componente
        </a>
    </div>

    @if (session('success'))
        <div class="mt-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white shadow-md rounded-lg">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="py-2 px-4 text-left">ID</th>
                    <th class="py-2 px-4 text-left">Nombre</th>
                    <th class="py-2 px-4 text-left">Preaviso (días)</th>
                    <th class="py-2 px-4 text-left">Intervalo de Revisión (días)</th>
                    <th class="py-2 px-4 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-300">
                @foreach ($components as $component)
                    <tr class="hover:bg-gray-100">
                        <td class="py-2 px-4">{{ $component->id }}</td>
                        <td class="py-2 px-4">{{ $component->nombre }}</td>
                        <td class="py-2 px-4">{{ $component->fecha_preaviso ?? 'N/A' }} días</td>
                        <td class="py-2 px-4">{{ $component->fecha_revision ?? 'N/A' }} días</td>
                        <td class="py-2 px-4 text-center">
                            <a href="{{ route('components.edit', $component->id) }}" class="px-3 py-1 bg-yellow-500 text-white rounded-md hover:bg-yellow-600">Editar</a>
                            
                            <form action="{{ route('components.destroy', $component->id) }}" method="POST" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1 bg-red-500 text-white rounded-md hover:bg-red-600" onclick="return confirm('¿Seguro que quieres eliminar este componente?')">
                                    Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $components->links() }}
    </div>
</div>
@endsection
