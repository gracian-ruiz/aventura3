@extends('layouts.app')

@section('content')
<div class="container mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mt-8">
    <!-- Encabezado -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">
                    <i class="bi bi-bicycle"></i> Bicicletas de {{ $user->name }}
                </h1>
                <p class="text-gray-600 mt-1">
                    <i class="bi bi-envelope"></i> {{ $user->email }} 
                    @if($user->telefono)
                        | <i class="bi bi-telephone"></i> {{ $user->telefono }}
                    @endif
                </p>
            </div>
            <a href="{{ route('users.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition">
                <i class="bi bi-arrow-left"></i> Volver a Usuarios
            </a>
        </div>
    </div>

    <!-- Mensaje de éxito -->
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-md flex items-center">
            <i class="bi bi-check-circle-fill mr-2"></i>
            {{ session('success') }}
        </div>
    @endif

    <!-- Sección: Añadir Nueva Bicicleta -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6 border-l-4 border-green-500">
        <h2 class="text-xl font-bold text-gray-800 mb-4">
            <i class="bi bi-plus-circle"></i> Añadir Nueva Bicicleta
        </h2>

        <form method="POST" action="{{ route('users.bikes.store', $user->id) }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @csrf

            <div>
                <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                <input type="text" name="nombre" id="nombre" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500"
                       placeholder="Ej: Mountain Bike Pro"
                       value="{{ old('nombre') }}">
                @error('nombre')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="marca" class="block text-sm font-medium text-gray-700 mb-1">Marca *</label>
                <input type="text" name="marca" id="marca" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500"
                       placeholder="Ej: Trek, Specialized..."
                       value="{{ old('marca') }}">
                @error('marca')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="anio_modelo" class="block text-sm font-medium text-gray-700 mb-1">Año del Modelo *</label>
                <input type="number" name="anio_modelo" id="anio_modelo" required
                       min="1900" max="{{ date('Y') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500"
                       placeholder="{{ date('Y') }}"
                       value="{{ old('anio_modelo') }}">
                @error('anio_modelo')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="color" class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                <input type="text" name="color" id="color"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500"
                       placeholder="Ej: Rojo, Azul..."
                       value="{{ old('color') }}">
                @error('color')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="kilometros" class="block text-sm font-medium text-gray-700 mb-1">Kilómetros</label>
                <input type="number" name="kilometros" id="kilometros" min="0"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500"
                       placeholder="Ej: 5000"
                       value="{{ old('kilometros') }}">
                @error('kilometros')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition font-medium shadow-md">
                    <i class="bi bi-plus-lg"></i> Añadir Bicicleta
                </button>
            </div>
        </form>
    </div>

    <!-- Sección: Listado de Bicicletas -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="bg-gray-800 text-white px-6 py-4">
            <h2 class="text-xl font-bold">
                <i class="bi bi-list-ul"></i> Bicicletas Registradas ({{ $bikes->total() }})
            </h2>
        </div>

        @if($bikes->isEmpty())
            <div class="p-8 text-center text-gray-500">
                <i class="bi bi-inbox text-6xl mb-3"></i>
                <p class="text-lg">Este usuario aún no tiene bicicletas registradas.</p>
                <p class="text-sm">Usa el formulario de arriba para añadir la primera bicicleta.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">ID</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Nombre</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Marca</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Año</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Color</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Kilómetros</th>
                            <th class="py-3 px-4 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($bikes as $bike)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-3 px-4 text-sm font-medium text-gray-900">{{ $bike->id }}</td>
                                <td class="py-3 px-4 text-sm text-gray-900">{{ $bike->nombre }}</td>
                                <td class="py-3 px-4 text-sm text-gray-700">{{ $bike->marca }}</td>
                                <td class="py-3 px-4 text-sm text-gray-700">{{ $bike->anio_modelo }}</td>
                                <td class="py-3 px-4 text-sm text-gray-700">{{ $bike->color ?? 'N/A' }}</td>
                                <td class="py-3 px-4 text-sm text-gray-700">{{ $bike->kilometros ?? 'N/A' }}</td>
                                <td class="py-3 px-4 text-center space-x-2">
                                    <a href="{{ route('bikes.revisions.index', ['bike' => $bike->id, 'from' => 'user_bikes']) }}" 
                                       class="inline-block px-3 py-1 bg-blue-500 text-white text-xs rounded-md hover:bg-blue-600 transition">
                                        <i class="bi bi-wrench"></i> Revisiones
                                    </a>

                                    <a href="{{ route('bikes.edit', ['bike' => $bike->id, 'from' => 'user_bikes']) }}" 
                                       class="inline-block px-3 py-1 bg-yellow-500 text-white text-xs rounded-md hover:bg-yellow-600 transition">
                                        <i class="bi bi-pencil"></i> Editar
                                    </a>

                                    <form action="{{ route('bikes.destroy', $bike->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="redirect_to" value="user.bikes">
                                        <button type="submit" 
                                                class="px-3 py-1 bg-red-500 text-white text-xs rounded-md hover:bg-red-600 transition"
                                                onclick="return confirm('¿Estás seguro de eliminar esta bicicleta?')">
                                            <i class="bi bi-trash"></i> Eliminar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                {{ $bikes->links() }}
            </div>
        @endif
    </div>
</div>

<style>
    @media (max-width: 768px) {
        .grid.grid-cols-1.md\:grid-cols-2.lg\:grid-cols-3 {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection
