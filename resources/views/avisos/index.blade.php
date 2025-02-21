@extends('layouts.app')

@section('content')
<div class="container mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mt-8">
    <h1 class="text-2xl font-bold text-center mb-4">Historial de Avisos Enviados</h1>

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

    <div class="mt-4">
        {{ $avisos->links() }}
    </div>
</div>
@endsection
