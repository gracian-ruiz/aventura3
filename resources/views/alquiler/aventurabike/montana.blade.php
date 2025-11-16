@extends('layouts.app3')

@section('content')
<div class="container mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 mt-8 bg-white shadow-md rounded-lg p-6">
<div class="text-right mb-3">
    <a href="{{ url('lang/es') }}" class="px-3 py-1 border rounded {{ app()->getLocale() === 'es' ? 'bg-gray-800 text-white' : 'bg-gray-200' }}">
        🇪🇸 ES
    </a>
    <a href="{{ url('lang/en') }}" class="px-3 py-1 border rounded {{ app()->getLocale() === 'en' ? 'bg-gray-800 text-white' : 'bg-gray-200' }}">
        🇬🇧 EN
    </a>
</div>

<!-- Encabezado con logo y enlace a la izquierda -->
<div class="flex items-center justify-between mb-6">
    <!-- Enlace a la izquierda -->
    <a href="https://aventurabike.es/" class="text-blue-600 hover:underline text-sm">
        ← {{ __('messages.back_link') }}
    </a>

    <!-- Título centrado -->
    <div class="flex-1 text-center">
        <h1 class="text-2xl font-bold">
            {{ __('messages.title') }}<br>
        </h1>
    </div>

    <!-- Logo a la derecha -->
    <img src="{{ asset('images/logo_taller_1.png') }}" alt="Logo Aventura Bike" class="h-20 w-auto ml-3">
</div>

    <!-- Texto introductorio -->
<div class="text-center my-4">
{{--     <p class="mb-3">
        {{ __('messages.form_intro') }}
    </p> --}}

    <!-- AVISO IMPORTANTE -->
    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 p-4 mb-4" role="alert">
        <p class="font-bold text-base" style="color: red">
            {{ __('messages.recogida') }}:
        </p>
        <p class="font-bold text-base">
            🕒 {{ __('messages.important') }}:
        </p>
        <ul class="text-sm mt-2 text-left sm:text-center">
            <li class="mb-1">
                📅 <strong>{{ __('messages.saturday_hours') }}.</strong><br>
                🕛 <strong>{{ __('messages.sunday_closed') }}.</strong>
            </li>
            <li class="mb-1">
                🚲 {{ __('messages.pickup_option') }}
            </li>
        </ul>
        <p class="text-xs text-gray-600 mt-2 sm:text-center">{{ __('messages.saturday_time') }}</p>
    </div>

    <p class="mb-3">
        {!! __('messages.contact_info') !!}
    </p>

    <p class="mb-4">
        {{ __('messages.stock_notice') ?? 'En caso de problemas de stock o disponibilidad, nos pondremos en contacto contigo lo antes posible.' }}
    </p>

    <a href="#modelos" class="btn btn-primary btn-sm">{{ __('messages.see_models') }}</a>
