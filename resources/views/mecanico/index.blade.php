@extends('layouts.app')

@section('content')
@if (session('error'))
    <div class="alert alert-danger text-white bg-red-500 p-3 rounded-lg">
        {{ session('error') }}
    </div>
@endif

<div class="container mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mt-8">
    <h1 class="text-2xl font-bold text-center mb-4">Órdenes pendientes para reparar</h1>

    <!-- 🔎 Buscador + Filtros -->
    <form action="{{ route('mecanico.index') }}" method="GET" class="mb-4 flex flex-wrap items-center gap-2">
        <input type="text" name="search" placeholder="Buscar por bicicleta, usuario o componente..." 
               class="border px-4 py-2 rounded-md flex-1 min-w-[250px]"
               value="{{ request('search') }}">

        <select name="filtro" class="border px-4 py-2 rounded-md">
            <option value="todos" {{ request('filtro') == 'todos' ? 'selected' : '' }}>Todos</option>
            <option value="proceso" {{ request('filtro') == 'proceso' ? 'selected' : '' }}>En proceso</option>
            <option value="sin-hacer" {{ request('filtro') == 'sin-hacer' ? 'selected' : '' }}>Pendientes</option>
            <option value="premium" {{ request('filtro') == 'premium' ? 'selected' : '' }}>Premium</option>
            <option value="incidencia" {{ request('filtro') == 'incidencia' ? 'selected' : '' }}>Con incidencia</option>
        </select>

        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
            Buscar
        </button>
    </form>

    @if (session('success'))
        <div class="mt-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto mt-6">
        <table class="min-w-full bg-white shadow-md rounded-lg">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="py-2 px-4 text-left">Bicicleta</th>
                    <th class="py-2 px-4 text-left">Usuario</th>
                    <th class="py-2 px-4 text-left">Componentes</th>
                    <th class="py-2 px-4 text-left">Prioridad</th>
                    <th class="py-2 px-4 text-left">Tiempo de Reparación</th>
                    <th class="py-2 px-4 text-center">Acciones</th>
                    <th class="py-2 px-4 text-left">Mecánico</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-300">
                @forelse ($appointments as $appointment)
                    <tr class="hover:bg-gray-100
                        @if(!empty($appointment->descripcion_problema))
                            bg-red-200
                        @elseif($appointment->estado == 'en proceso')
                            bg-yellow-200
                        @endif
                    ">
                        <td class="py-2 px-4">{{ $appointment->bike->marca }} {{ $appointment->bike->nombre }} {{ $appointment->bike->color }}</td>
                        <td class="py-2 px-4">{{ $appointment->bike->user->name }}</td>
                        <td class="py-2 px-4">
                            @if($appointment->componentes->isNotEmpty())
                                {{ $appointment->componentes->pluck('nombre')->join(', ') }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td class="py-2 px-4">
                            <span class="px-2 py-1 rounded-full text-xs font-bold 
                                {{ $appointment->prioridad == 'urgente' ? 'bg-red-500 text-white' : ($appointment->prioridad == 'premium' ? 'bg-yellow-600 text-white' : 'bg-blue-500 text-white') }}">
                                {{ ucfirst($appointment->prioridad) }}
                            </span>
                        </td>
                        <td class="py-2 px-4">{{ $appointment->horas_total }} min</td>
                        <td class="py-2 px-4 text-center">
                            <!-- Botón Revisar (solo si está pendiente) -->
                            @if($appointment->estado == 'pendiente')
                                <form action="{{ route('mecanico.updateEstado', $appointment->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="estado" value="en proceso">
                                    <button type="submit" class="px-3 py-1 bg-yellow-500 text-white rounded-md hover:bg-yellow-600">
                                        Revisar
                                    </button>
                                </form>
                            @endif

                            <!-- Botón Completar (solo si está en proceso) -->
                            @if($appointment->estado == 'en proceso')
                                <a href="{{ route('mecanico.confirmCompletion', $appointment->id) }}" 
                                   class="block px-3 py-1 bg-green-500 text-white rounded-md hover:bg-green-600 my-1">
                                    Finalizar
                                </a>
                                <a href="{{ route('mecanico.reparacion.show', $appointment->id) }}" 
                                   class="block px-3 py-1 bg-black text-white rounded-md hover:bg-gray-800 my-1">
                                   Reparación
                                </a>
                            @endif
                        </td>
                        <td class="py-2 px-4">
                            @foreach ($appointment->usuarios_asignados as $usuario)
                                <span class="block">{{ $usuario->name }}</span>
                            @endforeach
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-gray-500">
                            No se encontraron resultados para los filtros aplicados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="mt-6">
        {{ $appointments->appends(['search' => request('search'), 'filtro' => request('filtro')])->links() }}
    </div>
</div>
@endsection
