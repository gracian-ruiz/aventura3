@extends('layouts.app3')

@section('content')

<div class="w-full max-w-none mx-0 px-2 sm:px-4 lg:px-8 mt-0 bg-white shadow-md rounded-lg p-4">

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
    <form action="{{ route('addbicismontaña') }}" method="POST" enctype="multipart/form-data" onsubmit="return validarFormulario()">
        @csrf

        <!-- Datos personales -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">

            <div>
                <label class="block text-gray-700 font-semibold mb-2">
                    Nombre / Name
                </label>
                <input type="text" name="nombre" value="{{ old('nombre') }}" class="w-full px-4 py-2 border rounded-md" required>
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-2">
                    Apellidos / Surname
                </label>
                <input type="text" name="apellido" value="{{ old('apellido') }}" class="w-full px-4 py-2 border rounded-md" required>
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-2">
                    Correo electrónico / Email
                </label>
                <input type="email" name="email" value="{{ old('email') }}" class="w-full px-4 py-2 border rounded-md" required>
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-2">
                    Teléfono / Phone
                </label>
                <input type="text" name="telefono" value="{{ old('telefono') }}" class="w-full border px-4 py-2 rounded-md" placeholder="Ejemplo / Example: 612345678" required>
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-2">
                    DNI / ID Number
                </label>
                <input type="text" name="dni" value="{{ old('dni') }}" class="w-full px-4 py-2 border rounded-md" required>
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-2">
                    Dirección / Address
                </label>
                <input type="text" name="direccion" value="{{ old('direccion') }}" class="w-full px-4 py-2 border rounded-md" required>
            </div>
        </div>

        <!-- Imágenes DNI -->
        <div class="mb-6">
            <label class="block text-gray-700 font-semibold mb-2">
                Imágenes del DNI / ID Photos (you can upload several)
            </label>

            <input type="file" name="imagenes_dni[]" accept="image/*" multiple
                   class="w-full border px-4 py-2 rounded-md bg-gray-50 cursor-pointer" required>

            <p class="text-sm text-gray-600 mt-1">
                Puedes subir varias fotos (anverso y reverso).  
                You can upload multiple images (front and back).
            </p>
        </div>

        <!-- Observaciones -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold mb-2">
                Observaciones / Notes
            </label>
            <textarea name="observaciones" rows="4" class="w-full px-4 py-2 border rounded-md">{{ old('observaciones') }}</textarea>
        </div>

        <!-- Bicicletas -->
        <h2 class="text-lg font-bold mt-6 mb-2">Bicicletas a alquilar / Bicycles to Rent</h2>

        <div id="bicicletas-container">
            <div class="bicicleta-item border p-4 mb-4 rounded-md bg-blue-100">

                <!-- Header con número y botón eliminar -->
                <div class="flex justify-between items-center mb-3">
                    <span class="font-semibold text-gray-700 bicicleta-numero">🚲 Bicicleta / Bicycle #1</span>
                    <button type="button" onclick="eliminarBicicleta(this)" 
                            class="btn-eliminar hidden bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-md text-sm">
                        ❌ Eliminar / Remove
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                    <div style="display:none;">
                        <input type="text" name="website">
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-1">
                            Talla / Size
                        </label>
                        <select name="bicicletas[0][talla]" class="w-full border rounded-md">
                            <option value="">Selecciona / Select</option>
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
                        <label class="block text-gray-700 mb-1">
                            Tipo / Type
                        </label>
                        <select name="bicicletas[0][tipo]" class="w-full border rounded-md">
                            <option value="">{{ __('messages.select_type') }}</option>
                            <option value="mtb26">MTB 26</option>
                            <option value="mtb29">MTB 29</option>
                            <option value="mtb29doble">MTB 29 Doble</option>
                            <option value="electricapaseo">Eléctrica Paseo</option>
                            <option value="electricadoble">Eléctrica Doble</option>
                            <option value="electricarigida">Eléctrica Rígida</option>
                            <option value="carretera">Carretera / Road</option>
                            <option value="paseo">Paseo / City</option>
                            <option value="ninos">Niños / Kids</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-1">
                            Cantidad / Quantity
                        </label>
                        <select name="bicicletas[0][cantidad]" class="w-full border rounded-md">
                            <option value="">Selecciona / Select</option>
                            @for($i=1;$i<=6;$i++)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-gray-700 mb-1">
                            Fecha inicio / Start date
                        </label>
                        <input type="date" name="bicicletas[0][fecha_inicio]" class="w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1">
                            Fecha fin / End date
                        </label>
                        <input type="date" name="bicicletas[0][fecha_fin]" class="w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                </div>

            </div>
        </div>

        <button type="button" onclick="agregarBicicleta()"
                class="bg-blue-500 text-white px-4 py-2 rounded-md mt-4">
            Añadir otra bicicleta / Add another bicycle
        </button>

        <!-- Condiciones -->
        <div class="mt-8 border-t pt-5">
            <button type="button"
                onclick="document.getElementById('condicionesTexto').classList.toggle('hidden')"
                class="w-full bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-md font-semibold flex justify-between items-center transition">
                📜 Ver / Ocultar Condiciones Generales  
                View / Hide General Rental Terms
            </button>

            <div id="condicionesTexto" class="hidden mt-3 p-3 border rounded-md bg-gray-50 text-sm text-gray-800 overflow-y-auto max-h-72 leading-relaxed">

                <!-- 🇪🇸 Español -->
                <strong>CONDICIONES GENERALES DEL ALQUILER DE MATERIAL</strong><br><br>

                1.- Queda prohibido el alquiler de material a los menores de dieciocho (18) años de edad. La contratación del alquiler para los menores de dieciocho (18) años de edad requerirá del consentimiento de sus representantes legales.<br><br>

                2.- Es obligatorio contestar a todas las preguntas contenidas en el formulario, de forma que la prestación de los servicios ofertados y el suministro y alquiler de material queda supeditado al cumplimiento de esta obligación.<br><br>

                3.- El usuario del material alquilado queda obligado a la custodia, conservación y devolución del material que se le entregue, siendo directamente responsable de su deterioro y de los daños derivados de su pérdida y robo, así como de todos aquellos que excedan de su desgaste normal conforme a un uso idóneo y diligente según su destino.<br><br>

                4.- El usuario o, en su caso, su representante legal queda expresamente informado y conoce que la práctica del deporte constituye una práctica deportiva que entraña un riesgo cierto de sufrir caídas o lesiones, que pueden verse acrecentados entre otros factores por la edad y condiciones físicas de los usuarios, la adecuada utilización del material, las circunstancias climatológicas y el estado del terreno, por lo que la estación no asume responsabilidad alguna por las lesiones que pueda sufrir con ocasión de la práctica de este deporte y del uso del material alquilado.<br><br>

                <strong>Tratamiento de datos personales:</strong> Conforme a lo previsto en la Ley orgánica 15/1999, de 13 de diciembre, de protección de datos de carácter personal, y de los reglamentos dictados en su desarrollo, por la firma del presente documento se le informa y presta expresamente su consentimiento para que los datos de carácter personal propios o de su representado que de forma voluntaria se facilitan a Aventura Bike S.C. tales como nombre, apellidos, documento nacional de identidad/tarjeta de residente/NIF, fecha de nacimiento, nacionalidad, sexo, dirección postal y/o electrónica, teléfono, persona de contacto, datos bancarios, así como los que se recaben adicionalmente por tal causa pasen a formar parte, según corresponda, a nuestros ficheros.

                <br><br><hr class="my-4"><br>

                <!-- 🇬🇧 English -->
                <strong>GENERAL CONDITIONS OF RENTAL SHOP</strong><br><br>

                1. Equipment hire to persons under eighteen (18) years of age is prohibited. The rental agreement for minors under eighteen (18) years of age requires the consent of their legal representatives.<br><br>

                2. It is mandatory to answer all the questions on the form, so that the provision of the services offered and the supply and rental of equipment is subject to compliance with this obligation.<br><br>

                3. The user of the rented equipment is obliged to its custody, preservation, and return, being directly responsible for its deterioration and any damage resulting from its loss or theft, as well as for all those that exceed normal wear and tear due to appropriate and diligent use according to its purpose.<br><br>

                4. The user or, where appropriate, their legal representative is expressly informed and acknowledges that practicing this sport involves an inherent risk of falls or injuries, which may be increased by factors such as age, physical condition, proper use of the equipment, weather conditions, and terrain state. Therefore, Aventura Bike does not assume any liability for injuries that may occur during the practice of this sport and the use of rented equipment.<br><br>

                <strong>Processing of personal data:</strong> Pursuant to the Organic Law 15/1999 of December 13 on the protection of personal data, and the regulations issued for its development, by signing this document you are informed and expressly consent that the personal data provided voluntarily to Aventura Bike S.C., such as name, surname, ID card/residence permit/NIF, date of birth, nationality, gender, postal and/or email address, phone number, contact person, and bank details, as well as any additional data collected for this purpose, will form part of our files as appropriate.
            </div>

            <div class="mt-4 flex items-center justify-center gap-3">
                <input type="checkbox" name="acepta_condiciones" required
                    class="h-5 w-5 text-green-500 border-gray-300 rounded focus:ring-2 focus:ring-green-400">
                <label class="text-gray-700 text-sm leading-tight">
                    Acepto las condiciones / I accept the terms
                </label>
            </div>
        </div>


        <!-- Botones -->
        <div class="flex justify-between mt-6">
            <a href="{{ route('usuarios_alquiler.index') }}"
               class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                Cancelar / Cancel
            </a>

            <button type="submit"
                    class="px-6 py-2 bg-green-400 hover:bg-green-500 text-black rounded-md font-semibold">
                Enviar Reserva / Submit Booking
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
                        <h6 class="mb-0">MTB 29” – 25€/día / €25 per day</h6>
                    </div>
                    <div class="card-body p-2">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Modelo / Model</th>
                                    <th>Talla / Size</th>
                                </tr>
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
                        <h6 class="mb-0">MTB 26” – 15€/día / €15 per day</h6>
                    </div>
                    <div class="card-body p-2">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Modelo / Model</th>
                                    <th>Talla / Size</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>Scott spark rc</td><td>M</td></tr>
                                <tr><td>Mondraker Factor LSR</td><td>M, L</td></tr>
                                <tr><td>Diamond Back Titanio</td><td>S</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Carretera / Road -->
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white p-2">
                        <h6 class="mb-0">Carretera – 25€/día / Road – €25 per day</h6>
                    </div>
                    <div class="card-body p-2">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Modelo / Model</th>
                                    <th>Talla / Size</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>Giant Propel</td><td>S</td></tr>
                                <tr><td>BH Prisma Carbono</td><td>M</td></tr>
                                <tr><td>Pinarello Dogma</td><td>XS</td></tr>
                                <tr><td>Wilier</td><td>M</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- E-bikes dobles -->
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white p-2">
                        <h6 class="mb-0">Eléctricas Doble Suspensión – 65€/día / Electric Full Suspension – €65 per day</h6>
                    </div>
                    <div class="card-body p-2">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Modelo / Model</th>
                                    <th>Talla / Size</th>
                                </tr>
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

            <!-- E-bikes rígidas -->
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white p-2">
                        <h6 class="mb-0">Eléctricas – 45€/día / Electric – €45 per day</h6>
                    </div>
                    <div class="card-body p-2">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Modelo / Model</th>
                                    <th>Talla / Size</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>OLYMPIA BLAKE</td><td>S</td></tr>
                                <tr><td>OLIMPIA MASTER 630</td><td>M</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Paseo / City -->
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white p-2">
                        <h6 class="mb-0">Paseo / City</h6>
                    </div>
                    <div class="card-body p-2">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Modelo / Model</th>
                                    <th>Talla / Size</th>
                                    <th>Precio / Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>Sonora Domingos</td><td>S, M, L</td><td>18€</td></tr>
                                <tr><td>Bicicleta de niño / Kids bike</td><td>-</td><td>18€</td></tr>
                                <tr><td>Paseo eléctrica / Electric city bike</td><td>-</td><td>35€</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        <p class="text-muted mt-3 small fst-italic">
            Las bicicletas se entregan según disponibilidad.  
            Bicycles will be provided depending on availability.
        </p>
    </div>
    </main>
