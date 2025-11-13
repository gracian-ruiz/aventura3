@extends('layouts.app2')

@section('content')
<div class="container">
    <h1 class="text-2xl font-bold mb-4">Editar Alquiler</h1>

    {{-- Formulario para actualizar datos del alquiler --}}
    <form action="{{ route('alquileres.update', $alquiler) }}" method="POST" class="mb-10" enctype="multipart/form-data">
        @csrf
        @method('PUT')
    
        <!-- Estado -->
        <div class="mb-4">
            <label class="block font-medium text-gray-700 mb-1" for="estado">Estado del alquiler</label>
            <select name="estado" id="estado" class="w-full border-gray-300 rounded-md shadow-sm">
                <option value="reservado" {{ old('estado', $alquiler->estado) == 'reservado' ? 'selected' : '' }}>Reserva</option>
                <option value="activo" {{ old('estado', $alquiler->estado) == 'activo' ? 'selected' : '' }}>Alquilar</option>
            </select>
            @error('estado') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
    
        <!-- Total precio -->
        <div class="mb-4">
            <label class="block font-medium text-gray-700 mb-1" for="total_precio">Total (€)</label>
            <input type="number" step="0.01" name="total_precio" value="{{ old('total_precio', $alquiler->total_precio) }}" class="w-full border-gray-300 rounded-md shadow-sm">
            @error('total_precio') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
    
        <!-- Descuento -->
        <div class="mb-4">
            <label class="block font-medium text-gray-700 mb-1" for="descuento">Descuento (%)</label>
            <input type="number" step="0.01" name="descuento" value="{{ old('descuento', $alquiler->descuento) }}" class="w-full border-gray-300 rounded-md shadow-sm">
            @error('descuento') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
    
        <!-- Observaciones -->
        <div class="mb-4">
            <label class="block font-medium text-gray-700 mb-1" for="observaciones">Observaciones</label>
            <textarea name="observaciones" id="observaciones" rows="3" class="w-full border-gray-300 rounded-md shadow-sm">{{ old('observaciones', $alquiler->observaciones) }}</textarea>
            @error('observaciones') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
                <!-- 📸 Subida de imágenes del DNI -->
        <div class="mb-6">
            <label for="imagenes_dni" class="block text-gray-700 font-semibold mb-2">
                Imágenes del DNI / Documentos del cliente
            </label>
            <input type="file" id="imagenes_dni" name="imagenes_dni[]" accept="image/*" multiple
                class="w-full border px-4 py-2 rounded-md bg-gray-50 cursor-pointer"
                onchange="previewImages(event)">
            <p class="text-sm text-gray-600 mt-1">
                Puedes subir varias fotos (anverso y reverso). Solo se permiten imágenes JPG, PNG, JPEG.
            </p>
            <div id="preview-container" class="flex flex-wrap gap-3 mt-3"></div>
        </div>

        <div class="mb-4">
            <label class="block font-medium text-gray-700 mb-1" for="incidencia">Incidencia RESERVA WEB</label>
            <textarea name="incidencia" id="incidencia" rows="3" class="w-full border-gray-300 rounded-md shadow-sm">{{ old('incidencia', $alquiler->incidencia) }}</textarea>
            @error('incidencia') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Fallo de stock -->
        <div class="mb-4">
            <label class="block font-medium text-gray-700 mb-1" for="fallo">Problemas de stock</label>
            <select name="fallo" id="fallo" class="w-full border-gray-300 rounded-md shadow-sm">
                <option value="1" {{ old('fallo', $alquiler->fallo) == 1 ? 'selected' : '' }}>Tiene un problema</option>
                <option value="0" {{ old('fallo', $alquiler->fallo) == 0 ? 'selected' : '' }}>Problema solucionado</option>
            </select>
        </div>

    
        <!-- Botones -->
        <div class="mt-6">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded shadow">Actualizar Alquiler</button>
            <a href="{{ route('alquileres.index') }}" class="ml-4 text-blue-600 underline">Cancelar</a>
        </div>
    </form>
    

    {{-- Materiales asociados --}}
{{-- Materiales asociados --}}
<h2 class="text-xl font-semibold mb-4">Materiales del Alquiler</h2>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @forelse ($alquiler->materiales as $material)
        <div class="border p-4 rounded shadow relative">
            <h3 class="text-lg font-semibold mb-2">{{ $material->nombre }}</h3>

            <ul class="text-sm text-gray-700 mb-4">
                <li><strong>Descripción:</strong> {{ $material->descripcion ?? 'No disponible' }}</li>
                <li><strong>Precio:</strong> {{ number_format($material->pivot->precio_unitario, 2) }} €</li>
                <li><strong>Fecha inicio:</strong> {{ $material->pivot->fecha_inicio ?? 'No disponible' }}</li>
                <li><strong>Fecha fin:</strong> {{ $material->pivot->fecha_fin ?? 'No disponible' }}</li>
                <li><strong>Precio Reserva:</strong> {{ $material->pivot->reserva_precio ?? '0' }} eu</li>
            </ul>

            <form action="{{ route('alquileres.materiales.destroy', $material->pivot->id) }}" method="POST" onsubmit="return confirm('¿Seguro que quieres eliminar este material?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 text-white w-full py-2 rounded">
                    Eliminar Material
                </button>
            </form>
        </div>
    @empty
        <p class="col-span-2 text-center text-gray-500">No hay materiales asociados.</p>
    @endforelse
