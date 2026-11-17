@extends('layouts.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-10 mt-8">
    <h1 class="text-2xl font-bold text-center mb-4">Gestión de Componentes</h1>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div class="flex items-center gap-2">
            <label for="per_page" class="text-sm font-medium text-gray-700">Resultados por página</label>
            <form method="GET" class="inline-block">
                @if(request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif
                <select id="per_page" name="per_page" onchange="this.form.submit()" class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    @foreach([10, 50, 100, 200] as $option)
                        <option value="{{ $option }}" {{ (string) $perPage === (string) $option ? 'selected' : '' }}>{{ $option }}</option>
                    @endforeach
                </select>
            </form>
        </div>

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
                    <th class="py-2 px-4 text-left">Minutos Taller</th>
                    <th class="py-2 px-4 text-left">Precio (€)</th>
                    <th class="py-2 px-4 text-left">Orden</th> <!-- Nueva columna Orden -->
                    <th class="py-2 px-4 text-left">Descripcion</th>
                    <th class="py-2 px-4 text-left">Enviar mensaje</th>
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
                        <td class="py-2 px-4">{{ $component->hora_taller ?? 'N/A' }} Minutos</td>
                        <td class="py-2 px-4">{{ $component->precio }} €</td>
                        <td class="py-2 px-4">{{ $component->orden ?? 'N/A' }}</td> <!-- Mostrar el campo Orden -->
                        <td class="py-2 px-4">{{ $component->descripcion }}</td>
                        <td class="py-2 px-4">{{ $component->enviar_mensaje ? 'Sí' : 'No' }}</td>
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
