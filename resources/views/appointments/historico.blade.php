@extends('layouts.app')

@section('content')
<div class="container mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mt-8">
    <h1 class="text-2xl font-bold text-center mb-4">Histórico de Citas</h1>

    <div class="flex justify-between mb-4">
        <a href="{{ route('appointments.index') }}" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
            Volver a Citas Pendientes
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white shadow-md rounded-lg">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="py-2 px-4 text-left">ID</th>
                    <th class="py-2 px-4 text-left">Usuario</th>
                    <th class="py-2 px-4 text-left">Bicicleta</th>
                    <th class="py-2 px-4 text-left">Prioridad</th>
                    <th class="py-2 px-4 text-left">Fecha de Creación</th>
                    <th class="py-2 px-4 text-left">Fecha de Finalización</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-300">
                @foreach ($appointments as $appointment)
                    <tr class="hover:bg-gray-100">
                        <td class="py-2 px-4">{{ $appointment->id }}</td>
                        <td class="py-2 px-4">{{ $appointment->bike->user->name ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $appointment->bike->nombre ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ ucfirst($appointment->prioridad) }}</td>
                        <td class="py-2 px-4">{{ $appointment->created_at->format('d/m/Y H:i') }}</td>
                        <td class="py-2 px-4">{{ $appointment->updated_at->format('d/m/Y H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $appointments->links() }}
    </div>
</div>
@endsection
