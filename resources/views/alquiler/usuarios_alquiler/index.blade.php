@extends('layouts.app2')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-10 mt-8">
    <h1 class="text-2xl font-bold text-center mb-4">Usuarios de Alquiler</h1>

    <!-- Formulario de Búsqueda -->
    <form method="GET" action="{{ route('usuarios_alquiler.index') }}" class="mb-4">
        <div class="flex flex-col sm:flex-row gap-2 sm:items-center">
            <input type="text" name="search" value="{{ request('search') }}" 
                placeholder="Buscar usuario de alquiler por nombre o teléfono..."
                class="border px-4 py-2 rounded-md w-full sm:flex-1">

            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                Buscar
            </button>
        </div>
    </form>

    <!-- Botón para añadir -->
    <div class="flex justify-end mb-4">
        <a href="{{ route('usuarios_alquiler.create') }}" 
           class="px-4 py-2 bg-green-500 text-white rounded-md shadow-md hover:bg-green-600 transition duration-200">
            + Nuevo Usuario de Alquiler
        </a>
    </div>

    @if (session('success'))
        <div class="mt-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <!-- Tabla de Usuarios de Alquiler -->
    <div class="overflow-x-auto mt-6">
        <table class="min-w-full bg-white shadow-md rounded-lg">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="py-2 px-4 text-left">ID</th>
                    <th class="py-2 px-4 text-left">Nombre</th>
                    <th class="py-2 px-4 text-left">Teléfono</th>
                    <th class="py-2 px-4 text-left">DNI</th>
                    <th class="py-2 px-4 text-left">Alquilar</th>
                    <th class="py-2 px-4 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-300">
                @foreach ($usuariosAlquiler as $usuario)
                    <tr class="hover:bg-gray-100">
                        <td class="py-2 px-4">{{ $usuario->id }}</td>
                        <td class="py-2 px-4">{{ $usuario->nombre }}</td>
                        <td class="py-2 px-4">{{ $usuario->telefono }}</td>
                        <td class="py-2 px-4">{{ $usuario->dni }}</td>
                        <td class="py-2 px-4">
                            <a href="{{ route('alquiler.create', ['usuario_alquiler' => $usuario->id]) }}" 
                                class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                                 Crear Alquiler
                             </a>
                             
                             
                        </td>
                        <td class="py-2 px-4 text-center">
                            <a href="{{ route('usuarios_alquiler.edit', $usuario->id) }}" 
                               class="px-3 py-1 bg-yellow-500 text-white rounded-md hover:bg-yellow-600">Editar</a>

                            <form action="{{ route('usuarios_alquiler.destroy', $usuario->id) }}" method="POST" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1 bg-red-500 text-white rounded-md hover:bg-red-600" onclick="return confirm('¿Seguro que quieres eliminar este usuario de alquiler?')">
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
        {{ $usuariosAlquiler->appends(['search' => request('search')])->links() }}
    </div>
</div>
@endsection
