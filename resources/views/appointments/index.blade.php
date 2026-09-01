@extends('layouts.app')

@section('content')
@if (session('error'))
    <div class="alert alert-danger text-white bg-red-500 p-3 rounded-lg">
        {{ session('error') }}
    </div>
@endif

<div class="w-full px-4 sm:px-6 lg:px-8 mt-6">

    @php
        $indexContext = request()->only(['page', 'search', 'filtro']);
        $returnUrl = url()->full();
    @endphp

    <h1 class="app-title text-center mb-6">Órdenes pendientes para reparar</h1>

    <!-- 🔹 Línea: Buscador + Filtros -->
    <div class="app-toolbar">
    <div class="flex flex-wrap justify-between items-center gap-3">

        <!-- 🟦 Buscador -->
        <form action="{{ route('appointments.index') }}" method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center flex-1 gap-2">
            <input type="hidden" name="filtro" value="{{ request('filtro', 'todos') }}">
            <input 
                type="text" 
                name="search" 
                placeholder="Buscar por bicicleta, usuario o componente..." 
                class="border border-gray-300 px-4 py-2 rounded-md sm:rounded-l-md sm:rounded-r-none w-full text-base focus:outline-none focus:ring-2 focus:ring-blue-400"
                value="{{ request('search') }}"
            >
            <button 
                type="submit" 
                class="px-4 py-2 bg-blue-500 text-white rounded-md sm:rounded-l-none sm:rounded-r-md hover:bg-blue-600 whitespace-nowrap">
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
    <div class="mt-2 text-sm text-slate-600">
        Mostrando {{ $appointments->count() }} de {{ $appointments->total() }} órdenes
    </div>
    </div>

    @if (session('success'))
        <div class="mt-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded text-center">
            {{ session('success') }}
        </div>
    @endif

    <!-- 🔹 Tabla escritorio -->
    <div class="overflow-x-auto mt-6">
        <table class="w-full bg-white shadow-md rounded-lg border border-gray-200 text-[15px] table-mobile-friendly">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="py-2 px-4 text-left">Bicicleta</th>
                    <th class="py-2 px-4 text-left">Usuario</th>
                    <th class="py-2 px-4 text-left">Componentes</th>
                    <th class="py-2 px-4 text-left min-w-[120px]">Prioridad</th>
                    <th class="py-2 px-4 text-left">Tiempo de Reparación</th>
                    <th class="py-2 px-4 text-left">Fecha Creación</th>
                    <th class="py-2 px-4 text-left">Fecha Asignada</th>
                    <th class="py-2 px-4 text-center min-w-[220px]">Acciones</th>
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
                        <td class="py-2 px-4 min-w-[120px]">
                            <span class="app-badge
                                @if ($appointment->prioridad == 'urgente')
                                    app-badge-priority-urgente
                                @elseif ($appointment->prioridad == 'premium')
                                    app-badge-priority-premium
                                @else
                                    app-badge-priority-normal
                                @endif whitespace-nowrap min-w-[88px] justify-center">
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
                        <td class="py-2 px-4 text-center min-w-[220px]">
                            <div class="flex flex-col items-center justify-center gap-2 min-w-[190px] mx-auto">

                                <!-- 🟩 Completar (ancho completo) -->
                                @if($appointment->estado == 'en proceso')
                                    <a href="{{ route('appointments.confirmCompletion', array_merge(['appointment' => $appointment->id, 'return_url' => $returnUrl], $indexContext)) }}" 
                                       class="block w-full min-w-[112px] whitespace-nowrap px-3 py-1 bg-green-500 text-white rounded-md hover:bg-green-600 text-center">
                                        Completar
                                    </a>
                                @endif

                                <!-- ⚫ Reparación (ancho completo) -->
                                @if($appointment->estado == 'en proceso')
                                    <a href="{{ route('appointments.reparacion.show', array_merge(['appointment' => $appointment->id, 'return_url' => $returnUrl], $indexContext)) }}" 
                                       class="block w-full min-w-[112px] whitespace-nowrap px-3 py-1 bg-black text-white rounded-md hover:bg-gray-800 text-center">
                                        Reparación
                                    </a>
                                @endif

                                <!-- 🟨 Revisar (pendiente) -->
                                @if($appointment->estado == 'pendiente')
                                    <form action="{{ route('appointments.updateEstado', $appointment->id) }}" method="POST" class="w-full">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="estado" value="en proceso">
                                        <input type="hidden" name="return_page" value="{{ request('page') }}">
                                        <input type="hidden" name="return_search" value="{{ request('search') }}">
                                        <input type="hidden" name="return_filtro" value="{{ request('filtro') }}">
                                        <input type="hidden" name="return_url" value="{{ $returnUrl }}">
                                        <button type="submit" class="w-full min-w-[112px] whitespace-nowrap px-3 py-1 bg-yellow-500 text-white rounded-md hover:bg-yellow-600">
                                            Revisar
                                        </button>
                                    </form>
                                @endif

                                <!-- 🟦 Editar y Ver (2x2) -->
                                <div class="grid grid-cols-2 gap-2 w-full justify-center">
                                                <a href="{{ route('appointments.edit', array_merge(['appointment' => $appointment->id, 'return_url' => $returnUrl], $indexContext)) }}" 
                                                    class="px-3 py-1 min-w-[84px] whitespace-nowrap bg-blue-500 text-white rounded-md hover:bg-blue-600 text-center">
                                        Editar
                                    </a>

                                    <a href="{{ route('appointments.show', array_merge(['appointment' => $appointment->id, 'return_url' => $returnUrl], $indexContext)) }}" 
                                                    class="px-3 py-1 min-w-[84px] whitespace-nowrap bg-gray-500 text-white rounded-md hover:bg-gray-600 text-center">
                                        Ver
                                    </a>
                                </div>

                                <!-- 🔴 Eliminar (ancho completo) -->
                                <form action="{{ route('appointments.destroy', $appointment->id) }}" method="POST" class="w-full">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="return_page" value="{{ request('page') }}">
                                    <input type="hidden" name="return_search" value="{{ request('search') }}">
                                    <input type="hidden" name="return_filtro" value="{{ request('filtro') }}">
                                        <input type="hidden" name="return_url" value="{{ $returnUrl }}">
                                    <button type="submit" 
                                            class="w-full min-w-[112px] whitespace-nowrap px-3 py-1 bg-red-500 text-white rounded-md hover:bg-red-600"
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
        {{ $appointments->appends(request()->query())->links() }}
    </div>
</div>
@endsection
