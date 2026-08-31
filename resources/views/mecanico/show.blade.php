@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-4">Detalles de la Cita</h2>

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
                        <th class="border px-4 py-2">Precio</th>
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

    <div class="mt-4 flex space-x-4">
        <!-- Botón Volver -->
        <a href="{{ ($returnUrl ?? request('return_url')) ?: route('mecanico.index', $indexContext ?? []) }}" class="inline-block px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
            Volver a Citas
        </a>

        <!-- Botón Descargar Presupuesto -->
        @if (!empty($appointment[0]->presupuesto))
        <a href="{{ route('presupuestos.pdf', $appointment[0]->presupuesto) }}" 
           class="btn text-white px-4 py-2 rounded-md" style="background-color: #E1251B;">
            <i class="fas fa-file-pdf"></i> Descargar PDF
        </a>
    @endif
    
    </div>
</div>
@endsection
