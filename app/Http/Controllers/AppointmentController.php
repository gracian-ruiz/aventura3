<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Bike;
use Illuminate\Http\Request;
use App\Models\Component;
use Carbon\Carbon;


class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        // Recalcular siempre antes de mostrar la vista
        $this->recalcularFechasAsignadas();


        $appointments = Appointment::with('bike.user', 'componente')
            ->where('estado', 'pendiente')
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
            ->orderByRaw("CASE WHEN prioridad = 'urgente' THEN 1 ELSE 2 END")
            ->orderBy('created_at', 'asc')
            ->paginate(8);

        return view('appointments.index', compact('appointments', 'search'));
    }



    public function create()
    {
        $bikes = Bike::all();
        $componentes = Component::all(); // Agregamos los componentes

        return view('appointments.create', compact('bikes', 'componentes'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'bike_id' => 'required|exists:bikes,id',
            'componente_id' => 'nullable|exists:components,id',
            'prioridad' => 'required|in:normal,urgente',
            'tiempo_estimado' => 'nullable|string',
        ]);

        $appointment->update($validated);

        return redirect()->route('appointments.index')->with('success', '✅ Cita actualizada correctamente.');
    }

    public function edit(Appointment $appointment)
    {
        $bikes = Bike::all();
        $componentes = Component::all();
        return view('appointments.edit', compact('appointment', 'bikes', 'componentes'));
    }



    public function updateEstado(Appointment $appointment)
    {
        // Marcar cita como completada y registrar la fecha de actualización
        $appointment->update([
            'estado' => 'completada',
            'updated_at' => now()
        ]);

        // Redirigir a la creación de la revisión
        return redirect()->route('bikes.revisions.create', $appointment->bike_id)
            ->with('success', 'Cita convertida en revisión.');
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bike_id' => 'required|exists:bikes,id',
            'componente_id' => 'nullable|exists:components,id',
            'prioridad' => 'required|in:normal,urgente',
            'descripcion_problema' => 'required|string',
            'estimacion_reparacion' => 'nullable|string',
            'tiempo_estimado' => 'required|integer|min:1',
        ]);

        Appointment::create([
            'bike_id' => $validated['bike_id'],
            'componente_id' => $validated['componente_id'],
            'prioridad' => $validated['prioridad'],
            'descripcion_problema' => $validated['descripcion_problema'],
            'estimacion_reparacion' => $validated['estimacion_reparacion'],
            'tiempo_estimado' => $validated['tiempo_estimado'],
            'estado' => 'pendiente',
        ]);

        // 🔥 Recalcula fechas automáticamente
        $this->recalcularFechasAsignadas();

        return redirect()->route('appointments.index')->with('success', 'Cita registrada correctamente.');
    }

    private function recalcularFechasAsignadas()
    {
        $horas_laborales = [
            'monday'    => 400,
            'tuesday'   => 400,
            'wednesday' => 400,
            'thursday'  => 400,
            'friday'    => 400,
            'saturday'  => 240,
        ];
    
        // Reiniciar fechas para recalcular desde cero
        Appointment::where('estado', 'pendiente')->update(['fecha_asignada' => null]);
    
        $appointments = Appointment::where('estado', 'pendiente')
            ->orderByRaw("CASE WHEN prioridad = 'urgente' THEN 1 ELSE 2 END")
            ->orderBy('created_at', 'asc')
            ->get();
    
        // Comienza siempre desde hoy al horario actual correcto
        $fecha_actual = Carbon::today();
        $ahora = Carbon::now();
    
        // Ajusta el tiempo disponible hoy según la hora actual
        $dia_semana = strtolower($fecha_actual->format('l'));
        $tiempo_disponible_hoy = 0;
    
        if ($dia_semana !== 'sunday' && isset($horas_laborales[$dia_semana])) {
            if ($ahora->lt($fecha_actual->copy()->setTime(9, 30))) {
                // Aún no abrió la tienda, tiempo completo
                $tiempo_disponible_hoy = $horas_laborales[$dia_semana];
            } elseif ($ahora->between($fecha_actual->copy()->setTime(9, 30), $fecha_actual->copy()->setTime(14, 0))) {
                // Estamos en la mañana
                $tiempo_disponible_hoy = $ahora->diffInMinutes($fecha_actual->copy()->setTime(14, 0));
                $tiempo_disponible_hoy += 180; // Más la tarde completa (17-20)
            } elseif ($ahora->between($fecha_actual->copy()->setTime(14, 0), $fecha_actual->copy()->setTime(17, 0))) {
                // Mediodía cerrado, solo la tarde disponible
                $tiempo_disponible_hoy = 180; // Tarde completa
            } elseif ($ahora->between($fecha_actual->copy()->setTime(17, 0), $fecha_actual->copy()->setTime(20, 0))) {
                // Estamos en la tarde
                $tiempo_disponible_hoy = $ahora->diffInMinutes($fecha_actual->copy()->setTime(20, 0));
            } else {
                // Ya cerró el día, no queda tiempo hoy
                $tiempo_disponible_hoy = 0;
            }
        }
    
        foreach ($appointments as $appointment) {
            $tiempo_estimado = $appointment->tiempo_estimado;
    
            while (true) {
                $dia_semana = strtolower($fecha_actual->format('l'));
    
                if ($dia_semana === 'sunday' || !isset($horas_laborales[$dia_semana])) {
                    $fecha_actual->addDay();
                    $tiempo_disponible_hoy = $horas_laborales[strtolower($fecha_actual->format('l'))] ?? 0;
                    continue;
                }
    
                if ($tiempo_disponible_hoy >= $tiempo_estimado) {
                    $appointment->fecha_asignada = $fecha_actual->toDateString();
                    $appointment->save();
    
                    $tiempo_disponible_hoy -= $tiempo_estimado;
                    break;
                } else {
                    $fecha_actual->addDay();
                    $tiempo_disponible_hoy = $horas_laborales[strtolower($fecha_actual->format('l'))] ?? 0;
                }
            }
        }
    }
    
}
