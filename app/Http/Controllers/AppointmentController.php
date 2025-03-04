<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Bike;
use Illuminate\Http\Request;
use App\Models\Component;
use Carbon\Carbon;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $estado = $request->input('estado', 'pendiente'); // Estado seleccionado, por defecto 'pendiente'
    
        // Recalcular siempre antes de mostrar la vista
        $this->recalcularFechasAsignadas();
    
        $appointments = Appointment::with('bike.user', 'componente')
            ->whereIn('estado', ['pendiente', 'en proceso']) // Incluir pendientes y en proceso
            ->when($search, function ($query, $search) {
                $query->whereHas('bike', function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%");
                })
                ->orWhereHas('bike.user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('componente', function ($q) use ($search) {
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
            ->orderBy('fecha_asignada', 'asc') // Respetar la fecha asignada
            ->orderBy('created_at', 'asc') // Si hay empate, ordenar por creación
            ->paginate(8);
    
        return view('appointments.index', compact('appointments', 'search', 'estado'));
    }


    public function create()
    {
        $bikes = Bike::all();
        $componentes = Component::all(); // Agregamos los componentes

        return view('appointments.create', compact('bikes', 'componentes'));
    }

    public function update(UpdateAppointmentRequest $request, Appointment $appointment)
    {
        $appointment->update($request->validated());
    
        return redirect()->route('appointments.index')->with('success', '✅ Cita actualizada correctamente.');
    }

    public function edit(Appointment $appointment)
    {
        $bikes = Bike::all();
        $componentes = Component::all();
        return view('appointments.edit', compact('appointment', 'bikes', 'componentes'));
    }



    public function updateEstado(Appointment $appointment, Request $request)
    {
        $nuevoEstado = $request->input('estado');

        if (!in_array($nuevoEstado, ['pendiente', 'en proceso', 'completada'])) {
            return redirect()->back()->with('error', 'Estado no válido.');
        }

        $appointment->update(['estado' => $nuevoEstado]);

        if ($nuevoEstado === 'completada') {
            return redirect()->route('bikes.revisions.create', $appointment->bike_id)
                ->with('success', 'Cita convertida en revisión.');
        }

        return redirect()->route('appointments.index')->with('success', 'Estado de la cita actualizado.');
    }


    public function historico(Request $request)
    {
        $search = $request->input('search');

        $completedAppointments = Appointment::with('bike.user', 'componente')
            ->where('estado', 'completada')
            ->when($search, function ($query, $search) {
                $query->whereHas('bike', function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%");
                })
                    ->orWhereHas('bike.user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('componente', function ($q) use ($search) {
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
        Appointment::create([
            'bike_id' => $request->bike_id,
            'componente_id' => $request->componente_id,
            'prioridad' => $request->prioridad,
            'descripcion_problema' => $request->descripcion_problema,
            'estimacion_reparacion' => $request->estimacion_reparacion,
            'tiempo_estimado' => $request->tiempo_estimado,
            'estado' => 'pendiente',
        ]);
    
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
}
