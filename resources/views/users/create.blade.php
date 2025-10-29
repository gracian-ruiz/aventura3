@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['name' => 'Inicio', 'url' => route('dashboard')],
    ['name' => 'Usuarios', 'url' => route('users.index')],
    ['name' => 'Crear Usuario']
]" />

<div class="container mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 mt-8 bg-white shadow-md rounded-lg p-6">
    <h1 class="text-2xl font-bold text-center mb-6">Crear Nuevo Usuario</h1>

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

    <form action="{{ route('users.store') }}" method="POST">
        @csrf

        <!-- Nombre -->
        <div class="mb-4">
            <label for="name" class="block text-gray-700 font-semibold mb-2">Nombre</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400" required>
            @error('name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Email -->
        <div class="mb-4">
            <label for="email" class="block text-gray-700 font-semibold mb-2">Correo Electrónico</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400" required>
            @error('email')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Teléfono -->
        <div class="mb-4">
            <label for="telefono" class="block text-gray-700">Número de Teléfono</label>
            <input type="text" name="telefono" id="telefono" value="{{ old('telefono') }}" class="w-full border px-4 py-2 rounded-md" placeholder="Ejemplo: 612345678" required>
            @error('telefono')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Contraseña -->
        <div class="mb-4">
            <label for="password" class="block text-gray-700 font-semibold mb-2">Contraseña</label>
            <input type="password" id="password" name="password" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400" required>
            @error('password')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Confirmar Contraseña -->
        <div class="mb-4">
            <label for="password_confirmation" class="block text-gray-700 font-semibold mb-2">Confirmar Contraseña</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400" required>
        </div>

        <!-- Rol -->
        <div class="mb-4">
            <label for="role" class="block text-gray-700 font-semibold mb-2">Rol</label>
            <select id="role" name="role" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>Usuario</option>
                <option value="taller" {{ old('role') == 'taller' ? 'selected' : '' }}>Taller</option>
                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrador</option>
                <option value="premium" {{ old('role') == 'premium' ? 'selected' : '' }}>Premium</option>
            </select>
            @error('role')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Botones -->
        <div class="flex justify-between mt-6">
            <a href="{{ route('users.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                Cancelar
            </a>
            <button class="px-6 py-2 hover:bg-green-400 bg-green-300 text-black rounded-md font-semibold border border-green-600">
                Crear Usuario
            </button>
        </div>
    </form>
</div>
@endsection
