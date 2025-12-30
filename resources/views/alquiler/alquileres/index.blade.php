@extends('layouts.app2')

@section('content')
<div class="container mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mt-8">
    <h1 class="text-2xl font-bold text-center mb-6">Listado de Alquileres</h1>

    <!-- Mensaje de éxito -->
    @if (session('success'))
        <div class="mt-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <!-- Formulario de búsqueda -->
    <form method="GET" action="{{ route('alquileres.index') }}" class="mb-4">
        <div class="flex justify-between">
            <input type="text" name="search" value="{{ request('search') }}" 
                placeholder="Buscar usuario de alquiler por nombre o teléfono..."
                class="border px-4 py-2 rounded-md w-2/3">

            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                Buscar
            </button>
        </div>
    </form>


    <!-- Tabla de Alquileres -->
    <div class="overflow-x-auto mt-6">
        <table class="min-w-full bg-white shadow-md rounded-lg">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="py-2 px-4 text-left">ID</th>
                    <th class="py-2 px-4 text-left">Usuario</th>
                    <th class="py-2 px-4 text-left">Fecha inicio</th>
                    <th class="py-2 px-4 text-left">Fecha fin</th>
                    <th class="py-2 px-4 text-left">Estado</th>
                    <th class="py-2 px-4 text-left">Web</th>
                    <th class="py-2 px-4 text-left">Total (€)</th>
                    <th class="py-2 px-4 text-left">Acciones</th>
                    <th class="py-2 px-4 text-left">Ver</th>
                    <th class="py-2 px-4 text-left">Eliminar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-300">
                @forelse($alquileres as $alquiler)
                <tr class="
                @if($alquiler->fallo === 1) bg-yellow-300
                @elseif($alquiler->web === 1 && $alquiler->estado === 'reservado') bg-blue-300
                @elseif($alquiler->estado === 'reservado') bg-red-300
                @elseif($alquiler->estado === 'activo') bg-green-400
                @elseif($alquiler->estado === 'finalizado') bg-gray-100
                @else bg-white
                @endif hover:bg-opacity-80">
            
                
                <td class="py-2 px-4">{{ $alquiler->id }}</td>
                <td class="py-2 px-4">{{ $alquiler->usuario->nombre ?? '—' }}</td>
                <td class="py-2 px-4">{{ $alquiler->fecha_inicio }}</td>
                <td class="py-2 px-4">{{ $alquiler->fecha_fin }}</td>
                <td class="py-2 px-4 capitalize">{{ $alquiler->estado }}</td>
                <td class="py-2 px-4">
                    @if($alquiler->web === 1)
                        <span class="px-2 py-1 bg-blue-500 text-white text-xs font-bold rounded-full">Web</span>
                    @else
                        <span class="px-2 py-1 bg-gray-500 text-white text-xs font-bold rounded-full">Presencial</span>
                    @endif
                </td>
                <td class="py-2 px-4">{{ number_format($alquiler->total_precio - $alquiler->reserva_precio, 2) }} €</td>
            
                <td class="py-2 px-4">
                    <a href="{{ route('alquileres.edit', $alquiler) }}" class="text-blue-600 hover:underline">Editar</a>
                </td>
                <td class="py-2 px-4">
                    <a href="{{ route('alquileres.show', $alquiler->id) }}" class="text-blue-600 hover:underline">Ver</a>
                </td>
                <td class="py-2 px-4">
                    <form action="{{ route('alquileres.destroy', $alquiler->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este alquiler?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                    </form>
                </td>
            </tr>
            
            
                @empty
                    <tr>
                        <td colspan="10" class="py-4 px-4 text-center text-gray-500">
                            No hay alquileres registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="mt-6">
        {{ $alquileres->links() }}
    </div>
</div>
@endsection
