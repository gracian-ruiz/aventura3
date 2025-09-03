<?php

namespace App\Http\Controllers\Alquiler;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Alquiler;
use App\Models\Material;
use App\Models\AlquilerMaterial;
use App\Models\UsuarioAlquiler;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class AlquilerController extends Controller
{


    public function index(Request $request)
    {
        Alquiler::where('notificacion', true)->update(['notificacion' => false]);
        
        $query = Alquiler::with('usuario')
            ->whereIn('estado', ['Activo', 'Reservado']);
    
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->whereHas('usuario', function ($q) use ($searchTerm) {
                $q->where('nombre', 'like', '%' . $searchTerm . '%');
            });
        }
    
        // Ordenar primero por fallo (1 primero), luego por estado (Activo primero), luego por fecha
        $query->orderByDesc('fallo')
            ->orderByRaw("
                CASE 
                    WHEN estado = 'Activo' THEN 0
                    WHEN estado = 'Reservado' THEN 1
                    ELSE 2
                END
            ")
            ->orderBy('fecha_inicio', 'asc');
    
        $alquileres = $query->paginate(10)->withQueryString();
    
        return view('alquiler.alquileres.index', compact('alquileres'));
    }
    
        
    public function finalizado(Request $request)
    {
        $query = Alquiler::with('usuario')->where('estado', 'finalizado');
    
        if ($request->has('search')) {
            $query->whereHas('usuario', function ($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->search . '%');
            });
        }
    
        $alquileres = $query->latest()->paginate(10);
        $alquileres->appends(request()->query()); // ✅ mantiene los filtros en la paginación
        
        return view('alquiler.alquileres.finalizado', compact('alquileres'));
        
    }
    



    public function create(UsuarioAlquiler $usuario_alquiler)
    {
        $materiales = Material::where('stock_disponible', '>', 0)
            ->whereIn('estado', ['disponible', 'reservado'])
            ->orderBy('nombre')
            ->get();

        return view('alquiler.alquileres.create', compact('usuario_alquiler', 'materiales'));
    }

    public function store(Request $request, UsuarioAlquiler $usuario_alquiler)
    { 
        $request->validate([
            'usuario_id' => 'required|exists:usuarios_alquiler,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'materiales' => 'required|array|min:1',
        ]);

        $materialesSeleccionados = array_filter($request->input('materiales'), function ($material) {
            return isset($material['selected']) && $material['selected'] === 'on';
        });

        if (empty($materialesSeleccionados)) {
            return back()->withErrors(['materiales' => 'Debes seleccionar al menos un material.'])->withInput();
        }

        foreach ($materialesSeleccionados as $index => $material) {
            $request->validate([
                "materiales.$index.precio_unitario" => 'required|numeric|min:0',
                "materiales.$index.descuento" => 'required|numeric|min:0',
            ]);
        }

        // --------- Cálculo del total_precio ----------
        $totalPrecio = 0;
        $descuentoTotal = 0;
        $reservaTotal = 0;
        foreach ($materialesSeleccionados as $material) {
            $precioUnitario = $material['precio_unitario'];
            $descuento = $material['descuento'];

            $subtotal = $precioUnitario - $descuento;
            $totalPrecio += $subtotal;
            $descuentoTotal += $descuento;
            $reservaTotal += $material['reserva_precio'];
        }
        // ---------------------------------------------

        // Crear el alquiler
        $alquiler = Alquiler::create([
            'usuario_id' => $request->input('usuario_id'),
            'fecha_inicio' => $request->input('fecha_inicio'),
            'fecha_fin' => $request->input('fecha_fin'),
            'total_precio' => $totalPrecio,
            'reserva_precio' => $reservaTotal,
            'descuento' => $descuentoTotal,
            'observaciones' => $request->input('observaciones'),
            'estado' => $request->input('estado'),
        ]);

        // Asociar materiales seleccionados al alquiler
        foreach ($materialesSeleccionados as $material) {
            $alquiler->materiales()->attach(
                $material['id'],
                [
                    'fecha_inicio' => $request->input('fecha_inicio'),
                    'fecha_fin' => $request->input('fecha_fin'),
                    'precio_unitario' => $material['precio_unitario'],
                    'descuento' => $material['descuento'],
                    'reserva_precio' => $material['reserva_precio'],
                    'subtotal' => ($material['precio_unitario'] - $material['descuento']) ,
                ]
            );
        }

        return redirect()->route('alquileres.index')->with('success', 'Alquiler creado exitosamente.');
    }




    public function bicicletasDisponibles(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        $tiposMateriales = $request->input('tipos_materiales', []);
        $tallasSeleccionadas = $request->input('tallas', []);
        $combinaciones = $request->input('tipo_talla', []); // ['mtb-m', 'mtb-xl', ...]
    
        // Validación rápida de fechas
        if (!$fechaInicio || !$fechaFin) {
            return response()->json(['error' => 'Fechas no válidas'], 422);
        }
    
        // Calcular número de días de alquiler
        $dias = Carbon::parse($fechaInicio)->diffInDays(Carbon::parse($fechaFin)) + 1;

    
        // Buscar materiales ocupados
        $materialesOcupados = DB::table('alquiler_material')
            ->join('alquileres', 'alquiler_material.alquiler_id', '=', 'alquileres.id')
            ->where(function ($query) use ($fechaInicio, $fechaFin) {
                $query->whereBetween('alquiler_material.fecha_inicio', [$fechaInicio, $fechaFin])
                      ->orWhereBetween('alquiler_material.fecha_fin', [$fechaInicio, $fechaFin])
                      ->orWhere(function ($query2) use ($fechaInicio, $fechaFin) {
                          $query2->where('alquiler_material.fecha_inicio', '<=', $fechaInicio)
                                 ->where('alquiler_material.fecha_fin', '>=', $fechaFin);
                      });
            })
            ->where('alquileres.estado', '!=', 'finalizado')
            ->pluck('alquiler_material.material_id');
    
        $materiales = collect();
    
        if (!empty($combinaciones)) {
            foreach ($combinaciones as $comb) {
                [$tipo, $talla] = explode('-', $comb);
    
                $query = Material::query()
                    ->where('tipo', $tipo)
                    ->where('talla', $talla)
                    ->whereNotIn('id', $materialesOcupados);
    
                $materiales = $materiales->merge($query->get());
            }
        } else {
            $materiales = Material::whereNotIn('id', $materialesOcupados);
    
            if (!empty($tiposMateriales)) {
                $materiales = $materiales->whereIn('tipo', $tiposMateriales);
            }
    
            if (!empty($tallasSeleccionadas)) {
                $materiales = $materiales->whereIn('talla', $tallasSeleccionadas);
            }
    
            $materiales = $materiales->get();
        }
    
        // Añadir precio total al resultado
        $materialesConPrecio = $materiales->map(function ($material) use ($dias) {
            $material->precio_total = $material->precio_dia * $dias;
            $material->reserva_precio = $material->reserva_precio * $dias;
            return $material;
        });
    
        return response()->json($materialesConPrecio->unique('id')->values());
    }
    



    public function edit(Alquiler $alquiler)
    {
        // Cargar materiales ya asignados al alquiler
        $alquiler->load('materiales');
        $usuario_alquiler = $alquiler->usuario; // Asumiendo que tienes relación alquiler -> usuario

        // Obtener todos los materiales disponibles
        $materialesDisponibles = Material::all(); // Puedes modificar esto si tienes algún filtro específico

        // Pasar los datos a la vista
        return view('alquiler.alquileres.edit', compact('alquiler', 'materialesDisponibles', 'usuario_alquiler'));
    }



    public function update(Request $request, Alquiler $alquiler)
    {
        $request->validate([
            'estado' => 'required|in:reservado,activo',
            'total_precio' => 'nullable|numeric|min:0',
            'descuento' => 'nullable|numeric|min:0|max:100',
            'observaciones' => 'nullable|string|max:1000',
            'incidencia' => 'nullable|string|max:1000',
            'fallo' => 'nullable|boolean',
        ]);
    
        $alquiler->update([
            'estado' => $request->estado,
            'total_precio' => $request->total_precio,
            'descuento' => $request->descuento,
            'observaciones' => $request->observaciones,
            'incidencia' => $request->incidencia,
            'fallo' => $request->fallo,
        ]);
    
        return redirect()->route('alquileres.index')->with('success', 'Alquiler actualizado correctamente.');
    }
    

    public function destroy($id)
    {
        $alquiler = Alquiler::findOrFail($id);
    
        // Eliminar materiales asociados al alquiler
        DB::table('alquiler_material')->where('alquiler_id', $alquiler->id)->delete();
    
        // Luego eliminamos el alquiler
        $alquiler->delete();
    
        return redirect()->route('alquileres.index')->with('success', 'Alquiler y materiales eliminados correctamente.');
    }
    
    

    public function eliminarMaterial($pivotId)
    {
        $pivot = DB::table('alquiler_material')->where('id', $pivotId)->first();
    
        if (!$pivot) {
            return back()->with('error', 'Material no encontrado.');
        }
    
        // Obtener el alquiler y actualizar precios
        $alquiler = Alquiler::findOrFail($pivot->alquiler_id);
    
        $alquiler->total_precio -= $pivot->subtotal;
        $alquiler->descuento -= $pivot->descuento;
        $alquiler->reserva_precio -= $pivot->reserva_precio;
    
        $alquiler->total_precio = max(0, $alquiler->total_precio);
        $alquiler->descuento = max(0, $alquiler->descuento);
    
        $alquiler->save();
    
        // Eliminar el registro de la tabla intermedia
        DB::table('alquiler_material')->where('id', $pivotId)->delete();
    
        return redirect()
            ->route('alquileres.edit', $alquiler->id)
            ->with('success', 'Material eliminado correctamente.');
    }
    



    public function addMateriales(Request $request, Alquiler $alquiler)
    {
        $request->validate([
            'materiales' => 'required|array|min:1',
        ]);

        // Filtrar materiales seleccionados
        $materialesSeleccionados = array_filter($request->input('materiales'), function ($material) {
            return isset($material['selected']) && $material['selected'] === 'on';
        });

        if (empty($materialesSeleccionados)) {
            return back()->withErrors(['materiales' => 'Debes seleccionar al menos un material.'])->withInput();
        }

        // Inicializar las variables de total solo para el material nuevo
        $totalPrecioNuevo = 0;
        $totalDescuentoNuevo = 0;
        $totalReservaNuevo = 0;

        // Validar y asociar los materiales seleccionados al alquiler
        foreach ($materialesSeleccionados as $index => $material) {
            $request->validate([
                "materiales.$index.precio_unitario" => 'required|numeric|min:0',
                "materiales.$index.descuento" => 'required|numeric|min:0',
                "materiales.$index.reserva_precio" => 'required|numeric|min:0',
            ]);

            // Calcular subtotal por material
            $precioUnitario = $material['precio_unitario'];
            $descuento = $material['descuento'];
            $reserva = $material['reserva_precio'];

            $subtotal = ($precioUnitario) - $descuento;

            // Actualizar los totales solo para los materiales nuevos
            $totalPrecioNuevo += $subtotal;
            $totalDescuentoNuevo += $descuento;
            $totalReservaNuevo += $reserva;

            // Asociar el material al alquiler
            $alquiler->materiales()->attach(
                $material['id'],
                [
                    'precio_unitario' => $precioUnitario,
                    'descuento' => $descuento,
                    'subtotal' => $subtotal,
                    'reserva_precio' => $reserva,
                    'fecha_inicio' => $request->input('fecha_inicio'),
                    'fecha_fin' => $request->input('fecha_fin'),
                ]
            );
        }

        // Actualizar el alquiler solo con los totales nuevos
        $alquiler->total_precio += $totalPrecioNuevo; // Se suma al total existente
        $alquiler->descuento += $totalDescuentoNuevo; // Se suma al descuento existente
        $alquiler->reserva_precio += $totalReservaNuevo;

        // Guardar los cambios en el alquiler
        $alquiler->save();

        return redirect()->route('alquileres.edit', $alquiler->id)->with('success', 'Materiales añadidos correctamente.');
    }

    public function show($id)
    {
        $alquiler = Alquiler::with('materiales')->findOrFail($id);

        return view('alquiler.alquileres.show', compact('alquiler'));
    }

    public function devolverMaterial($pivotId)
    {
        $pivot = DB::table('alquiler_material')->where('id', $pivotId)->first();
    
        if (!$pivot || $pivot->estado === 'finalizado') {
            return back()->with('warning', 'Este material ya ha sido devuelto o no existe.');
        }
    
        // Obtener el material correspondiente
        $material = Material::find($pivot->material_id);
    
        if ($material) {
            // Sumar el subtotal a la amortización
            $material->amortizacion += $pivot->subtotal;
            $material->save();
        }
    
        // Actualizar el estado a finalizado
        DB::table('alquiler_material')
            ->where('id', $pivotId)
            ->update(['estado' => 'finalizado']);
    
        return back()->with('success', 'Material marcado como devuelto y amortización actualizada.');
    }
    
    



    public function finalizar(Request $request, $id)
    {
        $alquiler = Alquiler::with('materiales')->findOrFail($id);

        if ($alquiler->estado === 'reservado') {
            // Cambiar solo el estado del alquiler a "activo"
            $alquiler->estado = 'activo';
            $alquiler->save();

            return redirect()->route('alquileres.show', $alquiler->id)
                ->with('success', 'La reserva ha sido activada correctamente.');
        }

        if ($alquiler->estado === 'activo') {
            // Cambiar el estado del alquiler a "finalizado"
            $alquiler->estado = 'finalizado';
            $alquiler->save();

            // Finalizar los materiales vinculados al alquiler
            foreach ($alquiler->materiales as $material) {
                $pivot = $material->pivot;
        
                if ($pivot->estado !== 'finalizado') {
                    // Sumar el subtotal del alquiler al campo amortización del material
                    $material->amortizacion += $pivot->subtotal;
                    $material->save();
        
                    // Cambiar el estado del material en el pivot a finalizado
                    $pivot->estado = 'finalizado';
                    $pivot->save();
                }
            }

            return redirect()->route('alquileres.show', $alquiler->id)
                ->with('success', 'El alquiler y sus materiales han sido finalizados.');
        }

        return redirect()->route('alquileres.show', $alquiler->id)
            ->with('warning', 'No se pudo actualizar el estado del alquiler.');
    }

    // En AventuraBikeController