</div>


    <!-- Mensajes -->
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

    <!-- Formulario -->
    <form action="{{ route('addbicismontaña') }}" method="POST" enctype="multipart/form-data">

        @csrf

        <!-- Datos personales -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
            <div>
                <label for="nombre" class="block text-gray-700 font-semibold mb-2">{{ __('messages.name') }}</label>
                <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" class="w-full px-4 py-2 border rounded-md" required>
            </div>

            <div>
                <label for="apellido" class="block text-gray-700 font-semibold mb-2">{{ __('messages.surname') }}</label>
                <input type="text" id="apellido" name="apellido" value="{{ old('apellido') }}" class="w-full px-4 py-2 border rounded-md" required>
            </div>

            <div>
                <label for="email" class="block text-gray-700 font-semibold mb-2">{{ __('messages.email') }}</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" class="w-full px-4 py-2 border rounded-md" required>
            </div>

            <div>
                <label for="telefono" class="block text-gray-700 font-semibold mb-2">{{ __('messages.phone') }}</label>
                <input type="text" name="telefono" id="telefono" value="{{ old('telefono') }}" class="w-full border px-4 py-2 rounded-md" placeholder="Ejemplo / Example: 612345678" required>
            </div>

            <div>
                <label for="dni" class="block text-gray-700 font-semibold mb-2">{{ __('messages.dni') }}</label>
                <input type="text" id="dni" name="dni" value="{{ old('dni') }}" class="w-full px-4 py-2 border rounded-md" required>
            </div>

            <div>
                <label for="direccion" class="block text-gray-700 font-semibold mb-2">{{ __('messages.address') }}</label>
                <input type="text" id="direccion" name="direccion" value="{{ old('direccion') }}" class="w-full px-4 py-2 border rounded-md" required>
            </div>
        </div>

        <!-- 📸 Subir varias imágenes del DNI -->
        <div class="mb-6">
            <label for="imagenes_dni" class="block text-gray-700 font-semibold mb-2">{{ __('messages.dni_images') }}</label>
            <input type="file" id="imagenes_dni" name="imagenes_dni[]" accept="image/*" multiple class="w-full border px-4 py-2 rounded-md bg-gray-50 cursor-pointer" required>
            <p class="text-sm text-gray-600 mt-1">{{ __('messages.dni_images_hint') }}</p>
        </div>

        <!-- Observaciones -->
        <div class="mb-4">
            <label for="observaciones" class="block text-gray-700 font-semibold mb-2">{{ __('messages.notes') }}</label>
            <textarea id="observaciones" name="observaciones" rows="4" class="w-full px-4 py-2 border rounded-md">{{ old('observaciones') }}</textarea>
        </div>

        <!-- Bicicletas -->
        <h2 class="text-lg font-bold mt-6 mb-2">{{ __('messages.bicycles_to_rent') }}</h2>

        <div id="bicicletas-container">
            <div class="bicicleta-item border p-4 mb-4 rounded-md bg-blue-100">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Honeypot -->
                    <div style="display: none;">
                        <input type="text" name="website" tabindex="-1" autocomplete="off">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1">{{ __('messages.size') }}</label>
                        <select name="bicicletas[0][talla]" required class="w-full border rounded-md">
                            <option value="">{{ __('messages.select_size') }}</option>
                            <option value="XSS">Niño / Child</option>
                            <option value="XS">XS (145–160 cm)</option>
                            <option value="S">S (160–170 cm)</option>
                            <option value="M">M (165–175 cm)</option>
                            <option value="L">L (175–185 cm)</option>
                            <option value="XL">XL (185–195 cm)</option>
                            <option value="XXL">XXL (195+ cm)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1">{{ __('messages.type') }}</label>
                        <select name="bicicletas[0][tipo]" required class="w-full border rounded-md">
                            <option value="">{{ __('messages.select_type') }}</option>
                            <option value="Mtb">Montaña / Mountain</option>
                            <option value="Carretera">Carretera / Road</option>
                            <option value="Electrica">Eléctrica / Electric</option>
                            <option value="Paseo">Paseo / City</option>
                            <option value="Niños">Niños / Kids</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1">{{ __('messages.quantity') }}</label>
                        <select name="bicicletas[0][cantidad]" required class="w-full border rounded-md">
                            <option value="">{{ __('messages.select_quantity') }}</option>
                            @for($i = 1; $i <= 6; $i++)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-gray-700 mb-1">{{ __('messages.start_date') }}</label>
                        <input type="date" name="bicicletas[0][fecha_inicio]" required class="w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1">{{ __('messages.end_date') }}</label>
                        <input type="date" name="bicicletas[0][fecha_fin]" required class="w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                </div>
            </div>
        </div>

        <button type="button" onclick="agregarBicicleta()" class="bg-blue-500 text-white px-4 py-2 rounded-md mt-4">
            {{ __('messages.add_bike') }}
        </button>

                <!-- 🧾 Condiciones -->
        <div class="mt-8 border-t pt-5">
            <button type="button" onclick="document.getElementById('condicionesTexto').classList.toggle('hidden')" class="w-full bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-md font-semibold flex justify-between items-center transition">
                <span>📜 {{ __('messages.toggle_terms') }}</span>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div id="condicionesTexto" class="hidden mt-3 p-3 border rounded-md bg-gray-50 text-sm text-gray-800 overflow-y-auto max-h-72 leading-relaxed">
                {!! __('messages.general_terms_text') !!}
            </div>

            <div class="mt-4 flex items-center justify-center gap-3">
                <input type="checkbox" id="acepta_condiciones" name="acepta_condiciones" required class="h-5 w-5 text-green-500 border-gray-300 rounded focus:ring-2 focus:ring-green-400">
                <label for="acepta_condiciones" class="text-gray-700 text-sm leading-tight">
                    {{ __('messages.accept_terms') }}
                </label>
            </div>
        </div>

        <!-- Botones -->
        <div class="flex justify-between mt-6">
            <a href="{{ route('usuarios_alquiler.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                {{ __('messages.cancel') }}
            </a>
            <button type="submit" class="px-6 py-2 bg-green-400 hover:bg-green-500 text-black rounded-md font-semibold">
                {{ __('messages.submit_booking') }}
            </button>
        </div>
    </form>
