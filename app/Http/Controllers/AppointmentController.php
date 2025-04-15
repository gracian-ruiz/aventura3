<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Bike;
use Illuminate\Http\Request;
use App\Models\Component;
use Carbon\Carbon;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $estado = $request->input('estado', 'pendiente'); // Estado seleccionado, por defecto 'pendiente'

        // Recalcular siempre antes de mostrar la vista
        $this->recalcularFechasAsignadas();

        $appointments = Appointment::with('bike.user', 'componentes')
            ->whereIn('estado', ['pendiente', 'en proceso']) // Asegurar que en proceso aparece
            ->when($search, function ($query, $search) {
                $query->whereHas('bike', function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%");
                })
                    ->orWhereHas('bike.user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('componentes', function ($q) use ($search) {
                        $q->where('nombre', 'like', "%{$search}%");
                    });
            })
            ->orderByRaw("
            CASE 
                WHEN estado = 'en proceso' AND prioridad = 'urgente' THEN 0  -- Urgentes en proceso primero
                WHEN estado = 'en proceso' THEN 1  -- Luego las demás en proceso
                WHEN prioridad = 'urgente' THEN 2  -- Luego urgentes en pendiente
                WHEN tiempo_estimado < 31 THEN 3  -- Luego las de menos de 30 minutos
                ELSE 4 
            END
        ")
            ->orderBy('fecha_asignada', 'asc')
            ->orderBy('created_at', 'asc')
            ->paginate(8);


        return view('appointments.index', compact('appointments', 'search', 'estado'));
    }

    public function confirmCompletion(Appointment $appointment)
    {
        // Obtener los componentes de la cita
        $data = DB::table('appointment_component')
        ->join('appointments', 'appointment_component.appointment_id', '=', 'appointments.id')
        ->join('components', 'appointment_component.componente_id', '=', 'components.id')
        ->where('appointment_component.appointment_id', $appointment->id)
        ->select(
            'appointment_component.id as ac_id',
            'appointment_component.checked',
            'appointment_component.texto',
            'appointment_component.total_precio',
            'appointment_component.horas_trabajo',
            'components.nombre as componente_nombre',
            'appointments.estado as appointment_estado',
            // ...añade los que necesites con alias
        )
        ->get();
    
       
        // Verificar si hay componentes sin marcar como completados
        $faltanComponentes = $data->contains(function ($item) {
            return !$item->checked; // Si hay al menos un componente sin marcar, devuelve true
        });
        return view('appointments.confirm', compact('appointment', 'data', 'faltanComponentes'));
    }
    
    public function complete(Request $request, Appointment $appointment)
    {
        $request->validate([
            'revisiones' => 'required|array',
            'revisiones.*' => 'exists:components,id',
            'descripcion_revisiones.*' => 'required|string',
            'proxima_revision.*' => 'nullable|date',
            'tipo_fecha.*' => 'required|in:fija,opcional',
        ]);
    
        foreach ($request->revisiones as $componente_id) {
            $descripcion = $request->descripcion_revisiones[$componente_id] ?? 'Sin descripción';
            $componente = Component::find($componente_id);
    
            if ($request->tipo_fecha[$componente_id] === 'fija') {
                $dias_a_sumar = $componente ? $componente->fecha_revision : 30;
                $fecha_proxima = now()->addDays($dias_a_sumar);
            } else {
                $fecha_proxima = $request->proxima_revision[$componente_id]
                    ? Carbon::parse($request->proxima_revision[$componente_id])
                    : now()->addDays(30);
            }
    
            $appointment->bike->revisions()->create([
                'componente_id' => $componente_id,
                'fecha_revision' => now(),
                'descripcion' => $descripcion,
                'proxima_revision' => $fecha_proxima,
            ]);
        }
    
        $appointment->update([
            'estado' => 'completada',
            'usuario_taller_id' => auth()->id(),
        ]);
    
        // Llamar al controlador de recordatorios para enviar mensaje de WhatsApp
        app(RecordatorioController::class)->enviarMensajeFinalizacionCita($appointment);
    
        return redirect()->route('appointments.index')->with('success', '✅ Cita completada y revisiones generadas correctamente.');
    }



    public function create()
    {
        $bikes = Bike::all();
        $componentes = Component::all(); // Agregamos los componentes

        return view('appointments.create', compact('bikes', 'componentes'));
    }

    public function updatedos(Request $request, $id)
    {
        $request->validate([
            'bike_id' => 'required|exists:bikes,id',
            'componentes' => 'required|array',
            'horas_trabajo' => 'required|array',
            'precio' => 'required|array',
            'textos' => 'nullable|array',
            'prioridad' => 'required|in:normal,urgente',
            'descuento' => 'nullable|array', // Validación de descuentos
        ]);
    
        DB::beginTransaction();
        try {
            DB::table('appointments')
                ->where('id', $id)
                ->update([
                    'bike_id' => $request->bike_id,
                    'prioridad' => $request->prioridad,
                    'updated_at' => now(),
                ]);
    
            $componentesActuales = DB::table('appointment_component')
                ->where('appointment_id', $id)
                ->pluck('id', 'componente_id')
                ->toArray();
    
            $totalPresupuesto = 0;
            $totalHoras = 0;
    
            foreach ($request->componentes as $index => $componente_id) {
                $horas_trabajo = (int) $request->horas_trabajo[$index];
                $total_precio = (float) $request->precio[$index];
                $descuento = isset($request->descuento[$index]) ? (float) $request->descuento[$index] : 0; // Obtener descuento
    
                $totalPresupuesto += $total_precio;
                $totalHoras += $horas_trabajo;
    
                $datosItem = [
                    'appointment_id' => $id,
                    'horas_trabajo' => $horas_trabajo,
                    'total_precio' => $total_precio,
                    'descuento' => $descuento, // Incluir descuento
                    'texto' => isset($request->textos[$index]) ? $request->textos[$index] : '', // Texto del trabajo
                    'updated_at' => now(),
                ];
    
                if (isset($componentesActuales[$componente_id])) {
                    DB::table('appointment_component')
                        ->where('id', $componentesActuales[$componente_id])
                        ->update($datosItem);
                    unset($componentesActuales[$componente_id]);
                } else {
                    $datosItem['componente_id'] = $componente_id;
                    $datosItem['created_at'] = now();
                    DB::table('appointment_component')->insert($datosItem);
                }
            }
    
            if (!empty($componentesActuales)) {
                DB::table('appointment_component')
                    ->whereIn('id', $componentesActuales)
                    ->delete();
            }
    
            DB::table('appointments')
                ->where('id', $id)
                ->update([
                    'horas_total' => $totalHoras,
                    'precio_total' => $totalPresupuesto,
                ]);
    
            DB::commit();
    
            return redirect()->route('appointments.index')->with('success', 'Presupuesto actualizado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al actualizar el presupuesto: ' . $e->getMessage());
        }
    }    


    public function edit($id)
    {
        // Obtener el presupuesto con los datos de la bicicleta y el usuario
        $presupuesto = DB::table('appointments')
            ->leftJoin('bikes', 'appointments.bike_id', '=', 'bikes.id')
            ->leftJoin('users', 'bikes.user_id', '=', 'users.id')
            ->select('appointments.*', 'bikes.id as bike_id', 'bikes.nombre as bike_nombre', 'users.name as user_nombre')
            ->where('appointments.id', $id)
            ->first();

        if (!$presupuesto) {
            abort(404);
        }

        // Obtener todos los ítems asociados a este presupuesto
        $presupuesto_items = DB::table('appointment_component')
            ->join('components', 'appointment_component.componente_id', '=', 'components.id')
            ->where('appointment_component.appointment_id', $id)
            ->select(
                'appointment_component.id',
                'appointment_component.appointment_id',
                'appointment_component.componente_id',
                'appointment_component.texto',
                'appointment_component.descuento',
                'appointment_component.total_precio', // Ahora obtenemos el precio del presupuesto_item
                'appointment_component.horas_trabajo', // Ahora obtenemos las horas de trabajo editadas
                'components.nombre as componente_nombre'
            )
            ->get();



        // Obtener todas las bicicletas y componentes disponibles
        $bikes = DB::table('bikes')->get();
        $components = DB::table('components')->get();

        return view('appointments.edit', compact('presupuesto', 'bikes', 'components', 'presupuesto_items'));
    }


    public function updateEstado(Request $request, Appointment $appointment)
    {
        $nuevoEstado = $request->input('estado');

        if (!in_array($nuevoEstado, ['pendiente', 'en proceso', 'reparacion', 'completada'])) {
            return redirect()->back()->with('error', 'Estado no válido.');
        }

        $appointment->update(['estado' => $nuevoEstado]);

        if ($nuevoEstado === 'reparacion') {
            return redirect()->route('appointments.repair', ['appointment' => $appointment->id])
                ->with('success', 'Cita en fase de reparación.');
        }

        if ($nuevoEstado === 'completada') {
            return redirect()->route('bikes.revisions.create', ['bike' => $appointment->bike_id])
                ->with('success', 'Cita completada y revisiones generadas.');
        }

        return redirect()->route('appointments.index')->with('success', 'Estado de la cita actualizado.');
    }


    public function historico(Request $request)
    {
        $search = $request->input('search');

        $completedAppointments = Appointment::with('bike.user', 'componentes')
            ->where('estado', 'completada')
            ->when($search, function ($query, $search) {
                $query->whereHas('bike', function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%");
                })
                    ->orWhereHas('bike.user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('componentes', function ($q) use ($search) {
                        $q->where('nombre', 'like', "%{$search}%");
                    });
            })
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        return view('appointments.historico', compact('completedAppointments', 'search'));
    }

    public function destroy(Appointment $appointment)
    {
        if ($appointment->estado === 'completada') {
            $appointment->delete();
            return redirect()->route('appointments.historico')->with('success', '✅ Cita eliminada del historial.');
        }

        $appointment->delete();
        return redirect()->route('appointments.index')->with('success', '✅ Cita eliminada correctamente.');
    }

    public function asignarFechasCitas()
    {
        $horas_laborales = [
            'Monday' => 420,
            'Tuesday' => 420,
            'Wednesday' => 420,
            'Thursday' => 420,
            'Friday' => 420,
            'Saturday' => 240,
        ];

        $citas = Appointment::whereNull('fecha_asignada')
            ->orderByRaw("CASE WHEN prioridad = 'urgente' THEN 1 ELSE 2 END")
            ->orderBy('created_at', 'asc')
            ->get();

        $agenda = [];
        $fecha_actual = now()->startOfDay();

        foreach ($citas as $cita) {
            while (true) {
                $dia_semana = $fecha_actual->format('l');

                if (isset($horas_laborales[$dia_semana])) {
                    if (!isset($agenda[$fecha_actual->toDateString()])) {
                        $agenda[$fecha_actual->toDateString()] = 0;
                    }

                    if ($agenda[$fecha_actual->toDateString()] + $cita->tiempo_estimado <= $horas_laborales[$dia_semana]) {
                        $cita->fecha_asignada = $fecha_actual->toDateString();
                        $cita->save();
                        $agenda[$fecha_actual->toDateString()] += $cita->tiempo_estimado;
                        break;
                    }
                }

                $fecha_actual->addDay();
            }
        }
    }

    public function store(StoreAppointmentRequest $request)
    {
        $appointment = Appointment::create([
            'bike_id' => $request->bike_id,
            'prioridad' => $request->prioridad,
            'descripcion_problema' => $request->descripcion_problema,
            'estimacion_reparacion' => $request->estimacion_reparacion,
            'tiempo_estimado' => $request->tiempo_estimado,
            'estado' => 'pendiente',
        ]);

        // Asociar los componentes seleccionados a la cita
        $appointment->componentes()->attach($request->componentes);

        // 🔥 Recalcula fechas automáticamente
        $this->recalcularFechasAsignadas();

        return redirect()->route('appointments.index')->with('success', 'Cita registrada correctamente.');
    }


    private function recalcularFechasAsignadas()
    {
        $horas_laborales = [
            'monday'    => 300,  // 5 horas
            'tuesday'   => 300,
            'wednesday' => 300,
            'thursday'  => 300,
            'friday'    => 300,
            'saturday'  => 200,  // 3 horas y 20 minutos
        ];
    
        // Reiniciar fechas para recalcular desde cero
        Appointment::whereIn('estado', ['pendiente', 'en proceso'])->update(['fecha_asignada' => null]);
    
        $appointments = Appointment::whereIn('estado', ['pendiente', 'en proceso'])
            ->orderByRaw("
                CASE 
                    WHEN estado = 'en proceso' AND prioridad = 'urgente' THEN 0  -- Urgentes en proceso primero
                    WHEN prioridad = 'urgente' THEN 1  -- Luego urgentes en pendiente
                    WHEN tiempo_estimado < 30 THEN 2  -- Luego las de menos de 30 minutos
                    ELSE 3 
                END
            ")
            ->orderBy('created_at', 'asc')
            ->get();
    
        $fecha_actual = Carbon::today();
        $ahora = Carbon::now();
        $hora_cierre = $fecha_actual->copy()->setTime(20, 0);
    
        // Si ya cerró la tienda, empezamos desde el siguiente día laboral
        if ($ahora->greaterThanOrEqualTo($hora_cierre)) {
            do {
                $fecha_actual->addDay();
                $dia_semana = strtolower($fecha_actual->format('l'));
            } while ($dia_semana === 'sunday' || !isset($horas_laborales[$dia_semana]));
        }
    
        $agenda = [];
    
        foreach ($appointments as $appointment) {
            $tiempo_estimado = $appointment->horas_total;   
            while (true) {
                $dia_semana = strtolower($fecha_actual->format('l'));
    
                if ($dia_semana === 'sunday' || !isset($horas_laborales[$dia_semana])) {
                    $fecha_actual->addDay();
                    continue;
                }
    
                // Inicializar disponibilidad del día si no existe en la agenda
                if (!isset($agenda[$fecha_actual->toDateString()])) {
                    $agenda[$fecha_actual->toDateString()] = 0;
                }
    
                // **Asignar urgentes primero si hay huecos**
                if ($appointment->prioridad === 'urgente' || $agenda[$fecha_actual->toDateString()] + $tiempo_estimado <= $horas_laborales[$dia_semana]) {
                    $appointment->fecha_asignada = $fecha_actual->toDateString();
                    $appointment->save();
                    $agenda[$fecha_actual->toDateString()] += $tiempo_estimado;
                    break;
                } else {
                    $fecha_actual->addDay();
                }
            }
        }
    }
    

    public function show($id)
    {
        $appointment = DB::table('appointments')
            ->join('bikes', 'appointments.bike_id', '=', 'bikes.id')
            ->leftJoin('appointment_component', 'appointments.id', '=', 'appointment_component.appointment_id')
            ->leftJoin('components', 'appointment_component.componente_id', '=', 'components.id') // Asegúrate de que es `componente_id`
            ->select(
                'appointments.id as appointment_id',
                'appointment_component.usuario_taller_id',
                'appointments.presupuesto_id as presupuesto',
                'appointments.fecha_asignada as appointment_fecha', // Corregido según tu modelo
                'bikes.nombre as bike_nombre',
                'bikes.marca as bike_marca',
                'components.nombre as component_nombre',
                'appointment_component.horas_trabajo',
                'appointment_component.total_precio',
                'appointment_component.texto'
            )
            ->where('appointments.id', $id)
            ->get();


        if ($appointment->isEmpty()) {
            abort(404, 'Cita no encontrada');
        }

        return view('appointments.show', compact('appointment'));
    }

    public function showReparacion(Appointment $appointment)
    {
        $data = DB::table('appointment_component')
        ->join('appointments', 'appointment_component.appointment_id', '=', 'appointments.id')
        ->join('components', 'appointment_component.componente_id', '=', 'components.id')
        ->where('appointment_component.appointment_id', $appointment->id)
        ->select(
            'appointment_component.id',
            'appointment_component.usuario_taller_id',
            'appointment_component.texto',
            'appointment_component.total_precio',
            'appointment_component.horas_trabajo',
            'appointment_component.checked',
            'appointments.id as appointment_id',
            'appointments.fecha_asignada',
            'appointments.prioridad',
            'appointments.estado',
            'appointments.descripcion_problema',
            'appointments.estimacion_reparacion',
            'components.id as componente_id',
            'components.nombre as component_nombre',
            'components.fecha_preaviso',
            'components.fecha_revision'
        )
        ->get();
    

            // Verificar lo que contiene la variable $data
        ;

        return view('appointments.reparacion', compact('appointment', 'data'));
    }

    
    public function updateReparacion(Request $request, Appointment $appointment)
    {
        // Validar los componentes seleccionados
        $request->validate([
            'componentes' => 'array',
            'componentes.*.id' => 'exists:components,id',
            'componentes.*.checked' => 'boolean',
            'kilometros' => 'nullable|numeric|min:0', // Validar los kilómetros si vienen
        ]);
    
        $usuarioTallerId = auth()->id(); // Obtener el ID del usuario autenticado
    
        // Actualizar el estado de los componentes de la cita
        foreach ($request->componentes as $component) {
            $checked = isset($component['checked']) ? true : false;
    
            DB::table('appointment_component')
                ->where('appointment_id', $appointment->id)
                ->where('componente_id', $component['id'])
                ->update([
                    'checked' => $checked,
                    'usuario_taller_id' => $checked ? $usuarioTallerId : null
                ]);
        }
    
        // Actualizar los kilómetros si se enviaron
        if ($request->filled('kilometros')) {
            $appointment->bike->kilometros = $request->input('kilometros');
            $appointment->bike->save();
        }
    
        return redirect()->route('appointments.index')->with('success', 'Reparación actualizada exitosamente.');
    }
    
    
    
    
    

}
