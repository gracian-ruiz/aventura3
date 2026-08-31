@extends('layouts.app')

@section('content')
<div class="container mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mt-8">
    <h1 class="text-2xl font-bold text-center mb-8">Reparación de Cita - {{ $appointment->bike->nombre }}</h1>

    <form action="{{ route('mecanico.updateReparacion', array_merge(['appointment' => $appointment->id, 'return_url' => ($returnUrl ?? request('return_url'))], $indexContext ?? [])) }}" method="POST">
        @csrf
        @method('PUT')
        <input type="hidden" name="return_page" value="{{ ($indexContext['page'] ?? '') }}">
        <input type="hidden" name="return_search" value="{{ ($indexContext['search'] ?? '') }}">
        <input type="hidden" name="return_filtro" value="{{ ($indexContext['filtro'] ?? '') }}">
        <input type="hidden" name="return_url" value="{{ $returnUrl ?? request('return_url') }}">

        <div class="space-y-6">
            <div class="mt-6">
                <p class="text-sm font-semibold mt-4 text-gray-600">
                    ID SISTEMA: {{ $appointment->idprograma }}
                </p>
                <h1 class="text-2xl font-bold text-gray-900 mt-1">
                    Cliente: {{ $data->first()->user_name ?? 'Sin asignar' }}
                </h1>
                <h1 class="block text-700 text-lg font-semibold mb-1">
                    Descripción anterior del problema:
                </h1>
                <span class="block mb-2 text-red-700 text-lg font-medium">
                    {{ $appointment->descripcion_problema ?? 'No registrada' }}
                </span>
                <br>
                <label for="descripcion_problema" class="block text-700 text-lg mb-1 font-semibold">
                    Descripción actual del problema
                </label>
                <textarea name="descripcion_problema" id="descripcion_problema"
                          class="w-full border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Descripción actual del problema, si escribes la palabra -  nada - se quitaria el incidencia y ya no estaria en rojo(quitandose el problema que hbiera de la descripcion a vacio)">{{ old('descripcion_problema') }}</textarea>
            </div>
            
            <h3 class="text-xl font-semibold text-gray-800">Componentes de la Cita</h3>

            <!-- Iterar a través de los componentes de la cita -->
            @foreach ($data as $item)
            <label class="block cursor-pointer">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between bg-white p-4 rounded-md shadow-md mb-4 border border-gray-200 peer-checked:border-green-500 peer-checked:bg-green-50 transition-all">
                    <!-- Checkbox visible y grande -->
                    <div class="flex items-start mr-4">
                        <input type="checkbox" name="componentes[{{ $item->componente_id }}][checked]" value="1"
                               @if($item->checked) checked @endif
                               class="h-6 w-6 text-green-600 border-gray-300 rounded-md focus:ring-green-500 mr-3">
                        <input type="hidden" name="componentes[{{ $item->componente_id }}][id]" value="{{ $item->componente_id }}">
                    </div>
        
                    <!-- Información del componente -->
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-800">{{ $item->component_nombre }}</h3>
                        <p class="text-gray-500 text-sm italic mt-1">{{ $item->texto }}</p>
                        <p class="text-gray-500 text-sm italic">Duración estimada: {{ $item->horas_trabajo }} minutos</p>
                    </div>
        
                    <!-- Info del usuario asignado -->
                    <div class="text-sm text-right mt-2 sm:mt-0 sm:ml-4 text-gray-600">
                        Usuario asignado: {{ $item->usuario_taller_id }}
                    </div>
                </div>
            </label>
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
    <br>
    <br>
    <div class="mt-4 d-flex justify-content-center gap-3">
        <a href="{{ route('presupuestos.pdf', $appointment->id) }}" class="px-6 mr-5 py-3 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400" style="background-color: #E1251B;">
            <i class="fas fa-file-pdf"></i> Descargar PDF Ultimo Presupuesto
        </a>
        <a href="{{ route('mecanico.edit', array_merge(['appointment' => $appointment->id, 'return_url' => ($returnUrl ?? request('return_url'))], $indexContext ?? [])) }}" 
            class="px-6 py-3 bg-green-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400">
            Editar Presupuesto
        </a>            
    </div>
    <br>
    <br>
</div>
@endsection