</div>

<div>
    <main class="flex-grow" id="modelos">
    <div class="container py-4">
        <div class="row g-3">

            <!-- MTB 29 -->
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white p-2">
                        <h6 class="mb-0">{{ __('messages.mtb_29') }}</h6>
                    </div>
                    <div class="card-body p-2">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr><th>{{ __('messages.model') }}</th><th>{{ __('messages.size') }}</th></tr>
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
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white p-2">
                        <h6 class="mb-0">{{ __('messages.mtb_26') }}</h6>
                    </div>
                    <div class="card-body p-2">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr><th>{{ __('messages.model') }}</th><th>{{ __('messages.size') }}</th></tr>
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
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white p-2">
                        <h6 class="mb-0">{{ __('messages.road') }}</h6>
                    </div>
                    <div class="card-body p-2">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr><th>{{ __('messages.model') }}</th><th>{{ __('messages.size') }}</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>Giant Propel</td><td>S</td></tr>
                                <tr><td>Scott Addict 20</td><td>L</td></tr>
                                <tr><td>BH Prisma Carbono</td><td>M</td></tr>
                                <tr><td>Pinarello Dogma</td><td>XS</td></tr>
                                <tr><td>Wilier</td><td>M</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Eléctricas -->
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white p-2">
                        <h6 class="mb-0">{{ __('messages.electric-doble') }}</h6>
                    </div>
                    <div class="card-body p-2">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr><th>{{ __('messages.model') }}</th><th>{{ __('messages.size') }}</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>SCOTT STRIKE 930</td><td>M</td></tr>
                                <tr><td>OLYMPIA EX900 TRAIL</td><td>M</td></tr>
                                <tr><td>OLYMPIA EX900 SPORT</td><td>M</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

                        <!-- Eléctricas -->
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white p-2">
                        <h6 class="mb-0">{{ __('messages.electric') }}</h6>
                    </div>
                    <div class="card-body p-2">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr><th>{{ __('messages.model') }}</th><th>{{ __('messages.size') }}</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>OLYMPIA BLAKE</td><td>S</td></tr>
                                <tr><td>GIANT FATHOM PRO</td><td>M</td></tr>
                                <tr><td>OLIMPIA MASTER 630</td><td>M</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Paseo -->
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white p-2">
                        <h6 class="mb-0">{{ __('messages.city') }}</h6>
                    </div>
                    <div class="card-body p-2">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('messages.model') }}</th>
                                    <th>{{ __('messages.size') }}</th>
                                    <th>{{ __('messages.price') }}</th>
                                </tr>
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
            {{ __('messages.availability_note') }}
        </p>
    </div>
    </main>

</div>

<script>
    let index = 1;
    function agregarBicicleta() {
        const container = document.getElementById('bicicletas-container');
        const nueva = container.firstElementChild.cloneNode(true);
        nueva.querySelectorAll('select, input').forEach(el => {
            const name = el.getAttribute('name');
            if (name) {
                const nuevoNombre = name.replace(/\d+/, index);
                el.setAttribute('name', nuevoNombre);
                el.value = '';
            }
        });
        container.appendChild(nueva);
        index++;
    }
</script>
@endsection
