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
use Illuminate\Support\Facades\Log;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $filtro = $request->input('filtro', 'todos'); // Por defecto: 'todos'

        // Recalcular siempre antes de mostrar la vista
        $this->recalcularFechasAsignadas();

        $query = Appointment::with('bike.user', 'componentes');

        // 🔹 Aplicar filtros
        switch ($filtro) {
            case 'proceso':
                $query
                    ->where('descripcion_problema', '=', null)
                    ->where('estado', 'en proceso');
                break;

            case 'sin-hacer':
                $query->where('estado', 'pendiente');
                break;

            case 'premium':
                // 💎 Mostrar premium solo en pendiente o en proceso
                $query->where('prioridad', 'premium')
                      ->whereIn('estado', ['pendiente', 'en proceso'])
                      ->orderByRaw("
                          CASE
                              WHEN estado = 'en proceso' THEN 1
                              WHEN estado = 'pendiente' THEN 2
                              ELSE 3
                          END
                      ");
                break;

            case 'incidencia':
                $query->whereNotNull('descripcion_problema')
                    ->where('descripcion_problema', '!=', '')
                    ->whereIn('estado', ['en proceso', 'pendiente']);
                break;

            default: // 🔸 “Todos” (como tu versión original)
                $query->whereIn('estado', ['pendiente', 'en proceso'])
                    ->orderByRaw("
                    CASE
                        WHEN estado = 'en proceso' THEN 1
                        WHEN estado = 'pendiente' THEN 2
                        ELSE 3
                    END
                "); // primero en proceso, luego pendiente
                break;
        }

        // 🔍 Buscador
        if ($search) {
            $query->buscar($search);
        }

        // 🔹 Orden por prioridad y fecha
        $query->orderByRaw("
        CASE
            WHEN prioridad = 'premium' THEN 0
            WHEN prioridad = 'urgente' THEN 1
            WHEN prioridad = 'normal' THEN 2
            ELSE 3
        END
    ")->orderBy('fecha_asignada', 'asc');

        // 🔹 Paginar
        $appointments = $query->paginate(8)->appends(['search' => $search, 'filtro' => $filtro]);

        return view('appointments.index', compact('appointments', 'search', 'filtro'));
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
            'calendario' => null,
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
            'prioridad' => 'required|in:normal,urgente,premium',
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
                    'calendario' => $request->calendario
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

    private function recalcularFechasAsignadas2()
    {
        $horas_laborales = [
            'monday'    => 300,  // 5 horas
            'tuesday'   => 300,
            'wednesday' => 300,
            'thursday'  => 300,
            'friday'    => 300,
            'saturday'  => 200,  // 3h 20min
        ];

        // 🔸 No borramos las fechas fijas de los Premium
        DB::table('appointments')
            ->whereIn('estado', ['pendiente', 'en proceso'])
            ->where('fecha_fija', false)
            ->update(['fecha_asignada' => null]);

        // 🔸 Obtener todas las citas que necesitan asignación
        $appointments = Appointment::whereIn('estado', ['pendiente', 'en proceso'])
            ->orderByRaw("
            CASE
                WHEN fecha_fija = 1 THEN 0        -- 📅 Premium con fecha fija (no se tocarán)
                WHEN prioridad = 'premium' THEN 1 -- 💎 Premium sin fecha fija
                WHEN prioridad = 'urgente' THEN 2 -- 🔴 Urgente
                ELSE 3                            -- 🟡 Normal
            END
        ")
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $fecha_actual = Carbon::today();
        $ahora = Carbon::now();
        $hora_cierre = $fecha_actual->copy()->setTime(20, 0);

        // 🔹 Si ya cerró la tienda, pasar al siguiente día laboral
        if ($ahora->greaterThanOrEqualTo($hora_cierre)) {
            do {
                $fecha_actual->addDay();
                $dia_semana = strtolower($fecha_actual->format('l'));
            } while ($dia_semana === 'sunday' || !isset($horas_laborales[$dia_semana]));
        }

        $agenda = [];

        foreach ($appointments as $appointment) {
            // 🔹 Saltar premium con fecha fija
            if ($appointment->fecha_fija) {
                continue;
            }

            $tiempo_estimado = $appointment->horas_total ?: 30; // por si no está definido

            while (true) {
                $dia_semana = strtolower($fecha_actual->format('l'));

                // Saltar domingos o días no laborales
                if ($dia_semana === 'sunday' || !isset($horas_laborales[$dia_semana])) {
                    $fecha_actual->addDay();
                    continue;
                }

                // Inicializar el día si no existe aún
                if (!isset($agenda[$fecha_actual->toDateString()])) {
                    $agenda[$fecha_actual->toDateString()] = 0;
                }

                // 🔹 Si cabe la cita en el día
                if ($agenda[$fecha_actual->toDateString()] + $tiempo_estimado <= $horas_laborales[$dia_semana]) {
                    DB::table('appointments')
                        ->where('id', $appointment->id)
                        ->update(['fecha_asignada' => $fecha_actual->toDateString()]);

                    $agenda[$fecha_actual->toDateString()] += $tiempo_estimado;
                    break;
                } else {
                    // Si no cabe, pasar al siguiente día laboral
                    $fecha_actual->addDay();
                }
            }
        }
    }



    private function recalcularFechasAsignadas()
    {
        Log::info('🔄 Iniciando recalcularFechasAsignadas()');
        $startTotal = microtime(true);

        $horas_laborales = [
            'monday'    => 300,
            'tuesday'   => 300,
            'wednesday' => 300,
            'thursday'  => 300,
            'friday'    => 300,
            'saturday'  => 200,
        ];

        $fecha_inicio = now()->startOfDay();
        $minutos_dia = 300; // 5h por día base
        $acumulado = 0;
        $agenda = [];

        // 🔸 Reset de fechas
        DB::table('appointments')
            ->whereIn('estado', ['pendiente', 'en proceso'])
            ->where('fecha_fija', false)
            ->update(['fecha_asignada' => null]);

        // 🔸 Obtener todas las citas
        $appointments = DB::table('appointments')
            ->select('id', 'horas_total', 'fecha_fija', 'prioridad', 'created_at')
            ->whereIn('estado', ['pendiente', 'en proceso'])
            ->orderByRaw("
            CASE
                WHEN fecha_fija = 1 THEN 0
                WHEN prioridad = 'premium' THEN 1
                WHEN prioridad = 'urgente' THEN 2
                ELSE 3
            END
        ")
            ->orderBy('created_at', 'asc')
            ->get();

        $updates = [];

        foreach ($appointments as $a) {
            if ($a->fecha_fija) continue;

            $tiempo = $a->horas_total ?: 30;

            // Calcula en qué día cae según los minutos acumulados
            $dia_offset = floor($acumulado / $minutos_dia);

            // Ajustar si cae en domingo o día sin horas laborales
            $fecha = $fecha_inicio->copy()->addDays($dia_offset);
            while (true) {
                $dia_semana = strtolower($fecha->format('l'));
                if (isset($horas_laborales[$dia_semana])) break;
                $fecha->addDay(); // salta domingos
            }

            $updates[] = [
                'id' => $a->id,
                'fecha_asignada' => $fecha->toDateString(),
            ];

            $agenda[$fecha->toDateString()] = ($agenda[$fecha->toDateString()] ?? 0) + $tiempo;
            $acumulado += $tiempo;
        }

        // 🔸 Actualización masiva
        if ($updates) {
            $query = "UPDATE appointments SET fecha_asignada = CASE id ";
            $ids = [];

            foreach ($updates as $u) {
                $query .= "WHEN {$u['id']} THEN '{$u['fecha_asignada']}' ";
                $ids[] = $u['id'];
            }

            $query .= "END WHERE id IN (" . implode(',', $ids) . ")";
            DB::statement($query);
        }

        $timeTotal = round(microtime(true) - $startTotal, 2);
        Log::info("✅ recalcularFechasAsignadas() completado en {$timeTotal}s con " . count($updates) . " citas");
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
            'descripcion_problema' => 'nullable|string|max:1000',
            'idprograma' => 'nullable|string|max:200',
        ]);

        $usuarioTallerId = auth()->id(); // ID del usuario autenticado
        $tiempoTotalRestado = 0;

        // Actualizar estado de los componentes seleccionados
        foreach ($request->componentes as $component) {
            $checked = isset($component['checked']) ? true : false;

            // Obtener datos del pivote
            $pivot = DB::table('appointment_component')
                ->where('appointment_id', $appointment->id)
                ->where('componente_id', $component['id'])
                ->first();

            if ($pivot) {
                // ⚡️ Si antes no estaba marcado y ahora sí -> restamos horas_trabajo
                if ($checked && !$pivot->checked) {
                    $tiempoTotalRestado += (int) $pivot->horas_trabajo;
                }

                DB::table('appointment_component')
                    ->where('appointment_id', $appointment->id)
                    ->where('componente_id', $component['id'])
                    ->update([
                        'checked' => $checked,
                        'usuario_taller_id' => $checked ? $usuarioTallerId : null
                    ]);
            }
        }

        // Restar tiempo al appointment si corresponde
        if ($tiempoTotalRestado > 0) {
            $appointment->tiempo_reparacion = max(0, $appointment->tiempo_reparacion - $tiempoTotalRestado);
            $appointment->save();
        }

        // Actualizar los kilómetros si se proporcionaron
        if ($request->filled('kilometros')) {
            $appointment->bike->kilometros = $request->input('kilometros');
            $appointment->bike->save();
        }

        // Actualizar la descripción del problema si se proporciona
        if ($request->has('descripcion_problema')) {
            $descripcion = $request->input('descripcion_problema');

            if (strtolower(trim($descripcion)) === 'nada') {
                $appointment->descripcion_problema = null;
            } else {
                $appointment->descripcion_problema = $descripcion;
            }

            $appointment->save();
        }

        return redirect()->route('appointments.index')->with('success', 'Reparación actualizada exitosamente.');
    }

    public function calendariocitas()
    {
        // 📅 Fecha actual
        $hoy = now()->toDateString();

        // 🔁 Mover TODAS las citas con fecha pasada (no completadas) al día de hoy
        DB::table('appointments')
            ->whereNotNull('calendario')
            ->where('estado', '!=', 'completada') // solo las activas
            ->where('calendario', '<', $hoy)      // todas las fechas anteriores a hoy
            ->update(['calendario' => $hoy]);

        // 🔍 Obtener solo citas activas (sin completadas)
        $resultados = DB::table('appointments')
            ->join('bikes', 'bikes.id', '=', 'appointments.bike_id')
            ->join('users', 'users.id', '=', 'bikes.user_id')
            ->select(
                'appointments.id as presupuesto_id',
                'bikes.nombre as bike_nombre',
                'bikes.marca as bike_marca',
                'users.name as usuario',
                'appointments.calendario',
                'appointments.estado'
            )
            ->whereNotNull('appointments.calendario')
            ->where('appointments.estado', '!=', 'completada') // 🔹 no mostrar completadas
            ->get();

        // 🎨 Preparar los eventos para el calendario
        $eventos = $resultados->map(function ($item) {
            $color = match ($item->estado) {
                'pendiente'   => '#facc15', // amarillo
                'en proceso'  => '#60a5fa', // azul
                default       => '#a1a1aa', // gris
            };

            // 🧾 Mostrar cliente y bicicleta (marca + nombre)
            $titulo = "<b>{$item->usuario}</b><br>{$item->bike_marca} - {$item->bike_nombre}";

            return [
                'title' => $titulo,
                'start' => $item->calendario,
                'url'   => url('/presupuestos/' . $item->presupuesto_id . '/factura'),
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
            ->orderBy('tiempo_reparacion', 'asc')
            ->get();

        // 🎨 Preparar eventos para el calendario
        $eventos = $resultados->map(function ($item) {
            // 🟢 Color base por estado / prioridad
            $color = match (true) {
                $item->prioridad === 'premium' => '#F6C90E',   // dorado
                $item->prioridad === 'urgente' => '#E74C3C',   // rojo suave elegante
                $item->estado === 'pendiente'  => '#FFA552',   // naranja suave
                $item->estado === 'en proceso' => '#4A90E2',   // azul profesional
                $item->estado === 'completada' => '#00C49A',   // verde menta
                default => '#E0E0E0',                         // gris neutro
            };

            // 💎 Premium → dorado brillante
            if ($item->prioridad === 'premium') {
                $color = '#FFD700';
            }

            // 🔥 Urgente → rojo agradable
            elseif ($item->prioridad === 'urgente') {
                $color = '#E74C3C';
            }

            // 🧾 Título con cliente + bici
            $titulo = "<b>{$item->bike->user->name}</b><br>{$item->bike->marca} - {$item->bike->nombre}";

            // 🛠️ Añadir texto si hay problema
            if (!empty($item->descripcion_problema) && strtolower(trim($item->descripcion_problema)) !== 'nada') {
                $titulo .= "<br><small style='color:#ffffff; font-weight:600;'>🛠️ Problema para terminar</small>";
            }

            // 🔧 Mostrar estado (pendiente o en proceso)
            if ($item->estado === 'pendiente') {
                $titulo .= "<br><small style='color:#ffffff; font-weight:600;'>⏳ Pendiente</small>";
            } elseif ($item->estado === 'en proceso') {
                $titulo .= "<br><small style='color:#ffffff; font-weight:600;'>🔧 En proceso</small>";
            }

            return [
                'title' => $titulo,
                'start' => $item->fecha_asignada,
                'url'   => route('appointments.show', $item->id),
                'color' => $color,
            ];
        });

        return view('appointments.calendario_asignado', ['eventos' => $eventos]);
    }
}
