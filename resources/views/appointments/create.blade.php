@extends('layouts.app')

@section('content')
<div class="container mx-auto max-w-2xl px-4 sm:px-6 lg:px-8 mt-8">
    <h1 class="text-2xl font-bold text-center mb-4">Registrar Nueva Cita</h1>

    <form action="{{ route('appointments.store') }}" method="POST" class="bg-white shadow-md rounded-lg p-6">
        @csrf

        <!-- Selección de Bicicleta -->
        <div class="mb-4">
            <label for="bike_id" class="block text-gray-700">Bicicleta</label>
            <select name="bike_id" id="bike_id" class="w-full border px-4 py-2 rounded-md" required>
                @foreach($bikes as $bike)
                    <option value="{{ $bike->id }}">{{ $bike->nombre }} - {{ $bike->user->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Selección de Componente -->
        <div class="mb-4">
            <label for="componente_id" class="block text-gray-700">Componente (Opcional)</label>
            <select name="componente_id" id="componente_id" class="w-full border px-4 py-2 rounded-md">
                <option value="">Sin Componente</option>
                @foreach($componentes as $componente)
                    <option value="{{ $componente->id }}">{{ $componente->nombre }}</option>
                @endforeach
            </select>
        </div>

        <!-- Prioridad -->
        <div class="mb-4">
            <label for="prioridad" class="block text-gray-700">Prioridad</label>
            <select name="prioridad" id="prioridad" class="w-full border px-4 py-2 rounded-md" required>
                <option value="normal">Normal</option>
                <option value="urgente">Urgente</option>
            </select>
        </div>

        <!-- Tiempo Estimado -->
        <div class="mb-4">
            <label for="tiempo_estimado" class="block text-gray-700">Tiempo Estimado (Opcional)</label>
            <input type="text" name="tiempo_estimado" id="tiempo_estimado" 
                placeholder="Ej. 2 horas, 3 días"
                class="w-full border px-4 py-2 rounded-md">
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">
                Registrar Cita
            </button>
        </div>
    </form>
</div>
@endsection
