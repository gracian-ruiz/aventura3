@extends('layouts.app')

@section('content')
<div class="w-full lg:max-w-5xl mx-auto app-panel p-6">
    <h2 class="app-title mb-4">Detalles de la Cita</h2>

    @if ($appointment->isNotEmpty())
        <!-- Información de la Bicicleta -->
        <div class="mb-4">
            <p class="text-gray-700"><strong>Bicicleta:</strong> {{ $appointment[0]->bike_nombre }}</p>
            <p class="text-gray-700"><strong>Marca:</strong> {{ $appointment[0]->bike_marca }}</p>
            <p class="text-gray-700"><strong>Fecha de la Cita:</strong> {{ $appointment[0]->appointment_fecha }}</p>
        </div>

        <!-- Componentes y Trabajos -->
        @if ($appointment->first()->component_nombre)
            <h3 class="text-xl font-semibold mb-3">Trabajos a realizar</h3>
            <table class="w-full border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border px-4 py-2">Componente</th>
                        <th class="border px-4 py-2">Minutos Taller</th>
                        <th class="border px-4 py-2">Precio Mano Obra</th>
                        <th class="border px-4 py-2">Precio Material</th>
                        <th class="border px-4 py-2">Descuento</th>
                        <th class="border px-4 py-2">Total Línea</th>
                        <th class="border px-4 py-2">Descripción</th>
                        <th class="border px-4 py-2">Mecanico que edito</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($appointment as $item)
                        <tr>
                            <td class="border px-4 py-2">{{ $item->component_nombre }}</td>
                            <td class="border px-4 py-2">{{ $item->horas_trabajo }}</td>
                            <td class="border px-4 py-2">{{ $item->total_precio }}€</td>
                            <td class="border px-4 py-2">{{ number_format((float) ($item->precio_material ?? 0), 2) }}€</td>
                            <td class="border px-4 py-2">{{ number_format((float) ($item->descuento ?? 0), 2) }}€</td>
                            <td class="border px-4 py-2">{{ number_format(max(((float) $item->total_precio + (float) ($item->precio_material ?? 0)) - (float) ($item->descuento ?? 0), 0), 2) }}€</td>
                            <td class="border px-4 py-2">{{ $item->texto }}</td>
                            <td class="border px-4 py-2">{{ $item->usuario_taller_id }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-gray-600">No hay componentes asociados a esta cita.</p>
        @endif
    @else
        <p class="text-gray-600">No se encontró la cita.</p>
    @endif

    <div class="mt-6 flex flex-wrap gap-3">
        <!-- Botón Volver -->
        <a href="{{ ($returnUrl ?? request('return_url')) ?: route('appointments.index', $indexContext ?? []) }}" 
        class="app-btn app-btn-primary">
            Volver a Citas
        </a>

        <!-- Botón Descargar Presupuesto -->
        @if (!empty($appointment[0]->appointment_id))
            <a href="{{ route('presupuestos.pdf', $appointment[0]->appointment_id) }}" 
            class="app-btn app-btn-pdf">
                <i class="fas fa-file-pdf"></i> Descargar PDF
            </a>
        @endif

        <!-- Botón Quitar del Taller -->
        <form action="{{ route('appointments.quitarOrdenTaller', $appointment[0]->appointment_id) }}" 
            method="POST" 
            onsubmit="return confirm('⚠️ ¿Seguro que quieres devolver esta cita al estado presupuesto?');">
            @csrf
            @method('PATCH')
            <input type="hidden" name="return_page" value="{{ ($indexContext['page'] ?? '') }}">
            <input type="hidden" name="return_search" value="{{ ($indexContext['search'] ?? '') }}">
            <input type="hidden" name="return_filtro" value="{{ ($indexContext['filtro'] ?? '') }}">
            <input type="hidden" name="return_url" value="{{ $returnUrl ?? request('return_url') }}">
            <button type="submit" 
                    class="app-btn bg-yellow-500 text-white hover:bg-yellow-600">
                Quitar del Taller
            </button>
        </form>
    </div>

</div>
@endsection
