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
                WHEN estado = 'en proceso' THEN 0  -- Citas en proceso primero
                WHEN prioridad = 'urgente' THEN 1  -- Luego urgentes
                WHEN tiempo_estimado < 31 THEN 2  -- Luego las de menos de 30 minutos
                ELSE 3 
            END
        ")
            ->orderBy('fecha_asignada', 'asc')
            ->orderBy('created_at', 'asc')
            ->paginate(8);


        return view('appointments.index', compact('appointments', 'search', 'estado'));
    }

    public function confirmCompletion(Appointment $appointment)
    {
        $userId = auth()->id(); // ID del usuario actual
    
        // Si la cita no tiene mecánico asignado, se le asigna el actual
        if (!$appointment->usuario_taller_id) {
            $appointment->usuario_taller_id = $userId;
            $appointment->save();
        } elseif ($appointment->usuario_taller_id !== $userId) {
            // Si otro mecánico ya la está trabajando, bloqueamos la acción
            return redirect()->route('appointments.index')->with('error', '⚠️ Esta cita ya está siendo trabajada por otro mecánico.');
        }
    
        // Obtener los componentes de la cita
        $data = DB::table('appointment_component')
            ->join('appointments', 'appointment_component.appointment_id', '=', 'appointments.id')
            ->join('components', 'appointment_component.componente_id', '=', 'components.id')
            ->where('appointment_component.appointment_id', $appointment->id)
            ->select(
                'appointment_component.*',
                'appointments.*',
                'components.*'
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
        // Validar que al menos una revisión se ha seleccionado
        $request->validate([
            'revisiones' => 'required|array',
            'revisiones.*' => 'exists:components,id',
            'descripcion_revisiones.*' => 'required|string',
            'proxima_revision.*' => 'nullable|date',
            'tipo_fecha.*' => 'required|in:fija,opcional',
        ]);

        // Crear revisiones para los componentes seleccionados
        foreach ($request->revisiones as $componente_id) {
            $descripcion = $request->descripcion_revisiones[$componente_id] ?? 'Sin descripción';
            $componente = Component::find($componente_id);

            // Determinar la fecha de la próxima revisión
            if ($request->tipo_fecha[$componente_id] === 'fija') {
                $dias_a_sumar = $componente ? $componente->fecha_revision : 30; // Si no tiene, usar 30 días por defecto
                $fecha_proxima = now()->addDays($dias_a_sumar);
            } else {
                // Si es opcional, se usa la fecha proporcionada
                $fecha_proxima = $request->proxima_revision[$componente_id]
                    ? Carbon::parse($request->proxima_revision[$componente_id])
                    : now()->addDays(30); // Fallback en caso de error
            }

            // Crear la revisión asociada a la bicicleta
            $appointment->bike->revisions()->create([
                'componente_id' => $componente_id,
                'fecha_revision' => now(),
                'descripcion' => $descripcion,
                'proxima_revision' => $fecha_proxima,
            ]);
        }

        // Marcar la cita como completada y registrar quién la realizó
        $appointment->update([
            'estado' => 'completada',
            'usuario_taller_id' => auth()->id(), // Guardar el usuario logueado
        ]);

        return redirect()->route('appointments.index')->with('success', '✅ Cita completada y revisiones generadas correctamente.');
    }



    public function create()
    {
        $bikes = Bike::all();
        $componentes = Component::all(); // Agregamos los componentes

        return view('appointments.create', compact('bikes', 'componentes'));
    }

    public function updatedos(Request $request, Appointment $appointment)
    {
        // Validar los datos recibidos
        $request->validate([
            'descripcion_problema' => 'required|string|max:255',
            'tiempo_estimado' => 'required|integer|min:1',
            'componentes' => 'nullable|array',
            'componentes.*' => 'exists:components,id',
            'prioridad' => 'required|in:normal,urgente',
            'estado' => 'required|in:pendiente,en proceso,completada,cancelada', // Validación del estado
        ]);

        // Actualizar los datos de la cita
        $appointment->update([
            'descripcion_problema' => $request->descripcion_problema,
            'prioridad' => $request->prioridad,
            'estado' => $request->estado, // Se añade la actualización del estado
        ]);

        // Verificar y sincronizar componentes con texto
        if ($request->has('componentes')) {
            $componentes = array_values($request->componentes);
            $precios = array_values($request->precio);
            $horas_trabajo = array_values($request->horas_trabajo);
            $textos = array_values($request->textos);
            $datosComponentes = [];

            // Inicializar la variable para el total de las horas de trabajo
            $totalHoras = 0;

            foreach ($componentes as $index => $componenteId) {
                $precio = intval($precios[$index] ?? 0);
                $horas = intval($horas_trabajo[$index] ?? 0);
                $texto = $textos[$index] ?? ''; // Si es null, poner una cadena vacía

                // Los precios son independientes, no necesitamos multiplicarlos por las horas.
                $total_precio = $precio; // Ahora el precio total es simplemente el precio del componente.

                // Sumar el tiempo de trabajo del componente
                $totalHoras += $horas;

                // Asegurarse de que el total_precio esté dentro del rango permitido
                if ($total_precio > PHP_INT_MAX) {
                    // Si el valor excede el rango, puedes establecerlo en el valor máximo permitido
                    $total_precio = PHP_INT_MAX;
                }

                $datosComponentes[$componenteId] = [
                    'total_precio' => $total_precio,
                    'horas_trabajo' => $horas,
                    'texto' => $texto
                ];
            }

            // Sincronizar los componentes con los datos calculados
            $appointment->componentes()->sync($datosComponentes);

            // Actualizar el tiempo estimado total de la cita sumando las horas de todos los componentes
            $appointment->update(['tiempo_estimado' => $totalHoras]);
        } else {
            // Si no se pasan componentes, eliminarlos
            $appointment->componentes()->detach();
            // Si no hay componentes, también actualizamos el tiempo estimado a 0 o algún valor predeterminado
            $appointment->update(['tiempo_estimado' => 0]);
        }

        return redirect()->route('appointments.index')->with('success', '✅ Cita actualizada correctamente.');
    }










    public function edit(Appointment $appointment)
    {
        $bikes = Bike::all();
        $componentes = Component::all();

        // Obtener los componentes asociados a la cita
        $appointment_items = DB::table('appointment_component')
            ->join('components', 'appointment_component.componente_id', '=', 'components.id')
            ->where('appointment_component.appointment_id', $appointment->id)
            ->select(
                'appointment_component.componente_id as componente_id',
                'components.nombre as componente_nombre',
                'appointment_component.horas_trabajo',
                'appointment_component.total_precio',
                'appointment_component.texto'
            )
            ->get();

        //dd($componentes);

        return view('appointments.edit', compact('appointment', 'bikes', 'componentes', 'appointment_items'));
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
        Appointment::where('estado', 'pendiente')->orWhere('estado', 'en proceso')->update(['fecha_asignada' => null]);

        $appointments = Appointment::whereIn('estado', ['pendiente', 'en proceso'])
            ->orderByRaw("
            CASE 
                WHEN prioridad = 'urgente' THEN 1 
                WHEN tiempo_estimado < 30 THEN 2 
                ELSE 3 
            END
        ")
            ->orderBy('created_at', 'asc')
            ->get();

        // Fecha actual y hora actual
        $fecha_actual = Carbon::today();
        $ahora = Carbon::now();

        // Horario de cierre (20:00)
        $hora_cierre = $fecha_actual->copy()->setTime(20, 0);

        // Si ya cerró la tienda, empezamos desde el siguiente día laboral
        if ($ahora->greaterThanOrEqualTo($hora_cierre)) {
            do {
                $fecha_actual->addDay();
                $dia_semana = strtolower($fecha_actual->format('l'));
            } while ($dia_semana === 'sunday' || !isset($horas_laborales[$dia_semana])); // Evitar domingos y días sin horario laboral
        }

        $agenda = [];

        foreach ($appointments as $appointment) {
            $tiempo_estimado = $appointment->tiempo_estimado;

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

                // Verificar si hay espacio en la fecha actual
                if ($agenda[$fecha_actual->toDateString()] + $tiempo_estimado <= $horas_laborales[$dia_semana]) {
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
                'appointment_component.*',
                'appointments.*',
                'components.*'
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
        ]);
    
        $usuarioTallerId = auth()->id(); // Obtener el ID del usuario autenticado
    
        // Actualizar el estado de los componentes de la cita
        foreach ($request->componentes as $component) {
            $checked = isset($component['checked']) ? true : false; // Establecer como true si se envió 'checked'
    
            DB::table('appointment_component')
                ->where('appointment_id', $appointment->id)
                ->where('componente_id', $component['id'])
                ->update([
                    'checked' => $checked,
                    'usuario_taller_id' => $checked ? $usuarioTallerId : null // Solo actualizar si está marcado
                ]);
        }
    
        // Redirigir con un mensaje de éxito
        return redirect()->route('appointments.index')->with('success', 'Reparación actualizada exitosamente.');
    }
    
    
    
    

}