</div>


<script>
    let index = 1;

    function agregarBicicleta() {
        const container = document.getElementById('bicicletas-container');
        const nueva = container.firstElementChild.cloneNode(true);
        
        // Limpiar valores y actualizar nombres
        nueva.querySelectorAll('select, input').forEach(el => {
            const name = el.getAttribute('name');
            if (name) {
                const nuevoNombre = name.replace(/\d+/, index);
                el.setAttribute('name', nuevoNombre);
                el.value = '';
            }
        });
        
        // Actualizar número de bicicleta
        const numeroSpan = nueva.querySelector('.bicicleta-numero');
        if (numeroSpan) {
            numeroSpan.textContent = `🚲 Bicicleta / Bicycle #${index + 1}`;
        }
        
        // Mostrar botón eliminar en las nuevas bicicletas
        const btnEliminar = nueva.querySelector('.btn-eliminar');
        if (btnEliminar) {
            btnEliminar.classList.remove('hidden');
        }
        
        container.appendChild(nueva);
        index++;
    }

    function eliminarBicicleta(btn) {
        const container = document.getElementById('bicicletas-container');
        const items = container.querySelectorAll('.bicicleta-item');
        
        if (items.length <= 1) {
            alert('No puedes eliminar la única bicicleta.\nYou cannot remove the only bicycle.');
            return;
        }
        
        if (confirm('¿Eliminar esta bicicleta?\nRemove this bicycle?')) {
            const item = btn.closest('.bicicleta-item');
            item.remove();
            renumerarBicicletas();
        }
    }

    function renumerarBicicletas() {
        const container = document.getElementById('bicicletas-container');
        const items = container.querySelectorAll('.bicicleta-item');
        
        items.forEach((item, i) => {
            // Actualizar número visible
            const numeroSpan = item.querySelector('.bicicleta-numero');
            if (numeroSpan) {
                numeroSpan.textContent = `🚲 Bicicleta / Bicycle #${i + 1}`;
            }
            
            // Actualizar índices en nombres de campos
            item.querySelectorAll('select, input').forEach(el => {
                const name = el.getAttribute('name');
                if (name) {
                    el.setAttribute('name', name.replace(/\[\d+\]/, `[${i}]`));
                }
            });
            
            // Ocultar botón eliminar solo en la primera bicicleta
            const btnEliminar = item.querySelector('.btn-eliminar');
            if (btnEliminar) {
                if (i === 0) {
                    btnEliminar.classList.add('hidden');
                } else {
                    btnEliminar.classList.remove('hidden');
                }
            }
        });
        
        index = items.length;
    }

    function validarFormulario() {
        const container = document.getElementById('bicicletas-container');
        const items = container.querySelectorAll('.bicicleta-item');
        let valido = true;
        let errores = [];

        items.forEach((item, i) => {
            const numBici = i + 1;
            const talla = item.querySelector('select[name*="[talla]"]');
            const tipo = item.querySelector('select[name*="[tipo]"]');
            const cantidad = item.querySelector('select[name*="[cantidad]"]');
            const fechaInicio = item.querySelector('input[name*="[fecha_inicio]"]');
            const fechaFin = item.querySelector('input[name*="[fecha_fin]"]');

            if (!talla || !talla.value) {
                errores.push({ bici: numBici, msg: 'Selecciona la talla / Select the size' });
                valido = false;
            }
            if (!tipo || !tipo.value) {
                errores.push({ bici: numBici, msg: 'Selecciona el tipo / Select the type' });
                valido = false;
            }
            if (!cantidad || !cantidad.value) {
                errores.push({ bici: numBici, msg: 'Selecciona la cantidad / Select the quantity' });
                valido = false;
            }
            if (!fechaInicio || !fechaInicio.value) {
                errores.push({ bici: numBici, msg: 'Selecciona fecha de inicio / Select start date' });
                valido = false;
            }
            if (!fechaFin || !fechaFin.value) {
                errores.push({ bici: numBici, msg: 'Selecciona fecha fin / Select end date' });
                valido = false;
            }
            if (fechaInicio && fechaFin && fechaInicio.value && fechaFin.value) {
                if (new Date(fechaFin.value) < new Date(fechaInicio.value)) {
                    errores.push({ bici: numBici, msg: 'La fecha fin debe ser posterior a la de inicio / End date must be after start date' });
                    valido = false;
                }
            }
        });

        if (!valido) {
            // Obtener las bicicletas con errores (sin duplicados)
            const bicisConErrores = [...new Set(errores.map(e => e.bici))];
            const listaBicis = bicisConErrores.map(n => `Bicicleta #${n} / Bicycle #${n}`).join('\n');
            alert(`⚠️ Completa todos los campos de:\n${listaBicis}\n\nPlease fill in all fields for the bicycles listed above.`);
        }

        return valido;
    }
</script>
<script>
function enviarAltura() {
    const height = document.body.scrollHeight;
    parent.postMessage({ height: height }, "*");
}

// Enviar cuando la página termine de cargar
window.addEventListener("load", function () {
    enviarAltura();
    setTimeout(enviarAltura, 300);
    setTimeout(enviarAltura, 800);
    setTimeout(enviarAltura, 1500);
});

// Enviar cuando cambie el tamaño (móvil girado, zoom, etc.)
window.addEventListener("resize", function () {
    setTimeout(enviarAltura, 200);
});
</script>


@endsection