</div>


{{-- Formulario para añadir material al alquiler --}}
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
    <h1 class="text-2xl font-bold text-center mb-6">Crear Alquiler</h1>

    @if ($errors->any())
        <div class="mb-6">
            <ul class="list-disc list-inside text-red-500">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('alquileres.addMateriales', ['alquiler' => $alquiler->id]) }}" method="POST" id="form-alquiler">
        @csrf

        <!-- Usuario -->
        <div class="mb-4">
            <label for="usuario_id" class="block font-medium text-gray-700 mb-1">Usuario</label>
            <input type="text" disabled value="{{ $usuario_alquiler->nombre }}" class="w-full border-gray-300 rounded-md shadow-sm bg-gray-100">
            <input type="hidden" name="usuario_id" value="{{ $usuario_alquiler->id }}">
        </div>

        <!-- Fechas -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <div>
                <label for="fecha_inicio" class="block font-medium text-gray-700 mb-1">Fecha de inicio</label>
                <input type="date" name="fecha_inicio" value="{{ old('fecha_inicio') }}" class="w-full border-gray-300 rounded-md shadow-sm">
            </div>
            <div>
                <label for="fecha_fin" class="block font-medium text-gray-700 mb-1">Fecha de fin</label>
                <input type="date" name="fecha_fin" value="{{ old('fecha_fin') }}" class="w-full border-gray-300 rounded-md shadow-sm">
            </div>
        </div>

