@extends('layouts.app')

@section('content')
@if (session('error'))
    <div class="alert alert-danger text-white bg-red-500 p-3 rounded-lg">
        {{ session('error') }}
    </div>
@endif

<div class="container mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mt-8">
    <h1 class="text-2xl font-bold text-center mb-4">Orden Pendientes para reparar</h1>

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
                    <th class="py-2 px-4 text-left">Bicicleta</th>
                    <th class="py-2 px-4 text-left">Usuario</th>
                    <th class="py-2 px-4 text-left">Componentes</th>
                    <th class="py-2 px-4 text-left">Prioridad</th>
                    <th class="py-2 px-4 text-left">Tiempo de Reparacion</th>
                    <th class="py-2 px-4 text-left">Fecha Creación</th>
                    <th class="py-2 px-4 text-left">Fecha Asignada</th>
                    <th class="py-2 px-4 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-300">
                @foreach ($appointments as $appointment)
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
                                {{ $appointment->prioridad == 'urgente' ? 'bg-red-500 text-white' : 'bg-blue-500 text-white' }}">
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
                        <td class="py-2 px-4 text-center">
                            <!-- Botón Revisar (solo si está pendiente) -->
                            @if($appointment->estado == 'pendiente')
                                <form action="{{ route('appointments.updateEstado', $appointment->id) }}" method="POST" class="inline-block">
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
                                <a href="{{ route('appointments.confirmCompletion', $appointment->id) }}" 
                                    class="block px-3 py-1 bg-green-500 text-white rounded-md hover:bg-green-600 my-1">
                                    Completar
                                </a>
                            @endif

                            @if($appointment->estado == 'en proceso')
                            <a href="{{ route('appointments.reparacion.show', $appointment->id) }}" 
                                class="block px-3 py-1 bg-black text-white rounded-md hover:bg-gray-800 my-1">
                                Reparación
                            </a>
                            @endif
                        

                            <!-- Botón Editar -->
                            <a href="{{ route('appointments.edit', $appointment->id) }}" 
                                class="block px-3 py-1 bg-blue-500 text-white rounded-md hover:bg-blue-600 my-1">
                                Editar
                            </a>
                            <a href="{{ route('appointments.show', $appointment->id) }}" class="px-3 py-1 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                                Ver
                            </a>

                            <!-- Botón Eliminar -->
                            <form action="{{ route('appointments.destroy', $appointment->id) }}" method="POST" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1 bg-red-500 text-white rounded-md hover:bg-red-600 my-1" 
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
