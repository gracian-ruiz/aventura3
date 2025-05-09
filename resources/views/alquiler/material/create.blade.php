@extends('layouts.app2')

@section('content')
<div class="container mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 mt-8 bg-white shadow-md rounded-lg p-6">
    <h1 class="text-2xl font-bold text-center mb-6">Crear Material</h1>

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

    <form action="{{ route('material.store') }}" method="POST">
        @csrf

        <!-- Tipo -->
        <div class="mb-4">
            <label for="tipo" class="block text-gray-700 font-semibold mb-2">Tipo</label>
            <select name="tipo" id="tipo" class="w-full border px-4 py-2 rounded-md">
                @foreach(['mtb','electrica','carretera','paseo','niños','casco','material','bidones'] as $tipo)
                    <option value="{{ $tipo }}" @selected(old('tipo') == $tipo)>{{ ucfirst($tipo) }}</option>
                @endforeach
            </select>
        </div>

        <!-- Nombre -->
        <div class="mb-4">
            <label for="nombre" class="block text-gray-700 font-semibold mb-2">Modelo</label>
            <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" class="w-full border px-4 py-2 rounded-md" required>
        </div>

        <!-- Talla -->
        <div class="mb-4">
            <label for="talla" class="block text-gray-700 font-semibold mb-2">Talla</label>
            <input type="text" name="talla" id="talla" value="{{ old('talla') }}" class="w-full border px-4 py-2 rounded-md" placeholder="Ej. S, M, L, 54...">
        </div>

        <!-- Estado -->
        <div class="mb-4">
            <label for="estado" class="block text-gray-700 font-semibold mb-2">Estado</label>
            <select name="estado" id="estado" class="w-full border px-4 py-2 rounded-md">
                @foreach(['disponible', 'alquilado', 'mantenimiento', 'reservado'] as $estado)
                    <option value="{{ $estado }}" @selected(old('estado') == $estado)>{{ ucfirst($estado) }}</option>
                @endforeach
            </select>
        </div>

        <!-- Descripción -->
        <div class="mb-4">
            <label for="descripcion" class="block text-gray-700 font-semibold mb-2">Descripción</label>
            <textarea name="descripcion" id="descripcion" rows="3" class="w-full border px-4 py-2 rounded-md">{{ old('descripcion') }}</textarea>
        </div>

        <!-- Precio por Día -->
        <div class="mb-4">
            <label for="precio_dia" class="block text-gray-700 font-semibold mb-2">Precio por Día (€)</label>
            <input type="number" step="0.01" name="precio_dia" id="precio_dia" value="{{ old('precio_dia') }}" class="w-full border px-4 py-2 rounded-md">
        </div>

        <div class="mb-4">
            <label for="amortizacion" class="block text-gray-700 font-semibold mb-2">Amortizacion (€)</label>
            <input type="number" step="0.01" name="amortizacion" id="amortizacion" value="0" class="w-full border px-4 py-2 rounded-md" required>
        </div>

        <!-- Botones -->
        <div class="flex justify-between mt-6">
            <a href="{{ route('material.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                Cancelar
            </a>
            <button class="px-6 py-2 hover:bg-green-400 bg-green-300 text-black rounded-md font-semibold border border-green-600">
                Crear Material
            </button>
        </div>
    </form>
</div>
@endsection
