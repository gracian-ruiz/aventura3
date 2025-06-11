@extends('layouts.app3')

@section('content')
<div class="container mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 mt-8 bg-white shadow-md rounded-lg p-6">
<div class="flex items-center justify-center mb-6">
    <h1 class="text-2xl font-bold text-center">Alquiler de Bicicletas Aventura Bike</h1>
    <img src="{{ asset('images/logo_taller_1.png') }}" alt="Logo Aventura Bike" class="h-20 w-auto mr-3">
</div>


    <div class="text-center my-4">
        <p class="mb-3">
            Para continuar con la reserva, rellena el formulario con el <strong>día de inicio</strong>, <strong>día de fin</strong>, <strong>modelo</strong> y <strong>talla</strong> de la bicicleta que deseas alquilar.
        </p>
        <p class="mb-3">
            Ante cualquier duda, puedes enviar un <strong>WhatsApp al <a href="https://wa.me/34654231254" target="_blank">654 231 254</a></strong> o llamar al <strong><a href="tel:950345675">950 345 675</a></strong>.
        </p>
        <p class="mb-4">
            En caso de problemas de stock o disponibilidad, <strong>nos pondremos en contacto contigo</strong> lo antes posible.
        </p>
        <a href="#modelos" class="btn btn-primary btn-sm">Pincha aquí para ver los modelos</a>
    </div>

    <br>
    <br>



    @if(session('error'))
        <div class="mb-4 p-4 bg-red-200 text-red-700 rounded">
            {{ session('error') }}
        </div>
    @endif

    @if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
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

    <form action="{{ route('addbicismontaña') }}" method="POST">
        @csrf

        <!-- Datos del usuario -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
            <div>
                <label for="nombre" class="block text-gray-700 font-semibold mb-2">Nombre / Name</label>
                <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" class="w-full px-4 py-2 border rounded-md" required>
            </div>

            <div>
                <label for="apellido" class="block text-gray-700 font-semibold mb-2">Apellidos</label>
                <input type="text" id="apellido" name="apellido" value="{{ old('apellido') }}" class="w-full px-4 py-2 border rounded-md" required>
            </div>

            <div>
                <label for="email" class="block text-gray-700 font-semibold mb-2">Correo Electrónico</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" class="w-full px-4 py-2 border rounded-md" required>
            </div>

            <div>
                <label for="telefono" class="block text-gray-700 font-semibold mb-2">Número de Teléfono</label>
                <input type="text" name="telefono" id="telefono" value="{{ old('telefono') }}" class="w-full border px-4 py-2 rounded-md" placeholder="Ejemplo: 612345678" required>
            </div>

            <div>
                <label for="dni" class="block text-gray-700 font-semibold mb-2">DNI</label>
                <input type="text" id="dni" name="dni" value="{{ old('dni') }}" class="w-full px-4 py-2 border rounded-md" required>
            </div>

            <div>
                <label for="direccion" class="block text-gray-700 font-semibold mb-2">Dirección</label>
                <input type="text" id="direccion" name="direccion" value="{{ old('direccion') }}" class="w-full px-4 py-2 border rounded-md" required>
            </div>
        </div>

        <!-- Observaciones -->
        <div class="mb-4">
            <label for="observaciones" class="block text-gray-700 font-semibold mb-2">Observaciones</label>
            <textarea id="observaciones" name="observaciones" rows="4" class="w-full px-4 py-2 border rounded-md">{{ old('observaciones') }}</textarea>
        </div>


        <!-- Bicicletas -->
        <h2 class="text-lg font-bold mt-6 mb-2">Bicicletas a alquilar</h2>

        <div id="bicicletas-container">
            <div class="bicicleta-item border p-4 mb-4 rounded-md bg-gray-50" style="background-color: rgb(91 162 255)!important;">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <!-- Honeypot: campo invisible para bots -->
                        <div style="display: none;">
                            <input type="text" name="website" tabindex="-1" autocomplete="off">
                        </div>
                        <label class="block text-gray-700 mb-1">Talla</label>
                        <select name="bicicletas[0][talla]" class="w-full border rounded-md">
                            <option value="">Seleccionar talla</option>
                            <option value="XSS">Niño</option>
                            <option value="XS">XS (145–160 cm)</option>
                            <option value="S">S (160–170 cm)</option>
                            <option value="M">M (165–175 cm)</option>
                            <option value="L">L (175–185 cm)</option>
                            <option value="XL">XL (185–195 cm)</option>
                            <option value="XXL">XXL (195+ cm)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1">Tipo</label>
                        <select name="bicicletas[0][tipo]" class="w-full border rounded-md">
                            <option value="">Seleccionar</option>
                            <option value="Mtb">Montaña</option>
                            <option value="Carretera">Carretera</option>
                            <option value="Electrica">Eléctrica</option>
                            <option value="Paseo">Paseo</option>
                            <option value="Niños">Niños</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1">Cantidad</label>
                        <select name="bicicletas[0][cantidad]" class="w-full border rounded-md">
                            <option value="">Seleccionar</option>
                            @for($i = 1; $i <= 6; $i++)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
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
                Enviar Reserva
            </button>
        </div>
    </form>
