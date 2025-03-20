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
    public function index()
    {
        $presupuestos = DB::table('presupuestos')
            ->leftJoin('bikes', 'presupuestos.bike_id', '=', 'bikes.id')
            ->leftJoin('users', 'bikes.user_id', '=', 'users.id')
            ->select('presupuestos.*', 'bikes.nombre as bike_nombre', 'users.name as user_nombre')
            ->paginate(10); // Agrega paginación

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

    public function descargarPDF($id)
    {
        // Obtener el presupuesto con la bicicleta y el usuario
        $presupuesto = DB::table('presupuestos')
            ->join('bikes', 'presupuestos.bike_id', '=', 'bikes.id')
            ->join('users', 'bikes.user_id', '=', 'users.id')
            ->where('presupuestos.id', $id)
            ->select(
                'presupuestos.*',
                'bikes.id as bicicleta_id', // Asegura que este campo esté disponible
                'bikes.nombre as bicicleta_nombre',
                'users.name as usuario_nombre',
                'users.telefono as usuario_telefono'
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
            ->join('components', 'presupuesto_items.componente_id', '=', 'components.id') // Cambio componente_id -> componente_id
            ->where('presupuesto_items.presupuesto_id', $id)
            ->select(
                'presupuesto_items.*',
                'components.nombre as componente_nombre',
                'components.hora_taller',
                'components.precio'
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
            'textos' => 'required|array',
        ]);
    
        // Actualizar el presupuesto principal
        DB::table('presupuestos')
            ->where('id', $id)
            ->update([
                'bike_id' => $request->bike_id,
                'updated_at' => now(),
            ]);
    
        // Eliminar los componentes actuales del presupuesto
        DB::table('presupuesto_items')->where('presupuesto_id', $id)->delete();
    
        // Insertar los nuevos componentes
        foreach ($request->componentes as $index => $componente_id) {
            DB::table('presupuesto_items')->insert([
                'presupuesto_id' => $id,
                'componente_id' => $componente_id,
                'horas_trabajo' => $request->horas_trabajo[$index],
                'total_precio' => $request->precio[$index],
                'texto' => $request->textos[$index],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    
        return redirect()->route('presupuestos.index')->with('success', 'Presupuesto actualizado correctamente.');
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
