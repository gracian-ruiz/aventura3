@extends('layouts.app')

@section('content')
<div class="container mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mt-8">
    <h1 class="text-2xl font-bold text-center mb-4">
        {{ isset($bike) ? "Revisiones de la bicicleta: " . $bike->nombre : "Todas las Revisiones" }}
    </h1>

    @if(isset($bike))
        <div class="flex justify-end mb-4">
            <a href="{{ route('bikes.revisions.create', $bike->id) }}" 
               class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">
                + Nueva Revisión
            </a>
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white shadow-md rounded-lg">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="py-2 px-4 text-left">ID</th>
                    <th class="py-2 px-4 text-left">Componente</th>
                    <th class="py-2 px-4 text-left">Fecha</th>
                    <th class="py-2 px-4 text-left">Descripción</th>
                    <th class="py-2 px-4 text-left">Próxima Revisión</th>
                    <th class="py-2 px-4 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-300">
                @foreach ($revisions as $revision)
                    <tr class="hover:bg-gray-100">
                        <td class="py-2 px-4">{{ $revision->id }}</td>
                        <td class="py-2 px-4">{{ $revision->componente->nombre ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $revision->fecha_revision }}</td>
                        <td class="py-2 px-4">{{ $revision->descripcion }}</td>
                        <td class="py-2 px-4">{{ $revision->proxima_revision ?? 'N/A' }}</td>
                        <td class="py-2 px-4 text-center">
                            <a href="{{ route('bikes.revisions.edit', [$bike->id, $revision->id]) }}" 
                               class="px-3 py-1 bg-yellow-500 text-white rounded-md hover:bg-yellow-600">Editar</a>

                            <form action="{{ route('bikes.revisions.destroy', [$bike->id, $revision->id]) }}" 
                                  method="POST" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="px-3 py-1 bg-red-500 text-white rounded-md hover:bg-red-600" 
                                        onclick="return confirm('¿Seguro que quieres eliminar esta revisión?')">
                                    Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $revisions->links() }}
    </div>
</div>
@endsection
