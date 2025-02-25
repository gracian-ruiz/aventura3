@extends('layouts.app')

@section('content')
<div class="container mx-auto max-w-2xl px-4 sm:px-6 lg:px-8 mt-8">
    <h1 class="text-2xl font-bold text-center mb-4">Nueva Cita</h1>

    <form action="{{ route('appointments.store') }}" method="POST" class="bg-white shadow-md rounded-lg p-6">
        @csrf

        <!-- Selección de Bicicleta -->
        <div class="mb-4">
            <label for="bike_id" class="block text-gray-700">Bicicleta</label>
            <select name="bike_id" id="bike_id" class="w-full border px-4 py-2 rounded-md" required>
                <option value="">Selecciona una bicicleta</option>
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

        <!-- Descripción del Problema -->
        <div class="mb-4">
            <label for="descripcion_problema" class="block text-gray-700">Descripción del Problema</label>
            <textarea name="descripcion_problema" id="descripcion_problema" class="w-full border px-4 py-2 rounded-md" rows="3" required></textarea>
        </div>

        <!-- Estimación de Reparación -->
        <div class="mb-4">
            <label for="estimacion_reparacion" class="block text-gray-700">Estimación de Reparación</label>
            <textarea name="estimacion_reparacion" id="estimacion_reparacion" class="w-full border px-4 py-2 rounded-md" rows="3" required></textarea>
        </div>

        <!-- Tiempo Estimado en Minutos -->
        <div class="mb-4">
            <label for="tiempo_estimado" class="block text-gray-700">Tiempo Estimado (en minutos)</label>
            <input type="number" name="tiempo_estimado" id="tiempo_estimado" class="w-full border px-4 py-2 rounded-md" min="1" required>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">
                Guardar Cita
            </button>
        </div>
    </form>
</div>

<!-- Agregar Select2 para búsqueda en los selects -->
@section('scripts')
<script>
    $(document).ready(function() {
        $('#bike_id, #componente_id').select2({
            placeholder: "Selecciona una opción...",
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endsection

@endsection