<!-- Tipos y tallas combinados -->
<div class="mb-4">
    <p class="text-gray-700 font-medium mb-2">Selecciona materiales (tipo y talla):</p>
  
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      
      <!-- Mtb -->
      <fieldset class="p-4 rounded bg-red-100">
        <legend class="font-semibold mb-2 text-red-700">Mtb</legend>
        <label><input type="checkbox" name="tipo_talla[]" value="mtb-XS" class="mr-2">XS</label><br>
        <label><input type="checkbox" name="tipo_talla[]" value="mtb-S" class="mr-2">S</label><br>
        <label><input type="checkbox" name="tipo_talla[]" value="mtb-M" class="mr-2">M</label><br>
        <label><input type="checkbox" name="tipo_talla[]" value="mtb-L" class="mr-2">L</label><br>
        <label><input type="checkbox" name="tipo_talla[]" value="mtb-XL" class="mr-2">XL</label><br>
        <label><input type="checkbox" name="tipo_talla[]" value="mtb-XXL" class="mr-2">XXL</label>
      </fieldset>
  
      <!-- Eléctrica -->
      <fieldset class="p-4 rounded bg-green-100">
        <legend class="font-semibold mb-2 text-green-700">Eléctrica</legend>
        <label><input type="checkbox" name="tipo_talla[]" value="electrica-xs" class="mr-2">XS</label><br>
        <label><input type="checkbox" name="tipo_talla[]" value="electrica-s" class="mr-2">S</label><br>
        <label><input type="checkbox" name="tipo_talla[]" value="electrica-m" class="mr-2">M</label><br>
        <label><input type="checkbox" name="tipo_talla[]" value="electrica-l" class="mr-2">L</label><br>
        <label><input type="checkbox" name="tipo_talla[]" value="electrica-xl" class="mr-2">XL</label><br>
        <label><input type="checkbox" name="tipo_talla[]" value="electrica-xxl" class="mr-2">XXL</label>
      </fieldset>
  
      <!-- Carretera -->
      <fieldset class="p-4 rounded bg-blue-100">
        <legend class="font-semibold mb-2 text-blue-700">Carretera</legend>
        <label><input type="checkbox" name="tipo_talla[]" value="carretera-xs" class="mr-2">XS</label><br>
        <label><input type="checkbox" name="tipo_talla[]" value="carretera-s" class="mr-2">S</label><br>
        <label><input type="checkbox" name="tipo_talla[]" value="carretera-m" class="mr-2">M</label><br>
        <label><input type="checkbox" name="tipo_talla[]" value="carretera-l" class="mr-2">L</label><br>
        <label><input type="checkbox" name="tipo_talla[]" value="carretera-xl" class="mr-2">XL</label><br>
        <label><input type="checkbox" name="tipo_talla[]" value="carretera-xxl" class="mr-2">XXL</label>
      </fieldset>
  
      <!-- Paseo -->
      <fieldset class="p-4 rounded bg-yellow-100">
        <legend class="font-semibold mb-2 text-yellow-700">Paseo</legend>
        <label><input type="checkbox" name="tipo_talla[]" value="paseo-xs" class="mr-2">XS</label><br>
        <label><input type="checkbox" name="tipo_talla[]" value="paseo-s" class="mr-2">S</label><br>
        <label><input type="checkbox" name="tipo_talla[]" value="paseo-m" class="mr-2">M</label><br>
        <label><input type="checkbox" name="tipo_talla[]" value="paseo-l" class="mr-2">L</label><br>
        <label><input type="checkbox" name="tipo_talla[]" value="paseo-xl" class="mr-2">XL</label><br>
        <label><input type="checkbox" name="tipo_talla[]" value="paseo-xxl" class="mr-2">XXL</label>
      </fieldset>
  
      <!-- Niños -->
      <fieldset class="p-4 rounded bg-purple-100">
        <legend class="font-semibold mb-2 text-purple-700">Niños</legend>
        <label><input type="checkbox" name="tipo_talla[]" value="ninos-xs" class="mr-2">XS</label><br>
        <label><input type="checkbox" name="tipo_talla[]" value="ninos-s" class="mr-2">S</label><br>
        <label><input type="checkbox" name="tipo_talla[]" value="ninos-m" class="mr-2">M</label><br>
        <label><input type="checkbox" name="tipo_talla[]" value="ninos-l" class="mr-2">L</label><br>
        <label><input type="checkbox" name="tipo_talla[]" value="ninos-xl" class="mr-2">XL</label><br>
        <label><input type="checkbox" name="tipo_talla[]" value="ninos-xxl" class="mr-2">XXL</label>
      </fieldset>
  
      <!-- Accesorios -->
      <fieldset class="p-4 rounded bg-gray-100">
        <legend class="font-semibold mb-2 text-gray-700">Accesorios</legend>
        <label><input type="checkbox" name="tipo_talla[]" value="casco-xs" class="mr-2">Casco - XS</label><br>
        <label><input type="checkbox" name="tipo_talla[]" value="casco-s" class="mr-2">Casco - S</label><br>
        <label><input type="checkbox" name="tipo_talla[]" value="casco-m" class="mr-2">Casco - M</label><br>
        <label><input type="checkbox" name="tipo_talla[]" value="casco-l" class="mr-2">Casco - L</label><br>
        <label><input type="checkbox" name="tipo_talla[]" value="bidones-m" class="mr-2">Bidones</label><br>
        <label><input type="checkbox" name="tipo_talla[]" value="reparacion-m" class="mr-2">Reparación</label><br>
        <label><input type="checkbox" name="tipo_talla[]" value="bombin-m" class="mr-2">Bombín</label><br>
        <label><input type="checkbox" name="tipo_talla[]" value="kit_reparacion-m" class="mr-2">Kit Reparación</label>
      </fieldset>
  
    </div>
  </div>
  


        <!-- Ver materiales disponibles -->
        <div class="mb-4">
            <button type="button" id="checkDisponibilidad" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Ver materiales disponibles
            </button>
        </div>

        <!-- Resultado AJAX -->
        <div id="resultadoDisponibilidad" class="mb-4"></div>

        <!-- Botones -->
        <div class="flex justify-between items-center">
            <a href="{{ route('alquileres.index') }}" class="text-blue-600 hover:underline">← Volver al listado</a>
            <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
                Guardar alquiler
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('checkDisponibilidad').addEventListener('click', function () {
        const fechaInicio = document.querySelector('[name="fecha_inicio"]').value;
        const fechaFin = document.querySelector('[name="fecha_fin"]').value;

        if (!fechaInicio || !fechaFin) {
            alert('Por favor, ingresa ambas fechas.');
            return;
        }

        const combinaciones = Array.from(document.querySelectorAll('input[name="tipo_talla[]"]:checked')).map(el => el.value);

        const params = new URLSearchParams();
        params.append('fecha_inicio', fechaInicio);
        params.append('fecha_fin', fechaFin);
        combinaciones.forEach(comb => params.append('tipo_talla[]', comb));
        
        fetch(`/public/alquileres/materiales-disponibles?${params.toString()}`)
        //fetch(`/alquileres/materiales-disponibles?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                console.log(data);
                const resultado = document.getElementById('resultadoDisponibilidad');
                if (data.length === 0) {
                    resultado.innerHTML = `<p class="text-red-600">No hay materiales disponibles en este periodo.</p>`;
                } else {
                    let html = '<p class="text-green-700 font-semibold mb-2">Selecciona los materiales disponibles:</p>';
                    html += '<div class="space-y-4">';
                    data.forEach(mat => {
                        html += `
                                <div class="space-y-4">
                                    <!-- Un material -->
                                    <div class="border-b border-gray-300 pb-4">
                                        <!-- Nombre del material arriba con checkbox -->
                                        <div class="flex items-center space-x-2 mb-2">
                                            <input type="hidden" name="materiales[${mat.id}][id]" value="${mat.id}">
                                            <input type="checkbox" id="material_${mat.id}" name="materiales[${mat.id}][selected]" value="on" class="h-5 w-5 text-green-600" style="margin-right: 5px;">
                                            <label for="material_${mat.id}" class="text-green-700 text-lg font-semibold">
                                                ${mat.nombre} ${mat.descripcion} ${mat.tipo} (${mat.talla})
                                            </label>
                                        </div>

                                        <!-- Campos de precio, descuento y reserva en una fila -->
                                        <div class="flex items-center space-x-6 flex-wrap">
                                            <div class="flex flex-col">
                                                <label class="text-lg font-semibold text-gray-800">Precio (€)</label>
                                                <input type="number" step="0.01" min="0" name="materiales[${mat.id}][precio_unitario]" value="${mat.precio_total}" class="border border-gray-300 rounded-md shadow-sm w-32">
                                            </div>

                                            <div class="flex flex-col">
                                                <label class="text-lg font-semibold text-gray-800">Descuento (€)</label>
                                                <input type="number" step="0.01" min="0" name="materiales[${mat.id}][descuento]" value="0" class="border border-gray-300 rounded-md shadow-sm w-32">
                                            </div>

                                            <div class="flex flex-col">
                                                <label class="text-lg font-semibold text-gray-800">Reserva (€)</label>
                                                <input type="number" step="0.01" min="0" name="materiales[${mat.id}][reserva_precio]" value="${mat.reserva_precio}" class="border border-gray-300 rounded-md shadow-sm w-32">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        `;
                    });
                    html += '</div>';
                    resultado.innerHTML = html;
                }
            })
            .catch(err => {
                console.error('Error en la solicitud:', err);
                alert('Error consultando disponibilidad.');
            });
    });
});
</script>

@endsection
