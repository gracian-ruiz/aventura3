@extends('layouts.app')

@section('content')
<div class="container mx-auto px-3 sm:px-4 lg:px-6 py-4 sm:py-6">
    <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold mb-4 sm:mb-6">Histórico de Citas</h1>

    @if (session('success'))
        <div class="mt-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded text-sm sm:text-base">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-md overflow-hidden mt-4 sm:mt-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="py-2 sm:py-3 px-2 sm:px-4 text-left text-xs sm:text-sm font-medium">ID</th>
                        <th class="py-2 sm:py-3 px-2 sm:px-4 text-left text-xs sm:text-sm font-medium">Bicicleta</th>
                        <th class="py-2 sm:py-3 px-2 sm:px-4 text-left text-xs sm:text-sm font-medium">Usuario</th>
                        <th class="py-2 sm:py-3 px-2 sm:px-4 text-left text-xs sm:text-sm font-medium">Usuario Taller</th>
                        <th class="py-2 sm:py-3 px-2 sm:px-4 text-left text-xs sm:text-sm font-medium">Prioridad</th>
                        <th class="py-2 sm:py-3 px-2 sm:px-4 text-left text-xs sm:text-sm font-medium">Tiempo Estimado</th>
                        <th class="py-2 sm:py-3 px-2 sm:px-4 text-left text-xs sm:text-sm font-medium">Fecha Creación</th>
                        <th class="py-2 sm:py-3 px-2 sm:px-4 text-left text-xs sm:text-sm font-medium">Fecha Completada</th>
                        <th class="py-2 sm:py-3 px-2 sm:px-4 text-center text-xs sm:text-sm font-medium">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($completedAppointments as $appointment)
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 sm:py-3 px-2 sm:px-4 text-xs sm:text-sm">{{ $appointment->id }}</td>
                        <td class="py-2 sm:py-3 px-2 sm:px-4 text-xs sm:text-sm">{{ $appointment->bike->nombre }}</td>
                        <td class="py-2 sm:py-3 px-2 sm:px-4 text-xs sm:text-sm">{{ $appointment->bike->user->name }}</td>
                        <td class="py-2 sm:py-3 px-2 sm:px-4 text-xs sm:text-sm">{{ $appointment->usuario_taller_id ?? 'N/A' }}</td>
                        <td class="py-2 sm:py-3 px-2 sm:px-4">
                            <span class="px-2 py-1 rounded-full text-xs font-bold 
                                {{ $appointment->prioridad == 'urgente' ? 'bg-red-500 text-white' : 'bg-blue-500 text-white' }}">
                                {{ ucfirst($appointment->prioridad) }}
                            </span>
                        </td>
                        <td class="py-2 sm:py-3 px-2 sm:px-4 text-xs sm:text-sm">{{ $appointment->tiempo_estimado ?? 'N/A' }}</td>
                        <td class="py-2 sm:py-3 px-2 sm:px-4 text-xs sm:text-sm">{{ \Carbon\Carbon::parse($appointment->created_at)->format('d/m/Y H:i') }}</td>
                        <td class="py-2 sm:py-3 px-2 sm:px-4 text-xs sm:text-sm">{{ \Carbon\Carbon::parse($appointment->updated_at)->format('d/m/Y H:i') }}</td>
                        <td class="py-2 sm:py-3 px-2 sm:px-4">
                            <div class="flex flex-col sm:flex-row gap-2 justify-center items-center">
                                <!-- Botón Ver -->
                                <a href="{{ route('appointments.show', $appointment->id) }}" 
                                   class="w-full sm:w-auto px-2 sm:px-3 py-1 bg-blue-500 text-white rounded-md hover:bg-blue-600 text-xs sm:text-sm text-center">
                                    Ver
                                </a>
                                <!-- Botón Eliminar -->
                                <form action="{{ route('appointments.destroy', $appointment->id) }}" method="POST" class="w-full sm:w-auto">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="w-full px-2 sm:px-3 py-1 bg-red-500 text-white rounded-md hover:bg-red-600 text-xs sm:text-sm" 
                                            onclick="return confirm('¿Seguro que quieres eliminar esta cita del historial?')">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>

    <!-- Paginación -->
    <div class="mt-4 sm:mt-6">
        {{ $completedAppointments->links() }}
    </div>
</div>
@endsection
