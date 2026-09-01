@extends('layouts.app2')

@section('content')
{{-- <x-breadcrumbs :items="[
    ['name' => 'Inicio', 'url' => route('dashboard')],
    ['name' => 'Usuarios de Alquiler', 'url' => route('rental-users.index')],
    ['name' => 'Editar Usuario de Alquiler']
]" /> --}}

<div class="w-full px-4 sm:px-6 lg:px-10 mt-8 bg-white shadow-md rounded-lg p-4 sm:p-6">
    <h1 class="text-2xl font-bold text-center mb-6">Editar Usuario de Alquiler</h1>

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-200 text-red-700 rounded">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-200 text-red-700 rounded">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('usuarios_alquiler.update', $usuario_alquiler->id) }}" method="POST">
        @csrf
        @method('PUT') <!-- Indicamos que es una actualización -->
    
        <!-- Nombre -->
        <div class="mb-4">
            <label for="nombre" class="block text-gray-700 font-semibold mb-2">Nombre</label>
            <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $usuario_alquiler->nombre) }}" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400" required>
        </div>
    
        <!-- Email -->
        <div class="mb-4">
            <label for="email" class="block text-gray-700 font-semibold mb-2">Correo Electrónico</label>
            <input type="email" id="email" name="email" value="{{ old('email', $usuario_alquiler->email) }}" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400" required>
        </div>
    
        <!-- Teléfono -->
        <div class="mb-4">
            <label for="telefono" class="block text-gray-700">Número de Teléfono</label>
            <input type="text" name="telefono" id="telefono" value="{{ old('telefono', $usuario_alquiler->telefono) }}" class="w-full border px-4 py-2 rounded-md" placeholder="Ejemplo: 612345678" required>
        </div>
    
        <div class="mb-4">
            <label for="dni" class="block text-gray-700 font-semibold mb-2">Dni</label>
            <input type="text" id="dni" name="dni" value="{{ old('dni', $usuario_alquiler->dni) }}" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400" required>
        </div>
    
        <div class="mb-4">
            <label for="direccion" class="block text-gray-700 font-semibold mb-2">Dirección</label>
            <input type="text" id="direccion" name="direccion" value="{{ old('direccion', $usuario_alquiler->direccion) }}" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400" required>
        </div>
    
        <!-- Botones -->
        <div class="flex justify-between mt-6">
            <a href="{{ route('usuarios_alquiler.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                Cancelar
            </a>
            <button class="px-6 py-2 hover:bg-green-400 bg-green-300 text-black rounded-md font-semibold border border-green-600">
                Actualizar Usuario
            </button>
        </div>
    </form>    
</div>
@endsection
