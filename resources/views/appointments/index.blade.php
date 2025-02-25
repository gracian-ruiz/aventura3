@extends('layouts.app')

@section('content')
<div class="container mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mt-8">
    <h1 class="text-2xl font-bold text-center mb-4">Citas Pendientes</h1>

    <!-- Buscador -->
    <form action="{{ route('appointments.index') }}" method="GET" class="mb-4 flex items-center">
        <input type="text" name="search" placeholder="Buscar por bicicleta, usuario o componente..." 
               class="border px-4 py-2 rounded-md w-1/2" value="{{ request('search') }}">
        <button type="submit" class="ml-2 px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
            Buscar
        </button>
    </form>

    <!-- Botón para Añadir Nueva Cita -->
    <div class="flex justify-end mb-4">
        <a href="{{ route('appointments.create') }}" class="px-4 py-2 bg-green-500 text-white rounded-md shadow-md hover:bg-green-600">
            + Nueva Cita
        </a>
    </div>

    @if (session('success'))
        <div class="mt-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto mt-6">
        <table class="min-w-full bg-white shadow-md rounded-lg">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="py-2 px-4 text-left">ID</th>
                    <th class="py-2 px-4 text-left">Bicicleta</th>
                    <th class="py-2 px-4 text-left">Usuario</th>
                    <th class="py-2 px-4 text-left">Componente</th>
                    <th class="py-2 px-4 text-left">Prioridad</th>
                    <th class="py-2 px-4 text-left">Tiempo Estimado</th>
                    <th class="py-2 px-4 text-left">Fecha Creación</th>
                    <th class="py-2 px-4 text-left">Fecha Asignada</th>
                    <th class="py-2 px-4 text-left">Descripción Problema</th>
                    <th class="py-2 px-4 text-left">Estimación Reparación</th>
                    <th class="py-2 px-4 text-left">Estado</th>
                    <th class="py-2 px-4 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-300">
                @foreach ($appointments as $appointment)
                    <tr class="hover:bg-gray-100">
                        <td class="py-2 px-4">{{ $appointment->id }}</td>
                        <td class="py-2 px-4">{{ $appointment->bike->nombre }}</td>
                        <td class="py-2 px-4">{{ $appointment->bike->user->name }}</td>
                        <td class="py-2 px-4">{{ $appointment->componente->nombre ?? 'N/A' }}</td>
                        <td class="py-2 px-4">
                            <span class="px-2 py-1 rounded-full text-xs font-bold 
                                {{ $appointment->prioridad == 'urgente' ? 'bg-red-500 text-white' : 'bg-blue-500 text-white' }}">
                                {{ ucfirst($appointment->prioridad) }}
                            </span>
                        </td>
                        <td class="py-2 px-4">{{ $appointment->tiempo_estimado }} min</td>
                        <td class="py-2 px-4">{{ \Carbon\Carbon::parse($appointment->created_at)->format('d/m/Y H:i') }}</td>
                        <td class="py-2 px-4">
                            {{ $appointment->fecha_asignada ? \Carbon\Carbon::parse($appointment->fecha_asignada)->format('d/m/Y') : 'Pendiente' }}
                        </td>
                        <td class="py-2 px-4">{{ $appointment->descripcion_problema ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $appointment->estimacion_reparacion ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ ucfirst($appointment->estado) }}</td>
                        <td class="py-2 px-4 text-center">
                            @if($appointment->estado == 'pendiente')
                                <form action="{{ route('appointments.updateEstado', $appointment->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="px-3 py-1 bg-green-500 text-white rounded-md hover:bg-green-600">
                                        Completar
                                    </button>
                                </form>
                            @endif

                            <form action="{{ route('appointments.destroy', $appointment->id) }}" method="POST" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1 bg-red-500 text-white rounded-md hover:bg-red-600" 
                                    onclick="return confirm('¿Seguro que quieres eliminar esta cita?')">
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
        {{ $appointments->appends(['search' => request('search')])->links() }}
    </div>
</div>
@endsection
