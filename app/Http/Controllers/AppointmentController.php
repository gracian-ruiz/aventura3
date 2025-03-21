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
                WHEN tiempo_estimado < 30 THEN 2  -- Luego las de menos de 30 minutos
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
        return view('appointments.confirm', compact('appointment'));
    }

    public function finalizeCompletion(Request $request, Appointment $appointment)
    {
        // Cambia el estado a "completada" solo cuando el usuario lo confirme
        $appointment->update(['estado' => 'completada']);

        // Generar revisiones para cada componente seleccionado en la cita
        foreach ($appointment->componentes as $componente) {
            $appointment->bike->revisions()->create([
                'componente_id' => $componente->id,
                'fecha_revision' => now(),
                'descripcion' => "Revisión de " . $componente->nombre,
                'fecha_proxima_revision' => now()->addDays(30),
            ]);
        }

        return redirect()->route('appointments.index')->with('success', 'Cita completada y revisiones generadas.');
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
        // Obtener el componente asociado


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

        // Marcar la cita como completada solo después de confirmar las revisiones
        $appointment->update(['estado' => 'completada']);

        return redirect()->route('appointments.index')->with('success', '✅ Cita completada y revisiones generadas correctamente.');
    }


    public function create()
    {
        $bikes = Bike::all();
        $componentes = Component::all(); // Agregamos los componentes

        return view('appointments.create', compact('bikes', 'componentes'));
    }

    public function update(UpdateAppointmentRequest $request, Appointment $appointment)
    {
        dd("aqui");
        // Actualizar los datos de la cita
        $appointment->update([
            'descripcion_problema' => $request->descripcion_problema,
            'tiempo_estimado' => $request->tiempo_estimado,
        ]);

        // Sincronizar los componentes seleccionados en la tabla intermedia
        $appointment->componentes()->sync($request->componentes);

        return redirect()->route('appointments.index')->with('success', '✅ Cita actualizada correctamente.');
    }

    public function updatedos(Request $request, Appointment $appointment)
    {
        // Validar los datos recibidos
        $request->validate([
            'descripcion_problema' => 'required|string|max:255',
            'tiempo_estimado' => 'required|integer|min:1',
            'componentes' => 'nullable|array', // Permitir que sea opcional
            'componentes.*' => 'exists:components,id', // Validar que los IDs existen en la tabla
        ]);

        // Actualizar los datos de la cita
        $appointment->update([
            'descripcion_problema' => $request->descripcion_problema,
            'tiempo_estimado' => $request->tiempo_estimado,
        ]);

        // Sincronizar los componentes seleccionados en la tabla intermedia
        if ($request->has('componentes')) {
            $appointment->componentes()->sync($request->componentes);
        } else {
            $appointment->componentes()->detach(); // Si no se selecciona ninguno, se eliminan
        }

        return redirect()->route('appointments.index')->with('success', '✅ Cita actualizada correctamente.');
    }




    public function edit(Appointment $appointment)
    {
        $bikes = Bike::all();
        $componentes = Component::all();

        return view('appointments.edit', compact('appointment', 'bikes', 'componentes'));
    }




    public function updateEstado(Request $request, Appointment $appointment)
    {
        $nuevoEstado = $request->input('estado');

        if (!in_array($nuevoEstado, ['pendiente', 'en proceso', 'completada'])) {
            return redirect()->back()->with('error', 'Estado no válido.');
        }

        $appointment->update(['estado' => $nuevoEstado]);

        // Si el estado es 'completada', redirigir a la creación de revisión
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


    public function historic()
    {
        $completedAppointments = Appointment::where('estado', 'completada')
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        return view('appointments.historic', compact('completedAppointments'));
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
    
    
}
