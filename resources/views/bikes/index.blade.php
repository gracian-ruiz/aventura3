@extends('layouts.app')

@section('content')
<div class="container mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mt-8">
    <h1 class="text-2xl font-bold text-center mb-4">Gestión de Bicicletas</h1>
    
    <!-- Formulario de Búsqueda -->
    <form method="GET" action="{{ route('bikes.index') }}" class="mb-4">
        <div class="flex justify-between">
            <input type="text" name="search" value="{{ request('search') }}" 
                placeholder="Buscar bicicleta por nombre o marca..."
                class="border px-4 py-2 rounded-md w-2/3">
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                Buscar
            </button>
        </div>
    </form>

    <!-- Botón para Añadir Nueva Bicicleta -->
    <div class="flex justify-end">
        <a href="{{ route('bikes.create') }}" class="px-4 py-2 bg-green-500 text-white rounded-md shadow-md hover:bg-green-600">
            + Nueva Bicicleta
        </a>
    </div>

    <!-- Tabla de Bicicletas -->
    <div class="overflow-x-auto mt-6">
        <table class="min-w-full bg-white shadow-md rounded-lg">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="py-2 px-4 text-left">ID</th>
                    <th class="py-2 px-4 text-left">Nombre</th>
                    <th class="py-2 px-4 text-left">Marca</th>
                    <th class="py-2 px-4 text-left">Año</th>
                    <th class="py-2 px-4 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-300">
                @foreach ($bikes as $bike)
                    <tr class="hover:bg-gray-100">
                        <td class="py-2 px-4">{{ $bike->id }}</td>
                        <td class="py-2 px-4">{{ $bike->nombre }}</td>
                        <td class="py-2 px-4">{{ $bike->marca }}</td>
                        <td class="py-2 px-4">{{ $bike->anio_modelo }}</td>
                        <td class="py-2 px-4 text-center">
                            <a href="{{ route('bikes.revisions.index', $bike->id) }}" 
                               class="px-3 py-1 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                               Revisiones
                            </a>

                            <a href="{{ route('bikes.edit', $bike->id) }}" 
                               class="px-3 py-1 bg-yellow-500 text-white rounded-md hover:bg-yellow-600">
                               Editar
                            </a>

                            <form action="{{ route('bikes.destroy', $bike->id) }}" method="POST" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1 bg-red-500 text-white rounded-md hover:bg-red-600" 
                                        onclick="return confirm('¿Seguro que quieres eliminar esta bicicleta?')">
                                    Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="mt-6">
        {{ $bikes->appends(['search' => request('search')])->links() }}
    </div>
</div>
@endsection
