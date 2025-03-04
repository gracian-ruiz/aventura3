@extends('layouts.app')

@section('content')
<div class="container mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 mt-8">
    <h1 class="text-2xl font-bold text-center mb-4">Editar Cita</h1>

    <form action="{{ route('appointments.updatedos', $appointment->id) }}" method="POST" class="bg-white shadow-md rounded-lg p-6">
        @csrf
        @method('PUT')

        <!-- Bicicleta y Usuario -->
        <div class="mb-4">
            <label class="block text-gray-700">Bicicleta:</label>
            <p class="text-gray-900 font-semibold">{{ $appointment->bike->nombre }} - {{ $appointment->bike->user->name }}</p>
        </div>

        <!-- Descripción del Problema -->
        <div class="mb-4">
            <label for="descripcion_problema" class="block text-gray-700">Descripción del Problema</label>
            <textarea name="descripcion_problema" class="w-full border px-4 py-2 rounded-md" required>{{ $appointment->descripcion_problema }}</textarea>
        </div>

        <!-- Tiempo Estimado -->
        <div class="mb-4">
            <label for="tiempo_estimado" class="block text-gray-700">Tiempo Estimado (minutos)</label>
            <input type="number" name="tiempo_estimado" value="{{ $appointment->tiempo_estimado }}" class="w-full border px-4 py-2 rounded-md" required>
        </div>

        <!-- Componentes a Revisar -->
        <div class="mb-4">
            <label class="block text-gray-700">Componentes a Revisar</label>
            <select name="componentes[]" multiple class="w-full border px-4 py-2 rounded-md">
                @foreach($componentes as $componente)
                    <option value="{{ $componente->id }}" 
                        {{ $appointment->componentes->contains($componente->id) ? 'selected' : '' }}>
                        {{ $componente->nombre }}
                    </option>
                @endforeach
            </select>
            <p class="text-sm text-gray-500 mt-2">Puedes seleccionar varios componentes.</p>
        </div>

        <!-- Resumen de la Cita -->
        <div class="bg-gray-100 p-4 rounded-md shadow-inner">
            <h2 class="text-lg font-semibold mb-2">Resumen de la Cita:</h2>
            <p><strong>Descripción:</strong> {{ $appointment->descripcion_problema }}</p>
            <p><strong>Tiempo Estimado:</strong> {{ $appointment->tiempo_estimado }} min</p>
            <p><strong>Componentes:</strong> 
                @if($appointment->componentes->isNotEmpty())
                    {{ $appointment->componentes->pluck('nombre')->join(', ') }}
                @else
                    N/A
                @endif
            </p>
        </div>

        <!-- Botones -->
        <div class="flex justify-between mt-6">
            <a href="{{ route('appointments.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                Cancelar
            </a>
            <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">
                Guardar Cambios
            </button>
        </div>
    </form>
</div>
@endsection