</div>
<div>
    <main class="flex-grow" id="modelos">
    <div class="container py-4">
        <div class="row g-3">

            <!-- MTB 29 -->
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white p-2">
                        <h6 class="mb-0">MTB 29” – 25€/día</h6>
                    </div>
                    <div class="card-body p-2">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr><th>Modelo</th><th>Talla</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>Scott Spark RC WC</td><td>L</td></tr>
                                <tr><td>Scott Scale RC Team</td><td>L, M</td></tr>
                                <tr><td>Specialized Chamber</td><td>XL</td></tr>
                                <tr><td>Orbea Oiz</td><td>M, L</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- MTB 26 -->
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white p-2">
                        <h6 class="mb-0">MTB 26” – 15€/día</h6>
                    </div>
                    <div class="card-body p-2">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr><th>Modelo</th><th>Talla</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>Mondraker Pensacola</td><td>M</td></tr>
                                <tr><td>Mondraker Factor LSR</td><td>M, L</td></tr>
                                <tr><td>Diamond Back Titanio</td><td>S</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Carretera -->
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white p-2">
                        <h6 class="mb-0">Carretera – 25€/día</h6>
                    </div>
                    <div class="card-body p-2">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr><th>Modelo</th><th>Talla</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>Giant Propel</td><td>S</td></tr>
                                <tr><td>Scott Addict 20</td><td>L</td></tr>
                                <tr><td>Trek Emonda</td><td>M</td></tr>
                                <tr><td>BH Prisma Carbono</td><td>M</td></tr>
                                <tr><td>Pinarello Dogma</td><td>XS</td></tr>
                                <tr><td>Wilier</td><td>M</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Eléctricas -->
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white p-2">
                        <h6 class="mb-0">Eléctricas – 45€/día</h6>
                    </div>
                    <div class="card-body p-2">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr><th>Modelo</th><th>Talla</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>Scott E-Aspect 50</td><td>M</td></tr>
                                <tr><td>Giant Fathom Pro</td><td>S</td></tr>
                                <tr><td>Trek Powerfly</td><td>S</td></tr>
                                <tr><td>Olympia EX900</td><td>M</td></tr>
                                <tr><td>Specialized Levo</td><td>M</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Paseo -->
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white p-2">
                        <h6 class="mb-0">Paseo</h6>
                    </div>
                    <div class="card-body p-2">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr><th>Modelo</th><th>Talla</th><th>Precio</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>Sonora Domingos</td><td>S, M, L</td><td>18€</td></tr>
                                <tr><td>Bicicleta de niño</td><td>-</td><td>18€</td></tr>
                                <tr><td>Paseo eléctrica</td><td>-</td><td>25€</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        <p class="text-muted mt-3 small fst-italic">
            Las bicicletas se darán dependiendo de la disponibilidad. En caso de no tener el modelo específico, se proporcionará otro modelo equivalente.
        </p>
    </div>
    </main>

</div>

<script>
    let contadorBicicletas = 1;

    function agregarBicicleta() {
        const container = document.getElementById('bicicletas-container');
        const newItem = document.querySelector('.bicicleta-item').cloneNode(true);
        const selects = newItem.querySelectorAll('select, input');

        selects.forEach(select => {
            if (select.name.includes('bicicletas')) {
                select.name = select.name.replace(/\[\d+\]/, `[${contadorBicicletas}]`);
                select.value = '';
            }
        });

        container.appendChild(newItem);
        contadorBicicletas++;
    }
</script>
@endsection
