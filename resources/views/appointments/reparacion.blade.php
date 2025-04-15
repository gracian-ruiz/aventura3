@extends('layouts.app')

@section('content')
<div class="container mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mt-8">
    <h1 class="text-2xl font-bold text-center mb-8">Reparación de Cita - {{ $appointment->bike->nombre }}</h1>

    <form action="{{ route('appointments.updateReparacion', $appointment->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            <h3 class="text-xl font-semibold text-gray-800">Componentes de la Cita</h3>

            <!-- Iterar a través de los componentes de la cita -->
            @foreach ($data as $item)
                <div class="flex items-center justify-between bg-white p-4 rounded-md shadow-sm mb-4">
                    <!-- Checkbox con ID del componente y su estado 'checked' -->
                    <div class="flex items-center">
                        <input type="checkbox" name="componentes[{{ $item->componente_id }}][checked]" value="1"
                               @if($item->checked) checked @endif
                               class="form-checkbox h-5 w-5 text-green-500">
                        <input type="hidden" name="componentes[{{ $item->componente_id }}][id]" value="{{ $item->componente_id }}">
                        <label for="componentes[]" class="ml-3 text-lg text-gray-700">{{ $item->component_nombre }}</label>
                    </div>

                    <!-- Mostrar el texto descriptivo del componente (solo visualización) -->
                    <div class="ml-4 w-3/4">
                        <p class="text-gray-500 text-sm italic">{{ $item->texto }}</p>
                    </div>
                    <div>
                        <p>Usuario {{ $item->usuario_taller_id }}</p>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-6">
            <label class="block text-gray-700 font-semibold mb-1">Kilómetros anteriores:</label>
            <span class="block mb-2 text-blue-700 font-medium">
                {{ $appointment->bike->kilometros ?? 'No registrados' }} km
            </span>
        
            <label for="kilometros" class="block text-gray-700 mb-1">Kilómetros actuales de la bicicleta</label>
            <input type="number" name="kilometros" id="kilometros"
                   class="w-full border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                   placeholder="Introduce los kilómetros actuales"
                   min="0" value="{{ old('kilometros') }}">
        </div>
        

        <div class="mt-6">
            <button type="submit" class="px-6 py-3 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400">
                Actualizar Reparación
            </button>
        </div>
    </form>
</div>
@endsection
