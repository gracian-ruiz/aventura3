@extends('layouts.app2')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-10 mt-8">
    <h1 class="text-2xl font-bold text-center mb-4">Materiales de Alquiler</h1>

    <!-- Formulario de Búsqueda -->
    <form method="GET" action="{{ route('material.index') }}" class="mb-4">
        <div class="flex flex-col sm:flex-row gap-2 sm:items-center">
            <input type="text" name="search" value="{{ request('search') }}" 
                placeholder="Buscar material por nombre o tipo..."
                class="border px-4 py-2 rounded-md w-full sm:flex-1">

            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                Buscar
            </button>
        </div>
    </form>

    <!-- Botón para añadir -->
    <div class="flex justify-end mb-4">
        <a href="{{ route('material.create') }}" 
           class="px-4 py-2 bg-green-500 text-white rounded-md shadow-md hover:bg-green-600 transition duration-200">
            + Nuevo Material
        </a>
    </div>

    @if (session('success'))
        <div class="mt-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <!-- Tabla de Materiales -->
    <div class="overflow-x-auto mt-6">
        <table class="min-w-full bg-white shadow-md rounded-lg">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="py-2 px-4 text-left">ID</th>
                    <th class="py-2 px-4 text-left">Tipo</th>
                    <th class="py-2 px-4 text-left">Modelo</th>
                    <th class="py-2 px-4 text-left">Talla</th>
                    <th class="py-2 px-4 text-left">Estado</th>
                    <th class="py-2 px-4 text-left">Precio/día (€)</th>
                    <th class="py-2 px-4 text-left">Amortización (€)</th>
                    <th class="py-2 px-4 text-left">Precio Reserva (€)</th>
                    <th class="py-2 px-4 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-300">
                @php
                    $tipos = [
                        'mtb26' => 'MTB 26',
                        'mtb29' => 'MTB 29',
                        'mtb29doble' => 'MTB 29 Doble',
                        'electricapaseo' => 'Eléctrica Paseo',
                        'electricadoble' => 'Eléctrica Doble',
                        'electricarigida' => 'Eléctrica Rígida',
                        'carretera' => 'Carretera',
                        'paseo' => 'Paseo',
                        'niños' => 'Niños',
                        'casco' => 'Casco',
                        'material' => 'Material',
                        'bidones' => 'Bidones',
                    ];
                @endphp
                @foreach ($materials as $material)
                    <tr class="hover:bg-gray-100">
                        <td class="py-2 px-4">{{ $material->id }}</td>
                        <td class="py-2 px-4 capitalize">
                            {{ $tipos[$material->tipo] ?? ucfirst($material->tipo) }}
                        </td>
                        <td class="py-2 px-4">{{ $material->nombre }}</td>
                        <td class="py-2 px-4">{{ $material->talla ?? '-' }}</td>
                        <td class="py-2 px-4 capitalize">{{ $material->estado }}</td>
                        <td class="py-2 px-4">{{ number_format($material->precio_dia, 2) }} eu</td>
                        <td class="py-2 px-4">{{ number_format($material->amortizacion, 2) }} eu</td>
                        <td class="py-2 px-4">{{ number_format($material->reserva_precio, 2) }} eu</td>
                        <td class="py-2 px-4 text-center">
                            <a href="{{ route('material.edit', $material->id) }}" 
                               class="px-3 py-1 bg-yellow-500 text-white rounded-md hover:bg-yellow-600">Editar</a>

                            <form action="{{ route('material.destroy', $material->id) }}" method="POST" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                    class="px-3 py-1 bg-red-500 text-white rounded-md hover:bg-red-600" 
                                    onclick="return confirm('¿Seguro que quieres eliminar este material?')">
                                    Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="mt-6">
        {{ $materials->appends(['search' => request('search')])->links() }}
    </div>
</div>
@endsection
