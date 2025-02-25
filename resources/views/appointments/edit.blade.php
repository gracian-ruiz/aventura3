@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['name' => 'Inicio', 'url' => route('dashboard')],
    ['name' => 'Citas', 'url' => route('appointments.index')],
    ['name' => 'Editar Cita']
]" />

<div class="container mx-auto max-w-2xl px-4 sm:px-6 lg:px-8 mt-8">
    <h1 class="text-2xl font-bold text-center mb-4">Editar Cita</h1>

    <form action="{{ route('appointments.update', $appointment->id) }}" method="POST" class="bg-white shadow-md rounded-lg p-6">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="bike_id" class="block text-gray-700">Bicicleta</label>
            <select name="bike_id" id="bike_id" class="w-full border px-4 py-2 rounded-md">
                @foreach($bikes as $bike)
                    <option value="{{ $bike->id }}" {{ $appointment->bike_id == $bike->id ? 'selected' : '' }}>
                        {{ $bike->nombre }} - {{ $bike->user->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label for="componente_id" class="block text-gray-700">Componente (Opcional)</label>
            <select name="componente_id" id="componente_id" class="w-full border px-4 py-2 rounded-md">
                <option value="">Sin Componente</option>
                @foreach($componentes as $componente)
                    <option value="{{ $componente->id }}" {{ $appointment->componente_id == $componente->id ? 'selected' : '' }}>
                        {{ $componente->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label for="prioridad" class="block text-gray-700">Prioridad</label>
            <select name="prioridad" id="prioridad" class="w-full border px-4 py-2 rounded-md">
                <option value="normal" {{ $appointment->prioridad == 'normal' ? 'selected' : '' }}>Normal</option>
                <option value="urgente" {{ $appointment->prioridad == 'urgente' ? 'selected' : '' }}>Urgente</option>
            </select>
        </div>

        <div class="mb-4">
            <label for="tiempo_estimado" class="block text-gray-700">Tiempo Estimado</label>
            <input type="text" name="tiempo_estimado" id="tiempo_estimado" class="w-full border px-4 py-2 rounded-md" value="{{ $appointment->tiempo_estimado }}">
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">
                Guardar Cambios
            </button>
        </div>
    </form>
</div>
@endsection
