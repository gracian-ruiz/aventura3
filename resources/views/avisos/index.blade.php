@extends('layouts.app')

@section('content')
@if (session('success'))
    <div class="mt-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded">
        {{ session('success') }}
    </div>
@endif

<div class="w-full px-4 sm:px-6 lg:px-10 mt-8">
    <h1 class="text-2xl font-bold text-center mb-4">Historial de Avisos Enviados</h1>

    <!-- Botón para Enviar Mensajes -->
    <div class="flex justify-end mb-4">
        <a href="{{ route('enviar.recordatorios') }}" class="px-4 py-2 bg-green-500 text-white rounded-md shadow-md hover:bg-green-600">
            📩 Enviar Mensajes
        </a>
    </div>

    <!-- Formulario de Búsqueda -->
    <form method="GET" action="{{ route('avisos.index') }}" class="mb-4">
        <div class="flex flex-col sm:flex-row gap-2 sm:items-center">
            <input type="text" name="search" value="{{ request('search') }}" 
                placeholder="Buscar por usuario, teléfono, bicicleta o componente..."
                class="border px-4 py-2 rounded-md w-full sm:flex-1">
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                Buscar
            </button>
        </div>
    </form>

    <div class="overflow-x-auto mt-6">
        <table class="min-w-full bg-white shadow-md rounded-lg">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="py-2 px-4 text-left">Usuario</th>
                    <th class="py-2 px-4 text-left">Teléfono</th>
                    <th class="py-2 px-4 text-left">Bicicleta</th>
                    <th class="py-2 px-4 text-left">Componente</th>
                    <th class="py-2 px-4 text-left">Fecha de Envío</th>
                    <th class="py-2 px-4 text-left">Mensaje</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-300">
                @foreach ($avisos as $aviso)
                    <tr class="hover:bg-gray-100">
                        <td class="py-2 px-4">{{ $aviso->user->name }}</td>
                        <td class="py-2 px-4">{{ $aviso->telefono }}</td>
                        <td class="py-2 px-4">{{ $aviso->bike->nombre ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $aviso->componente->nombre ?? 'N/A' }}</td>
                        <td class="py-2 px-4">
                            {{ \Carbon\Carbon::parse($aviso->enviado_en)->format('d/m/Y H:i') }}
                        </td>
                        <td class="py-2 px-4">{{ $aviso->mensaje }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="mt-4">
        {{ $avisos->appends(['search' => request('search')])->links() }}
    </div>
</div>
@endsection
