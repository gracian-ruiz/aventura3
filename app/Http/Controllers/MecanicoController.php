<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\User;
use App\Models\Bike;
use Illuminate\Http\Request;
use App\Models\Component;
use Carbon\Carbon;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use Illuminate\Support\Facades\DB;

class MecanicoController extends Controller
{
public function index(Request $request)
    {
        $userId = auth()->user()->id;
        $search = $request->input('search');
        $filtro = $request->input('filtro', 'todos'); // Por defecto 'todos'

        // 🔄 Recalcular antes de mostrar la vista
        $this->recalcularFechasAsignadas();

        // 🔹 Base de la consulta con relaciones
        $query = Appointment::with('bike.user', 'componentes')
            ->where(function ($query) use ($userId) {
                if ($userId == 1 || $userId == 14) {
                    // Muestra solo los que tienen mecánicos asignados
                    $query->whereNotNull('asignacion_taller')
                        ->where('asignacion_taller', '!=', '[]');
                } else {
                    // Solo los que contienen el ID del mecánico actual
                    $query->whereJsonContains('asignacion_taller', (string) $userId);
                }
            });

        // 🔹 Filtros personalizados
        switch ($filtro) {
            case 'proceso':
                $query->where('descripcion_problema', '=', null)
                    ->where('estado', 'en proceso');
                break;

            case 'sin-hacer':
                $query->where('estado', 'pendiente');
                break;

            case 'premium':
                $query->where('prioridad', 'premium')
                    ->where('estado', 'en proceso');
                break;

            case 'incidencia':
                $query->whereNotNull('descripcion_problema')
                    ->where('descripcion_problema', '!=', '')
                    ->whereIn('estado', ['en proceso', 'pendiente']);
                break;

            default: // 🔸 "Todos"
                $query->whereIn('estado', ['pendiente', 'en proceso'])
                    ->orderByRaw("
                        CASE
                            WHEN estado = 'en proceso' THEN 1
                            WHEN estado = 'pendiente' THEN 2
                            ELSE 3
                        END
                    ");
                break;
        }

        // 🔍 Buscador
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('bike', function ($q2) use ($search) {
                    $q2->where('nombre', 'like', "%{$search}%")
                        ->orWhere('marca', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($qq) use ($search) {
                            $qq->where('name', 'like', "%{$search}%");
                        });
                })
                ->orWhere('idprograma', 'like', "%{$search}%");
            });
        }

        // 🔹 Orden por prioridad y fecha (igual que appointments.index)
        $query->orderByRaw("
            CASE
                WHEN prioridad = 'premium' THEN 0
                WHEN prioridad = 'urgente' THEN 1
                WHEN prioridad = 'normal' THEN 2
                ELSE 3
            END
        ")->orderBy('fecha_asignada', 'asc');

        // 🔹 Paginar
        $appointments = $query->paginate(8)->appends([
            'search' => $search,
            'filtro' => $filtro,
        ]);

        // 🔹 Asignar los usuarios mecánicos correspondientes
        foreach ($appointments as $appointment) {
            $userIds = is_array($appointment->asignacion_taller)
                ? $appointment->asignacion_taller
                : json_decode($appointment->asignacion_taller, true);

            $userIds = is_array($userIds) ? $userIds : [];
            $appointment->usuarios_asignados = User::whereIn('id', $userIds)->get();
        }

        return view('mecanico.index', compact('appointments', 'search', 'filtro'));
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
        return view('mecanico.confirm', compact(
            'appointment',
            'data',
            'faltanComponentes',
            'mensaje',
            'telefono',
            'nombre'
        ));
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
        //app(RecordatorioController::class)->enviarMensajeFinalizacionCita($appointment);

        return redirect()->route('mecanico.index')->with('success', '✅ Cita completada y revisiones generadas correctamente.');
    }

    public function updatedos(Request $request, $id)
    {
        $request->validate([
            'bike_id' => 'required|exists:bikes,id',
            'componentes' => 'required|array',
            'horas_trabajo' => 'required|array',
            'precio' => 'required|array',
            'textos' => 'nullable|array',
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
                    'calendario' => $request->calendario
                ]);

            DB::commit();

            return redirect()->route('mecanico.index')->with('success', 'Presupuesto actualizado correctamente.');
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

        return view('mecanico.edit', compact('presupuesto', 'bikes', 'components', 'presupuesto_items', 'usuariosTaller'));
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

        return redirect()->route('mecanico.index')->with('success', 'Estado de la cita actualizado.');
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

        return view('mecanico.historico', compact('completedAppointments', 'search'));
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

        return view('mecanico.show', compact('appointment'));
    }

    public function showReparacion(Appointment $appointment)
    {
        // Obtener los componentes asociados a la cita con joins completos
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
                'bikes.marca as bike_marca',
                'users.id as user_id',
                'users.name as user_name',
                'users.email as user_email',
                'users.telefono as user_telefono'
            )
            ->get();

        // Pasamos todo a la vista
        return view('mecanico.reparacion', compact('appointment', 'data'));
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
            'idprograma' => 'nullable|string|max:1000',
        ]);

        $usuarioTallerId = auth()->id(); // ID del usuario autenticado
        $ajusteTiempo = 0; // acumulador de cambios

        // Actualizar estado de los componentes seleccionados
        foreach ($request->componentes as $component) {
            $checked = isset($component['checked']) ? true : false;

            // Obtener datos actuales del pivote
            $pivot = DB::table('appointment_component')
                ->where('appointment_id', $appointment->id)
                ->where('componente_id', $component['id'])
                ->first();

            if ($pivot) {
                // Si antes no estaba marcado y ahora sí → restamos horas
                if ($checked && !$pivot->checked) {
                    $ajusteTiempo -= (int) $pivot->horas_trabajo;
                }

                // Si antes estaba marcado y ahora se desmarca → sumamos horas
                if (!$checked && $pivot->checked) {
                    $ajusteTiempo += (int) $pivot->horas_trabajo;
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

        // Aplicar el ajuste al campo tiempo_reparacion
        if ($ajusteTiempo !== 0) {
            $appointment->tiempo_reparacion = max(0, $appointment->tiempo_reparacion + $ajusteTiempo);
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

        return redirect()->route('mecanico.index')->with('success', 'Reparación actualizada exitosamente.');
    }
}
