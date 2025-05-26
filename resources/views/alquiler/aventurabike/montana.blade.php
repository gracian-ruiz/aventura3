@extends('layouts.app3')

@section('content')

<div class="container mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 mt-8 bg-white shadow-md rounded-lg p-6">
    <h1 class="text-2xl font-bold text-center mb-6">Alquiler bicicleta</h1>

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

    <form action="{{ route('usuarios_alquiler.store') }}" method="POST">
        @csrf

        <!-- Datos del usuario -->
        <div class="mb-4">
            <label for="nombre" class="block text-gray-700 font-semibold mb-2">Nombre / Name</label>
            <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" class="w-full px-4 py-2 border rounded-md" required>
        </div>

        <div class="mb-4">
            <label for="apellido" class="block text-gray-700 font-semibold mb-2">Apellidos</label>
            <input type="text" id="apellido" name="apellido" value="{{ old('apellido') }}" class="w-full px-4 py-2 border rounded-md" required>
        </div>

        <div class="mb-4">
            <label for="email" class="block text-gray-700 font-semibold mb-2">Correo Electrónico</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" class="w-full px-4 py-2 border rounded-md" required>
        </div>

        <div class="mb-4">
            <label for="telefono" class="block text-gray-700">Número de Teléfono</label>
            <input type="text" name="telefono" id="telefono" value="{{ old('telefono') }}" class="w-full border px-4 py-2 rounded-md" placeholder="Ejemplo: 612345678" required>
        </div>

        <div class="mb-4">
            <label for="dni" class="block text-gray-700 font-semibold mb-2">DNI</label>
            <input type="text" id="dni" name="dni" value="{{ old('dni') }}" class="w-full px-4 py-2 border rounded-md" required>
        </div>

        <div class="mb-4">
            <label for="direccion" class="block text-gray-700 font-semibold mb-2">Dirección</label>
            <input type="text" id="direccion" name="direccion" value="{{ old('direccion') }}" class="w-full px-4 py-2 border rounded-md" required>
        </div>

        <!-- Bicicletas -->
        <h2 class="text-lg font-bold mt-6 mb-2">Bicicletas a alquilar</h2>

        <div id="bicicletas-container">
            <div class="bicicleta-item border p-4 mb-4 rounded-md bg-gray-50">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-gray-700 mb-1">Talla</label>
                        <select name="bicicletas[0][talla]" class="w-full border rounded-md">
                            <option value="">Seleccionar</option>
                            <option>XS</option>
                            <option>S</option>
                            <option>M</option>
                            <option>L</option>
                            <option>XL</option>
                            <option>XXL</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1">Tipo</label>
                        <select name="bicicletas[0][tipo]" class="w-full border rounded-md tipo-select" onchange="mostrarTamanio(this)">
                            <option value="">Seleccionar</option>
                            <option value="Montaña">Montaña</option>
                            <option value="Carretera">Carretera</option>
                            <option value="Eléctrica">Eléctrica</option>
                            <option value="Paseo">Paseo</option>
                            <option value="Niños">Niños</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1">Cantidad</label>
                        <select name="bicicletas[0][cantidad]" class="w-full border rounded-md">
                            @for ($i = 1; $i <= 7; $i++)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div class="mt-2 tamano-montana hidden">
                    <label class="block text-gray-700 mb-1">Tamaño (solo para Montaña)</label>
                    <select name="bicicletas[0][tamano_montana]" class="w-full border rounded-md">
                        <option value="">Seleccionar</option>
                        <option>26</option>
                        <option>29</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-gray-700 mb-1">Fecha de inicio</label>
                        <input type="date" name="bicicletas[0][fecha_inicio]" class="w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1">Fecha de fin</label>
                        <input type="date" name="bicicletas[0][fecha_fin]" class="w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                </div>
            </div>
        </div>

        <button type="button" onclick="agregarBicicleta()" class="bg-blue-500 text-white px-4 py-2 rounded-md mt-4">Añadir otra bicicleta</button>

        <!-- Botones -->
        <div class="flex justify-between mt-6">
            <a href="{{ route('usuarios_alquiler.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                Cancelar
            </a>
            <button type="submit" class="px-6 py-2 bg-green-400 hover:bg-green-500 text-black rounded-md font-semibold">
                Crear Usuario
            </button>
        </div>
    </form>
</div>

<script>
let contadorBicicletas = 1;

function agregarBicicleta() {
    const container = document.getElementById('bicicletas-container');
    const newItem = document.querySelector('.bicicleta-item').cloneNode(true);
    const selects = newItem.querySelectorAll('select, input');

    selects.forEach(select => {
        if (select.name.includes('bicicletas')) {
            select.name = select.name.replace(/\[0\]/, `[${contadorBicicletas}]`);
            select.value = '';
        }
    });

    newItem.querySelector('.tamano-montana').classList.add('hidden');
    container.appendChild(newItem);
    contadorBicicletas++;
}

function mostrarTamanio(select) {
    const container = select.closest('.bicicleta-item');
    const tamanoDiv = container.querySelector('.tamano-montana');
    if (select.value === 'Montaña') {
        tamanoDiv.classList.remove('hidden');
    } else {
        tamanoDiv.classList.add('hidden');
        const tamanoSelect = tamanoDiv.querySelector('select');
        tamanoSelect.value = '';
    }
}
</script>

@endsection
