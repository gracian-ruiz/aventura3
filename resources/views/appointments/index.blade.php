@extends('layouts.app')

@section('content')
@if (session('error'))
    <div class="alert alert-danger text-white bg-red-500 p-3 rounded-lg">
        {{ session('error') }}
    </div>
@endif

<div class="w-full px-4 sm:px-6 lg:px-8 mt-6">

    <h1 class="text-2xl font-bold text-center mb-6">Órdenes pendientes para reparar</h1>

    <!-- 🔹 Línea: Buscador + Filtros -->
    <div class="flex flex-wrap justify-between items-center mb-8 gap-3">

        <!-- 🟦 Buscador -->
        <form action="{{ route('appointments.index') }}" method="GET" class="flex items-center flex-1 max-w-2xl">
            <input 
                type="text" 
                name="search" 
                placeholder="Buscar por bicicleta, usuario o componente..." 
                class="border border-gray-300 px-4 py-2 rounded-l-md w-full text-base focus:outline-none focus:ring-2 focus:ring-blue-400"
                value="{{ request('search') }}"
            >
            <button 
                type="submit" 
                class="px-4 py-2 bg-blue-500 text-white rounded-r-md hover:bg-blue-600 whitespace-nowrap">
                Buscar
            </button>
        </form>

        <!-- 🎛️ Botones de filtro -->
        <div class="flex flex-wrap justify-end gap-2 mt-3 md:mt-0">
            @php
                $botones = [
                    'todos' => 'Todos',
                    'proceso' => 'En proceso',
                    'incidencia' => 'Incidencias',
                    'sin-hacer' => 'Sin hacer',
                    'premium' => 'Premium',
                ];
            @endphp

            @foreach ($botones as $key => $label)
                <a href="{{ route('appointments.index', ['filtro' => $key, 'search' => request('search')]) }}"
                   class="px-4 py-2 rounded-md font-semibold transition
                   {{ request('filtro', 'todos') === $key 
                        ? 'bg-blue-600 text-white shadow-md scale-105' 
                        : 'bg-gray-200 text-gray-800 hover:bg-gray-300' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    @if (session('success'))
        <div class="mt-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded text-center max-w-3xl mx-auto">
            {{ session('success') }}
        </div>
    @endif

    <!-- 🔹 Tabla -->
    <div class="overflow-x-auto mt-6">
        <table class="w-full bg-white shadow-md rounded-lg border border-gray-200 text-[15px]">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="py-2 px-4 text-left">Bicicleta</th>
                    <th class="py-2 px-4 text-left">Usuario</th>
                    <th class="py-2 px-4 text-left">Componentes</th>
                    <th class="py-2 px-4 text-left">Prioridad</th>
                    <th class="py-2 px-4 text-left">Tiempo de Reparación</th>
                    <th class="py-2 px-4 text-left">Fecha Creación</th>
                    <th class="py-2 px-4 text-left">Fecha Asignada</th>
                    <th class="py-2 px-4 text-center">Acciones</th>
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
                        <td class="py-2 px-4">
                            {{ $appointment->bike->marca }} {{ $appointment->bike->nombre }} {{ $appointment->bike->color }}
                            <br><br> <span class="text-gray-500 text-sm">ID: {{$appointment->idprograma}}</span>
                        </td>
                        <td class="py-2 px-4">{{ $appointment->bike->user->name }}</td>
                        <td class="py-2 px-4">
                            {{ $appointment->componentes->isNotEmpty() ? $appointment->componentes->pluck('nombre')->join(', ') : 'N/A' }}
                        </td>
                        <td class="py-2 px-4">
                            <span class="px-2 py-1 rounded-full text-xs font-bold 
                                @if ($appointment->prioridad == 'urgente')
                                    bg-red-500 text-white
                                @elseif ($appointment->prioridad == 'premium')
                                    bg-yellow-400 text-black border border-yellow-600 shadow-sm
                                @else
                                    bg-blue-500 text-white
                                @endif">
                                {{ ucfirst($appointment->prioridad) }}
                            </span>
                        </td>
                        <td class="py-2 px-4">{{ $appointment->horas_total }} min</td>
                        <td class="py-2 px-4">
                            {{ \Carbon\Carbon::parse($appointment->created_at)->addHour()->format('d/m/Y H:i') }}
                        </td>
                        <td class="py-2 px-4">
                            {{ $appointment->fecha_asignada ? \Carbon\Carbon::parse($appointment->fecha_asignada)->format('d/m/Y') : 'Pendiente' }}
                        </td>

                        <!-- 🔹 Acciones -->
                        <td class="py-2 px-4 text-center">
                            <div class="flex flex-col items-center justify-center space-y-2">

                                <!-- 🟩 Completar (ancho completo) -->
                                @if($appointment->estado == 'en proceso')
                                    <a href="{{ route('appointments.confirmCompletion', $appointment->id) }}" 
                                       class="block w-full px-3 py-1 bg-green-500 text-white rounded-md hover:bg-green-600">
                                        Completar
                                    </a>
                                @endif

                                <!-- ⚫ Reparación (ancho completo) -->
                                @if($appointment->estado == 'en proceso')
                                    <a href="{{ route('appointments.reparacion.show', $appointment->id) }}" 
                                       class="block w-full px-3 py-1 bg-black text-white rounded-md hover:bg-gray-800">
                                        Reparación
                                    </a>
                                @endif

                                <!-- 🟨 Revisar (pendiente) -->
                                @if($appointment->estado == 'pendiente')
                                    <form action="{{ route('appointments.updateEstado', $appointment->id) }}" method="POST" class="w-full">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="estado" value="en proceso">
                                        <button type="submit" class="w-full px-3 py-1 bg-yellow-500 text-white rounded-md hover:bg-yellow-600">
                                            Revisar
                                        </button>
                                    </form>
                                @endif

                                <!-- 🟦 Editar y Ver (2x2) -->
                                <div class="grid grid-cols-2 gap-2 w-full justify-center">
                                    <a href="{{ route('appointments.edit', $appointment->id) }}" 
                                       class="px-3 py-1 bg-blue-500 text-white rounded-md hover:bg-blue-600 text-center">
                                        Editar
                                    </a>

                                    <a href="{{ route('appointments.show', $appointment->id) }}" 
                                       class="px-3 py-1 bg-gray-500 text-white rounded-md hover:bg-gray-600 text-center">
                                        Ver
                                    </a>
                                </div>

                                <!-- 🔴 Eliminar (ancho completo) -->
                                <form action="{{ route('appointments.destroy', $appointment->id) }}" method="POST" class="w-full">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="w-full px-3 py-1 bg-red-500 text-white rounded-md hover:bg-red-600"
                                            onclick="return confirm('¿Seguro que quieres eliminar esta cita?')">
                                        Eliminar
                                    </button>
                                </form>

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-gray-500">
                            No se encontraron resultados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- 🔹 Paginación -->
    <div class="mt-6 text-center">
        {{ $appointments->appends(['search' => request('search')])->links() }}
    </div>
</div>
@endsection
