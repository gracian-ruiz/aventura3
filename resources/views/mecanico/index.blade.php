@extends('layouts.app')

@section('content')
@if (session('error'))
    <div class="alert alert-danger text-white bg-red-500 p-3 rounded-lg">
        {{ session('error') }}
    </div>
@endif

<!-- 🔹 Contenedor de ancho completo -->
<div class="w-full px-4 sm:px-6 lg:px-10 mt-8">
    @php
        $indexContext = request()->only(['page', 'search', 'filtro']);
        $returnUrl = url()->full();
    @endphp

    <h1 class="app-title text-center mb-4">Órdenes pendientes para reparar</h1>

    <!-- 🔎 Buscador + Filtros -->
    <div class="app-toolbar">
        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-3">
        <form action="{{ route('mecanico.index') }}" method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center flex-1 gap-2 mb-0">
            <input type="hidden" name="filtro" value="{{ request('filtro', 'todos') }}">
            <input type="text" name="search" placeholder="Buscar por bicicleta, usuario o componente..." 
                class="border border-gray-300 px-4 py-2 rounded-md sm:rounded-l-md sm:rounded-r-none w-full text-base focus:outline-none focus:ring-2 focus:ring-blue-400"
                value="{{ request('search') }}">

            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md sm:rounded-l-none sm:rounded-r-md hover:bg-blue-600 whitespace-nowrap">
                Buscar
            </button>
        </form>
        <div class="flex flex-wrap gap-2 xl:justify-end xl:max-w-[48%]">
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
                <a href="{{ route('mecanico.index', ['filtro' => $key, 'search' => request('search')]) }}"
                   class="px-3 py-2 rounded-md font-semibold transition
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
        <div class="mt-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <!-- 🔹 Tabla a pantalla completa -->
    <div class="overflow-x-auto mt-6">
        <table class="w-full bg-white shadow-md rounded-lg table-mobile-friendly">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="py-2 px-4 text-left">Bicicleta</th>
                    <th class="py-2 px-4 text-left">Usuario</th>
                    <th class="py-2 px-4 text-left">Componentes</th>
                    <th class="py-2 px-4 text-left min-w-[120px]">Prioridad</th>
                    <th class="py-2 px-4 text-left">Tiempo de Reparación</th>
                    <th class="py-2 px-4 text-center min-w-[220px]">Acciones</th>
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
                        <td class="py-2 px-4 text-center min-w-[220px]">
                            <div class="flex flex-col items-center justify-center gap-2 min-w-[190px] mx-auto">
                            <!-- Botón Revisar (solo si está pendiente) -->
                            @if($appointment->estado == 'pendiente')
                                <form action="{{ route('mecanico.updateEstado', $appointment->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="estado" value="en proceso">
                                    <input type="hidden" name="return_page" value="{{ request('page') }}">
                                    <input type="hidden" name="return_search" value="{{ request('search') }}">
                                    <input type="hidden" name="return_filtro" value="{{ request('filtro') }}">
                                    <input type="hidden" name="return_url" value="{{ $returnUrl }}">
                                    <button type="submit" class="px-3 py-1 min-w-[112px] whitespace-nowrap bg-yellow-500 text-white rounded-md hover:bg-yellow-600">
                                        Revisar
                                    </button>
                                </form>
                            @endif

                            <!-- Botón Completar (solo si está en proceso) -->
                            @if($appointment->estado == 'en proceso')
                                <a href="{{ route('mecanico.confirmCompletion', array_merge(['appointment' => $appointment->id, 'return_url' => $returnUrl], $indexContext)) }}" 
                                   class="block px-3 py-1 min-w-[112px] whitespace-nowrap bg-green-500 text-white rounded-md hover:bg-green-600 text-center">
                                    Finalizar
                                </a>
                                <a href="{{ route('mecanico.reparacion.show', array_merge(['appointment' => $appointment->id, 'return_url' => $returnUrl], $indexContext)) }}" 
                                   class="block px-3 py-1 min-w-[112px] whitespace-nowrap bg-black text-white rounded-md hover:bg-gray-800 text-center">
                                   Reparación
                                </a>
                            @endif
                            
                            <a href="{{ route('mecanico.show', array_merge(['appointment' => $appointment->id, 'return_url' => $returnUrl], $indexContext)) }}" 
                               class="px-3 py-1 min-w-[84px] whitespace-nowrap bg-gray-500 text-white rounded-md hover:bg-gray-600 text-center">
                                Ver
                            </a>
                            </div>
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
        {{ $appointments->appends(request()->query())->links() }}
    </div>
</div>
@endsection