public function calendarioAlquiler()
{
    //return view('pruebas'); // o simplemente 'index' si la vista está en resources/views/

$resultados = DB::table('alquiler_material')
        ->join('alquileres', 'alquileres.id', '=', 'alquiler_material.alquiler_id')
        ->join('usuarios_alquiler', 'usuarios_alquiler.id', '=', 'alquileres.usuario_id')
        ->join('materials', 'materials.id', '=', 'alquiler_material.material_id')
        ->where('alquiler_material.estado', 'activo')
        ->select(
            'alquiler_material.alquiler_id',
            'usuarios_alquiler.nombre as usuario',
            'materials.nombre as material',
            'alquiler_material.fecha_inicio',
            'alquiler_material.fecha_fin',
            'alquileres.estado',
            'alquileres.fallo',
            'alquileres.web'
        )
        ->get();

    $eventos = $resultados->map(function ($item) {
        // Asignar color según condiciones
        if ($item->fallo === 1) {
            $color = '#fcd34d'; // amarillo (bg-yellow-300)
        } elseif ($item->web === 1 && $item->estado === 'reservado') {
            $color = '#93c5fd'; // azul (bg-blue-300)
        } elseif ($item->estado === 'reservado') {
            $color = '#fca5a5'; // rojo claro (bg-red-300)
        } elseif ($item->estado === 'activo') {
            $color = '#4ade80'; // verde (bg-green-400)
        } elseif ($item->estado === 'finalizado') {
            $color = '#e5e7eb'; // gris claro (bg-gray-100)
        } else {
            $color = '#ffffff'; // blanco
        }

        return [
            'title' => $item->usuario . ' - ' . $item->material,
            'start' => $item->fecha_inicio,
            'end' => $item->fecha_fin
                ? \Carbon\Carbon::parse($item->fecha_fin)->addDay()->toDateString()
                : null,
            'url' => url('/alquileres/' . $item->alquiler_id),
            'color' => $color,
        ];
    });

    return view('alquiler.alquileres.calendario', ['eventos' => $eventos]);
}


}
