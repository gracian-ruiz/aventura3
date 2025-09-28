<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Bike;
use Illuminate\Http\Request;
use App\Models\Component;
use Carbon\Carbon;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Http\Controllers\Alquiler\EnviarCorreosController;
use App\Http\Controllers\EnviarCorreosController as ControllersEnviarCorreosController;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $estado = $request->input('estado', 'pendiente'); // por defecto 'pendiente'

        // Recalcular siempre antes de mostrar la vista
        $this->recalcularFechasAsignadas();

        $appointments = Appointment::with('bike.user', 'componentes')
            ->whereIn('estado', ['pendiente', 'en proceso'])
            ->where(function ($query) use ($search) {
                if ($search) {
                    $query->whereHas('bike', function ($q) use ($search) {
                        $q->where('nombre', 'like', '%' . $search . '%')
                            ->orWhereHas('user', function ($qq) use ($search) {
                                $qq->where('name', 'like', '%' . $search . '%');
                            });
                    })
                        ->orWhere('idprograma', 'like', '%' . $search . '%');
                }
            })
            ->orderBy('fecha_asignada', 'asc') // 🔹 Ahora el criterio único
            ->paginate(8);

        return view('appointments.index', compact('appointments', 'search', 'estado'));
    }



    public function indextaller(Request $request)
    {
        $user = auth()->user()->id;
        $search = $request->input('search');
        $estado = $request->input('estado', 'pendiente'); // Estado seleccionado, por defecto 'pendiente'

        // Recalcular siempre antes de mostrar la vista
        $this->recalcularFechasAsignadas();
        $search = $request->input('search'); // Obtén el término de búsqueda desde el input

        $search = $request->input('search');

        $appointments = Appointment::with('bike.user', 'componentes')
            ->whereIn('estado', ['pendiente', 'en proceso'])
            ->whereJsonContains('asignacion_taller', (string) auth()->user()->id)
            ->where(function ($query) use ($search) {
                if ($search) {
                    $query->whereHas('bike', function ($q) use ($search) {
                        $q->where('nombre', 'like', '%' . $search . '%')
                            ->orWhere('marca', 'like', '%' . $search . '%') // 🔍 búsqueda por marca
                            ->orWhereHas('user', function ($qq) use ($search) {
                                $qq->where('nombre', 'like', '%' . $search . '%');
                            });
                    });
                }
            })
            ->orderByRaw('
                CASE 
                    -- EN PROCESO
                    WHEN estado = "en proceso" AND prioridad = "urgente" AND horas_total < 30 THEN 1
                    WHEN estado = "en proceso" AND prioridad = "urgente" AND horas_total >= 30 THEN 2
                    WHEN estado = "en proceso" AND prioridad = "normal" AND horas_total < 30 THEN 3
                    WHEN estado = "en proceso" AND prioridad = "normal" AND horas_total >= 30 THEN 4
                    -- PENDIENTE
                    WHEN estado = "pendiente" AND prioridad = "urgente" AND horas_total < 30 THEN 5
                    WHEN estado = "pendiente" AND prioridad = "urgente" AND horas_total >= 30 THEN 6
                    WHEN estado = "pendiente" AND prioridad = "normal" AND horas_total < 30 THEN 7
                    ELSE 8
                END
            ')
            ->orderBy('horas_total', 'asc')
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
                'appointments.estado as appointment_estado'
            )
            ->get();

        // Verificar si hay componentes sin marcar como completados
        $faltanComponentes = $data->contains(function ($item) {
            return !$item->checked;
        });

        // Obtener información del usuario y bicicleta
        $user = $appointment->bike->user;
        $bike = $appointment->bike;

        // Generar mensaje de finalización
        $mensaje = "✅ ¡Hola {$user->name}! Tu bicicleta {$bike->nombre} ya está lista.\n"
            . "Puedes pasar a recogerla en nuestro horario habitual. ¡Gracias! 🚴";

        // Teléfono y nombre del cliente para la vista
        $telefono = $user->telefono ?? 'No disponible';
        $nombre = $user->name;

        // Pasar todo a la vista
        return view('appointments.confirm', compact(
            'appointment',
            'data',
            'faltanComponentes',
            'mensaje',
            'telefono',
            'nombre'
        ));
    }

    //AQUI TERMINA EL PROCESO DE RAPACION CUANDO LE DAS AL BOTON DE CONFIRMAR FINALIZACION 
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
        //app(RecordatorioController::class)->enviarMensajeFinalizacionCita($appointment);
        $correoController = new ControllersEnviarCorreosController();
        $correoController->enviarCompletado($appointment->id);

        return redirect()->route('appointments.index')->with('success', '✅ Cita completada y revisiones generadas correctamente.');
    }

    public function updatedos(Request $request, $id)
    {
        $request->validate([
            'bike_id' => 'required|exists:bikes,id',
            'componentes' => 'required|array',
            'horas_trabajo' => 'required|array',
            'precio' => 'required|array',
            'textos' => 'nullable|array',
            'idprograma' => 'nullable',
            'prioridad' => 'required|in:normal,urgente',
            'descuento' => 'nullable|array', // Validación de descuentos
            'asignacion_taller' => 'nullable|array',
            'asignacion_taller.*' => 'exists:users,id',
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
                    'asignacion_taller' => $request->asignacion_taller ?? [],
                    'idprograma' => $request->idprograma,
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


        $usuariosTaller = DB::table('users')
            ->whereIn('role', ['admin', 'taller'])
            ->select('id', 'name')
            ->get();




        // Obtener todas las bicicletas y componentes disponibles
        $bikes = DB::table('bikes')->get();
        $components = DB::table('components')->get();

        return view('appointments.edit', compact('presupuesto', 'bikes', 'components', 'presupuesto_items', 'usuariosTaller'));
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
        DB::table('appointments')
            ->whereIn('estado', ['pendiente', 'en proceso'])
            ->update(['fecha_asignada' => null]);

        // Obtener citas con orden de prioridad
        $appointments = Appointment::whereIn('estado', ['pendiente', 'en proceso'])
            ->orderByRaw("
        CASE 
            WHEN estado = 'en proceso' AND prioridad = 'urgente' AND horas_total < 30 THEN 1
            WHEN estado = 'en proceso' AND prioridad = 'urgente' AND horas_total >= 30 THEN 2
            WHEN estado = 'en proceso' AND prioridad = 'normal' AND horas_total < 30 THEN 3
            WHEN estado = 'en proceso' AND prioridad = 'normal' AND horas_total >= 30 THEN 4
            WHEN estado = 'pendiente' AND prioridad = 'urgente' AND horas_total < 30 THEN 5
            WHEN estado = 'pendiente' AND prioridad = 'urgente' AND horas_total >= 30 THEN 6
            WHEN estado = 'pendiente' AND prioridad = 'normal' AND horas_total < 30 THEN 7
            ELSE 8
        END
    ")
            ->orderBy('created_at', 'asc') // ⏳ primero por fecha de entrada
            ->orderBy('id', 'asc')         // 🔑 luego por id para evitar empates
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

                // Comprobar si cabe en el día
                if ($appointment->prioridad === 'urgente' || $agenda[$fecha_actual->toDateString()] + $tiempo_estimado <= $horas_laborales[$dia_semana]) {
                    DB::table('appointments')
                        ->where('id', $appointment->id)
                        ->update(['fecha_asignada' => $fecha_actual->toDateString()]);

                    $agenda[$fecha_actual->toDateString()] += $tiempo_estimado;
                    break;
                } else {
                    $fecha_actual->addDay();
                }
            }
        }
    }


    private function recalcularFechasAsignadas2()
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
            ->join('bikes', 'appointments.bike_id', '=', 'bikes.id')
            ->join('users', 'bikes.user_id', '=', 'users.id')
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
                'appointments.idprograma',
                'appointments.estimacion_reparacion',
                'components.id as componente_id',
                'components.nombre as component_nombre',
                'components.fecha_preaviso',
                'components.fecha_revision',
                'bikes.id as bike_id',
                'bikes.nombre as bike_nombre',
                'users.id as user_id',
                'users.name as user_name',
                'users.email as user_email'
            )
            ->get();

        return view('appointments.reparacion', compact('appointment', 'data'));
    }



    public function updateReparacion(Request $request, Appointment $appointment)
    {
        // Validar los datos recibidos
        $request->validate([
            'componentes' => 'array',
            'componentes.*.id' => 'exists:components,id',
            'componentes.*.checked' => 'boolean',
            'kilometros' => 'nullable|numeric|min:0',
            'descripcion_problema' => 'nullable|string|max:1000', // Validar la descripción si viene
            'idprograma' => 'nullable|string|max:200',
        ]);

        $usuarioTallerId = auth()->id(); // ID del usuario autenticado

        // Actualizar estado de los componentes seleccionados
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

        // Actualizar los kilómetros si se proporcionaron
        if ($request->filled('kilometros')) {
            $appointment->bike->kilometros = $request->input('kilometros');
            $appointment->bike->save();
        }

        // Actualizar la descripción del problema si se proporciona
        // Actualizar la descripción del problema
        if ($request->has('descripcion_problema')) {
            $descripcion = $request->input('descripcion_problema');

            if (strtolower(trim($descripcion)) === 'nada') {
                // Si el usuario pone "nada", lo dejamos vacío (NULL en BD)
                $appointment->descripcion_problema = null;
            } else {
                // Guardamos el valor normal
                $appointment->descripcion_problema = $descripcion;
            }

            $appointment->save();
        }


        return redirect()->route('appointments.index')->with('success', 'Reparación actualizada exitosamente.');
    }

    public function calendariocitas()
    {
        $resultados = DB::table('appointments')
            ->join('bikes', 'bikes.id', '=', 'appointments.bike_id')
            ->join('users', 'users.id', '=', 'bikes.user_id')
            ->select(
                'appointments.id as presupuesto_id',
                'bikes.nombre as bicicleta',
                'users.name as usuario',
                'appointments.calendario',
                'appointments.estado'
            )
            ->whereNotNull('appointments.calendario')
            ->get();

        $eventos = $resultados->map(function ($item) {
            $color = '#4ade80'; // verde fijo (puedes cambiar según estado si quieres)

            return [
                'title' => $item->usuario . "\n" . $item->bicicleta . ' - ' . $item->presupuesto_id,
                'start' => $item->calendario,
                'url' => url('/presupuestos/' . $item->presupuesto_id . '/factura'),
                'color' => $color,
            ];
        });

        return view('appointments.calendario', ['eventos' => $eventos]);
    }

    public function quitarOrdenTaller(Appointment $appointment)
    {
        try {
            // Cambiar estado a presupuesto
            $appointment->update([
                'estado' => 'presupuesto',
            ]);

            return redirect()->route('appointments.index')
                ->with('success', '✅ La cita se ha pasado a estado presupuesto correctamente.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', '❌ Error al actualizar la cita: ' . $e->getMessage());
        }
    }



    public function calendarioAsignado()
    {
        $resultados = Appointment::with('bike.user')
            ->whereNotNull('fecha_asignada')
            ->whereIn('estado', ['pendiente', 'en proceso'])
            ->orderBy('fecha_asignada', 'asc') // 👈 ordenar por fecha asignada
            ->get();

        $eventos = $resultados->map(function ($item) {
            // Color por defecto según estado
            $color = match ($item->estado) {
                'pendiente'   => '#facc15', // amarillo
                'en proceso'  => '#60a5fa', // azul
                'completada'  => '#22c55e', // verde
                default       => '#a1a1aa', // gris
            };

            // Si tiene descripción del problema, marcar en rojo
            if (!empty($item->descripcion_problema)) {
                $color = '#ef4444'; // 🔴 rojo
            }

            return [
                'title' => $item->bike->user->name . " - " . $item->bike->nombre . " (#{$item->id})",
                'start' => $item->fecha_asignada,
                'url'   => route('appointments.show', $item->id),
                'color' => $color,
            ];
        });

        return view('appointments.calendario_asignado', ['eventos' => $eventos]);
    }
}
