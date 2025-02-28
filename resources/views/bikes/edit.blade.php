@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['name' => 'Inicio', 'url' => route('dashboard')],
    ['name' => 'Bicicletas', 'url' => route('bikes.index')],
    ['name' => 'Editar Componente']
]" />
<div class="container mx-auto max-w-2xl px-4 sm:px-6 lg:px-8 mt-8">
    <h1 class="text-2xl font-bold text-center mb-4">Editar Bicicleta</h1>

    <form action="{{ route('bikes.update', $bike->id) }}" method="POST" class="bg-white shadow-md rounded-lg p-6">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="user_id" class="block text-gray-700">Usuario</label>
            <select name="user_id" id="user_id" class="w-full border px-4 py-2 rounded-md">
                <option value="">Seleccionar Usuario</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" {{ $bike->user_id == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <!-- Activar Select2 en el Select de Usuarios -->
        <script>
            $(document).ready(function() {
                $('#user_id').select2({
                    placeholder: "Buscar usuario...",
                    allowClear: true,
                    width: '100%'
                });
            });
        </script>
        

        <div class="mb-4">
            <label for="nombre" class="block text-gray-700">Nombre</label>
            <input type="text" name="nombre" id="nombre" class="w-full border px-4 py-2 rounded-md" value="{{ $bike->nombre }}" required>
        </div>

        <div class="mb-4">
            <label for="marca" class="block text-gray-700">Marca</label>
            <input type="text" name="marca" id="marca" class="w-full border px-4 py-2 rounded-md" value="{{ $bike->marca }}" required>
        </div>

        <div class="mb-4">
            <label for="anio_modelo" class="block text-gray-700">Año Modelo</label>
            <input type="number" name="anio_modelo" id="anio_modelo" class="w-full border px-4 py-2 rounded-md" value="{{ $bike->anio_modelo }}" required min="1900" max="{{ date('Y') }}">
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">
                Guardar Cambios
            </button>
        </div>
    </form>
</div>
@endsection
