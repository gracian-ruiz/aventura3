@extends('layouts.app')

@section('content')
<!-- 🔹 Contenedor a pantalla completa -->
<div class="w-full px-4 sm:px-6 lg:px-10 mt-8">

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

    <!-- 🔹 Tabla a ancho completo -->
    <div class="overflow-x-auto mt-6">
        <table class="w-full bg-white shadow-md rounded-lg">
            <thead class="bg-gray-800 text-white text-sm">
                <tr>
                    <th class="py-2 px-4 text-left">ID</th>
                    <th class="py-2 px-4 text-left">Cliente</th>
                    <th class="py-2 px-4 text-left">Bicicleta</th>
                    <th class="py-2 px-4 text-left">Fecha</th>
                    <th class="py-2 px-4 text-left">Prioridad</th>
                    <th class="py-2 px-4 text-center">Aprobar / Denegar</th>
                    <th class="py-2 px-4 text-center">Editar / Ver Presupuesto</th>
                    <th class="py-2 px-4 text-center">Eliminar / Enviar Correo</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-300 text-sm">
                @foreach ($presupuestos as $presupuesto)
                    <tr class="hover:bg-gray-100 
                        {{ $presupuesto->estado === 'denegado' ? 'bg-red-200' : '' }}
                        {{ $presupuesto->estado === 'vacia' ? 'bg-yellow-200' : '' }}">
                        
                        <td class="py-2 px-4">
                            {{ $presupuesto->id }}<br>
                            <span class="text-gray-500 text-xs">{{ $presupuesto->idprograma }}</span>
                        </td>

                        <td class="py-2 px-4">{{ $presupuesto->user_nombre ?? 'N/A' }}</td>

                        <td class="py-2 px-4">
                            {{ $presupuesto->marca ?? 'N/A' }} {{ $presupuesto->bike_nombre ?? 'N/A' }}
                        </td>

                        <td class="py-2 px-4">
                            {{ date('d/m/Y', strtotime($presupuesto->created_at)) }}
                        </td>

                        <td class="py-2 px-4 text-center">
                            <span class="px-2 py-1 rounded-full text-xs font-bold 
                                @if ($presupuesto->prioridad == 'urgente')
                                    bg-red-500 text-white
                                @elseif ($presupuesto->prioridad == 'premium')
                                    bg-amber-400 text-black border border-amber-600 shadow-sm premium
                                @else
                                    bg-blue-500 text-white
                                @endif">
                                {{ ucfirst($presupuesto->prioridad) }}
                            </span>
                        </td>

                        <style>
                            .premium {
                                background: rgb(242, 255, 99);
                            }
                        </style>

                        <!-- 🟩 Aprobar / Denegar -->
                        <td class="py-2 px-4 text-center">
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
                        </td>

                        <!-- 🟨 Editar / Ver Presupuesto -->
                        <td class="py-2 px-4 text-center">
                            <div class="flex flex-col space-y-2">
                                <a href="{{ route('presupuestos.edit', $presupuesto->id) }}"
                                   class="px-3 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600">
                                   Editar
                                </a>                       

                                <a href="{{ url("/presupuestos/{$presupuesto->id}/factura") }}" 
                                   class="px-3 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                                   Ver Presupuesto
                                </a>

                                @if ($presupuesto->presupuesto_enviado)
                                    <p class="font-bold text-green-700 text-xs mt-2">SE HA ENVIADO EL MENSAJE</p>
                                @endif
                            </div>
                        </td>

                        <!-- 🟥 Eliminar / Enviar correo -->
                        <td class="py-2 px-4 text-center">
                            <div class="flex flex-col space-y-2">
                                <!-- Eliminar -->
                                <form action="{{ route('presupuestos.destroy', $presupuesto->id) }}" method="POST" 
                                      onsubmit="return confirm('¿Estás seguro de que deseas eliminar este presupuesto?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 w-full">
                                        Eliminar
                                    </button>
                                </form>

                                <!-- Enviar correo debajo -->
                                <a href="{{ url("/enviar/correo/presupuesto/{$presupuesto->id}") }}" 
                                    class="px-3 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                                     Enviar correo
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
