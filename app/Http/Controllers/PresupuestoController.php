<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Presupuesto;
use App\Models\User;
use App\Models\Component;
use App\Models\PresupuestoItem;
use App\Models\Bike;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Models\Cita;


class PresupuestoController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('presupuestos')
            ->leftJoin('bikes', 'presupuestos.bike_id', '=', 'bikes.id')
            ->leftJoin('users', 'bikes.user_id', '=', 'users.id')
            ->select('presupuestos.*', 'bikes.nombre as bike_nombre', 'users.name as user_nombre')
            ->where('presupuestos.estado', 'pendiente'); // Filtrar solo los pendientes
    
        // Aplicar filtro de búsqueda
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%$search%")
                  ->orWhere('bikes.nombre', 'like', "%$search%");
            });
        }
    
        $presupuestos = $query->paginate(10)->appends(['search' => $request->search]); // Mantiene el término en la paginación
    
        return view('presupuestos.index', compact('presupuestos'));
    }
    

    public function create($userId)
    {
        $user = User::findOrFail($userId); // Obtiene el usuario
        $bikes = Bike::where('user_id', $userId)->get(); // Obtiene sus bicicletas
        $components = Component::all(); // Obtiene todos los componentes disponibles


        return view('presupuestos.create', compact('user', 'bikes', 'components'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bike_id' => 'required|exists:bikes,id',
            'componentes' => 'required|array',
            'horas_trabajo' => 'required|array',
            'precios' => 'required|array',
            'textos' => 'required|array',
            'horas_trabajo.*' => 'required|integer|min:0',
            'precios.*' => 'required|numeric|min:0',
        ]);

        $bikeId = $request->bike_id;
        $componentes = $request->componentes;
        $horasTrabajo = $request->horas_trabajo;
        $precios = $request->precios;
        $textos = $request->textos;

        // Validar que los arrays sean del mismo tamaño
        if (count($componentes) !== count($horasTrabajo) || count($componentes) !== count($precios) || count($componentes) !== count($textos)) {
            return redirect()->back()->withErrors(['error' => 'Los datos de los componentes no coinciden.']);
        }

        // Validar que haya al menos un componente
        if (empty($componentes)) {
            return redirect()->back()->withErrors(['error' => 'Debe agregar al menos un componente.']);
        }

        // Crear el presupuesto sin totales aún
        $presupuesto = Presupuesto::create([
            'bike_id' => $bikeId,
            'horas_total' => 0,
            'precio_total' => 0,
            'user_id' => auth()->id(),
        ]);

        $totalHoras = 0;
        $totalPrecio = 0;

        // Insertar cada componente en `presupuesto_items` y calcular totales
        foreach ($componentes as $index => $componenteId) {

            $horas = (int) $horasTrabajo[$index];
            $precio = (int) $precios[$index];
            $texto = $textos[$index] ?? '';

            PresupuestoItem::create([
                'presupuesto_id' => $presupuesto->id,
                'componente_id' => $componenteId,
                'horas_trabajo' => $horas,
                'total_precio' => $precio, // Asegúrate que en la migración se llame igual
                'texto' => $texto,
            ]);

            // Acumular totales
            $totalHoras += $horas;
            $totalPrecio += $precio;
        }
        //dd($totalPrecio);
        // Actualizar los totales en la tabla presupuestos
        $presupuesto->update([
            'horas_total' => $totalHoras,
            'precio_total' => round($totalPrecio, 2),
        ]);

        return redirect()->route('presupuestos.factura', ['id' => $presupuesto->id])
            ->with('success', 'Presupuesto guardado correctamente.');
    }

    public function factura($id)
    {
        // Obtener el presupuesto con la bicicleta y el usuario
        $presupuesto = DB::table('presupuestos')
            ->join('bikes', 'presupuestos.bike_id', '=', 'bikes.id')
            ->join('users', 'bikes.user_id', '=', 'users.id')
            ->where('presupuestos.id', $id)
            ->select(
                'presupuestos.*',
                'bikes.id as bicicleta_id',
                'bikes.nombre as bicicleta_nombre',
                'users.id as usuario_id',
                'users.name as usuario_nombre',
                'users.email as usuario_email'
            )
            ->first();

        // Si no se encuentra el presupuesto, retornar error 404
        if (!$presupuesto) {
            abort(404, 'Presupuesto no encontrado');
        }

        // Obtener los ítems del presupuesto con los componentes
        $items = DB::table('presupuesto_items')
            ->join('components', 'presupuesto_items.componente_id', '=', 'components.id')
            ->where('presupuesto_items.presupuesto_id', $id)
            ->select([
                'presupuesto_items.*',
                'components.nombre as componente_nombre'
            ])
            ->get();

        return view('presupuestos.factura', compact('presupuesto', 'items'));
    }

    public function descargarPDF($presupuestoId)
    {
        $presupuesto = DB::table('presupuestos')
            ->join('bikes', 'presupuestos.bike_id', '=', 'bikes.id')
            ->join('users', 'bikes.user_id', '=', 'users.id')
            ->where('presupuestos.id', $presupuestoId)
            ->select('presupuestos.*', 'bikes.nombre as bicicleta_nombre', 'users.name as usuario_nombre')
            ->first();

        $items = DB::table('presupuesto_items')
            ->join('components', 'presupuesto_items.componente_id', '=', 'components.id')
            ->where('presupuesto_items.presupuesto_id', $presupuestoId)
            ->select('presupuesto_items.*', 'components.nombre as componente_nombre')
            ->get();
    
        // Si no se encuentra el presupuesto, retornar error 404
        if (!$presupuesto) {
            abort(404, 'Presupuesto no encontrado');
        }
    

    
        // Generar el nombre del archivo PDF
        $nombreArchivo = "Factura_{$presupuesto->usuario_nombre}_{$presupuesto->bicicleta_nombre}_" .
            date('Y-m-d', strtotime($presupuesto->created_at)) . ".pdf";
    
        // Generar PDF
        $pdf = Pdf::loadView('pdf.presupuesto', compact('presupuesto', 'items'));
    
        return $pdf->download($nombreArchivo);
    }


    public function edit($id)
    {
        // Obtener el presupuesto con los datos de la bicicleta y el usuario
        $presupuesto = DB::table('presupuestos')
            ->leftJoin('bikes', 'presupuestos.bike_id', '=', 'bikes.id')
            ->leftJoin('users', 'bikes.user_id', '=', 'users.id')
            ->select('presupuestos.*', 'bikes.id as bike_id', 'bikes.nombre as bike_nombre', 'users.name as user_nombre')
            ->where('presupuestos.id', $id)
            ->first();

        if (!$presupuesto) {
            abort(404);
        }

        // Obtener todos los ítems asociados a este presupuesto
        $presupuesto_items = DB::table('presupuesto_items')
        ->join('components', 'presupuesto_items.componente_id', '=', 'components.id')
        ->where('presupuesto_items.presupuesto_id', $id)
        ->select(
            'presupuesto_items.id',
            'presupuesto_items.presupuesto_id',
            'presupuesto_items.componente_id',
            'presupuesto_items.texto',
            'presupuesto_items.total_precio', // Ahora obtenemos el precio del presupuesto_item
            'presupuesto_items.horas_trabajo', // Ahora obtenemos las horas de trabajo editadas
            'components.nombre as componente_nombre'
        )
        ->get();
    
    

        // Obtener todas las bicicletas y componentes disponibles
        $bikes = DB::table('bikes')->get();
        $components = DB::table('components')->get();

        return view('presupuestos.edit', compact('presupuesto', 'bikes', 'components', 'presupuesto_items'));
    }



    public function update(Request $request, $id)
    {
        $request->validate([
            'bike_id' => 'required|exists:bikes,id',
            'componentes' => 'required|array',
            'horas_trabajo' => 'required|array',
            'precio' => 'required|array',
            'textos' => 'nullable|array',
        ]);
    
        DB::beginTransaction();
        try {
            DB::table('presupuestos')
                ->where('id', $id)
                ->update([
                    'bike_id' => $request->bike_id,
                    'updated_at' => now(),
                ]);
    
            $componentesActuales = DB::table('presupuesto_items')
                ->where('presupuesto_id', $id)
                ->pluck('id', 'componente_id')
                ->toArray();
    
            $totalPresupuesto = 0;
            $totalHoras = 0;
    
            foreach ($request->componentes as $index => $componente_id) {
                $horas_trabajo = (int) $request->horas_trabajo[$index];
                $total_precio = (float) $request->precio[$index];
    
                $totalPresupuesto += $total_precio;
                $totalHoras += $horas_trabajo;
    
                $datosItem = [
                    'presupuesto_id' => $id,
                    'horas_trabajo' => $horas_trabajo,
                    'total_precio' => $total_precio,
                    'texto' => isset($request->textos[$index]) ? $request->textos[$index] : '', // Usa cadena vacía si no está definido
                    'updated_at' => now(),
                ];
    
                if (isset($componentesActuales[$componente_id])) {
                    DB::table('presupuesto_items')
                        ->where('id', $componentesActuales[$componente_id])
                        ->update($datosItem);
                    unset($componentesActuales[$componente_id]);
                } else {
                    $datosItem['componente_id'] = $componente_id;
                    $datosItem['created_at'] = now();
                    DB::table('presupuesto_items')->insert($datosItem);
                }
            }
    
            if (!empty($componentesActuales)) {
                DB::table('presupuesto_items')
                    ->whereIn('id', $componentesActuales)
                    ->delete();
            }
    
            DB::table('presupuestos')
                ->where('id', $id)
                ->update([
                    'horas_total' => $totalHoras,
                    'precio_total' => $totalPresupuesto,
                ]);
    
            DB::commit();
    
            return redirect()->route('presupuestos.index')->with('success', 'Presupuesto actualizado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al actualizar el presupuesto: ' . $e->getMessage());
        }
    }
    
    

    public function actualizarEstado(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:aprobado,denegado',
        ]);

        // Obtener el presupuesto y sus ítems
        $presupuesto = DB::table('presupuestos')->where('id', $id)->first();

        if (!$presupuesto) {
            return redirect()->route('presupuestos.index')->with('error', 'Presupuesto no encontrado.');
        }

        if ($request->estado === 'aprobado') {
            DB::beginTransaction(); // Iniciar transacción

            try {
                // Crear la cita
                $appointmentId = DB::table('appointments')->insertGetId([
                    'bike_id' => $presupuesto->bike_id,
                    'presupuesto_id' => $presupuesto->id,
                    'descripcion_problema' => 'Cita generada desde presupuesto aprobado',
                    'tiempo_estimado' => $presupuesto->horas_total, // Convertir horas a minutos
                    'estimacion_reparacion' => $presupuesto->precio_total,
                    'estado' => 'pendiente',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Obtener los ítems del presupuesto
                $presupuestoItems = DB::table('presupuesto_items')
                    ->where('presupuesto_id', $presupuesto->id)
                    ->get();

                // Insertar los ítems en appointment_component
                foreach ($presupuestoItems as $item) {
                    DB::table('appointment_component')->insert([
                        'appointment_id' => $appointmentId,
                        'componente_id' => $item->componente_id,
                        'texto' => $item->texto,
                        'total_precio' => $item->total_precio,
                        'horas_trabajo' => $item->horas_trabajo,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Actualizar el estado del presupuesto
                DB::table('presupuestos')->where('id', $id)->update([
                    'estado' => 'aprobado',
                    'updated_at' => now(),
                ]);

                DB::commit(); // Confirmar transacción

                return redirect()->route('presupuestos.index')->with('success', 'Presupuesto aprobado y cita creada.');
            } catch (\Exception $e) {
                dd($e);
                DB::rollBack(); // Revertir cambios en caso de error
                return redirect()->route('presupuestos.index')->with('error', 'Error al procesar la cita.');
            }
        }

        // Si es denegado, solo actualizar el estado del presupuesto
        DB::table('presupuestos')->where('id', $id)->update([
            'estado' => $request->estado,
            'updated_at' => now(),
        ]);

        return redirect()->route('presupuestos.index')->with('success', 'Presupuesto actualizado correctamente.');
    }
}
