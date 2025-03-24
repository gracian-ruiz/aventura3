@extends('layouts.app')

@section('content')
<div class="container mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mt-8">
    <h1 class="text-2xl font-bold text-center mb-4">Listado de Presupuestos</h1>

    <!-- Formulario de Búsqueda -->
    <form method="GET" action="{{ route('presupuestos.index') }}" class="mb-4">
        <div class="flex justify-between">
            <input type="text" name="search" value="{{ request('search') }}" 
                placeholder="Buscar presupuesto por cliente o bicicleta..."
                class="border px-4 py-2 rounded-md w-2/3">
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                Buscar
            </button>
        </div>
    </form>

    <!-- Mensajes de éxito -->
    @if (session('success'))
        <div class="mt-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white shadow-md rounded-lg">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="py-2 px-4 text-left">ID</th>
                    <th class="py-2 px-4 text-left">Cliente</th>
                    <th class="py-2 px-4 text-left">Bicicleta</th>
                    <th class="py-2 px-4 text-left">Fecha</th>
                    <th class="py-2 px-4 text-left">Estado</th>
                    <th class="py-2 px-4 text-center">Aprobar / Denegar</th>
                    <th class="py-2 px-4 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-300">
                @foreach ($presupuestos as $presupuesto)
                    <tr class="hover:bg-gray-100">
                        <td class="py-2 px-4">{{ $presupuesto->id }}</td>
                        <td class="py-2 px-4">{{ $presupuesto->user_nombre ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $presupuesto->bike_nombre ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ date('d/m/Y', strtotime($presupuesto->created_at)) }}</td>
                        <td class="py-2 px-4">
                            <span class="px-2 py-1 text-white rounded-md 
                                {{ $presupuesto->estado === 'pendiente' ? 'bg-yellow-500' : ($presupuesto->estado === 'aprobado' ? 'bg-green-500' : 'bg-red-500') }}">
                                {{ ucfirst($presupuesto->estado) }}
                            </span>
                        </td>
                        <td class="py-2 px-4 text-center">
                            @if ($presupuesto->estado === 'pendiente')
                                <form action="{{ route('presupuestos.actualizarEstado', $presupuesto->id) }}" method="POST" class="mb-1">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="estado" value="aprobado">
                                    <button type="submit" class="px-3 py-1 bg-green-500 text-white rounded-md hover:bg-green-600 w-full">
                                        Aprobar
                                    </button>
                                </form>
                                <form action="{{ route('presupuestos.actualizarEstado', $presupuesto->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="estado" value="denegado">
                                    <button type="submit" class="px-3 py-1 bg-red-500 text-white rounded-md hover:bg-red-600 w-full">
                                        Denegar
                                    </button>
                                </form>
                            @endif
                        </td>
                        <td class="py-2 px-4 text-center">
                            <div class="flex flex-col space-y-2">
                                <a href="{{ route('presupuestos.edit', $presupuesto->id) }}"
                                    class="px-3 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600">
                                    Editar
                                </a>                       
                                <a href="{{ url("/presupuestos/{$presupuesto->id}/factura") }}" 
                                    class="px-3 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                                     Ver Factura
                                 </a>
                                <a href="{{ route('presupuestos.pdf', $presupuesto->id) }}" 
                                   class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
                                    Descargar PDF
                                </a>
                                <a href="{{ route('presupuesto.enviar', ['clienteId' => $presupuesto->user_id, 'presupuestoId' => $presupuesto->id]) }}" 
                                    class="bg-green-500 text-white px-4 py-2 rounded">
                                    📩 Enviar PDF por WhatsApp
                                </a>                                
                            </div>
                        </td>
                                                                     
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="mt-6">
        {{ $presupuestos->appends(['search' => request('search')])->links() }}
    </div>
</div>
@endsection
