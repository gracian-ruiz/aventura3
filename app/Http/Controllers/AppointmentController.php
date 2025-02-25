<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Bike;
use Illuminate\Http\Request;
use App\Models\Component;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
    
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
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bike_id' => 'required|exists:bikes,id',
            'componente_id' => 'nullable|exists:components,id',
            'prioridad' => 'required|in:normal,urgente',
            'descripcion_problema' => 'nullable|string',
            'estimacion_reparacion' => 'nullable|string',
            'tiempo_estimado' => 'required|integer|min:1',
        ]);
    
        $fecha_asignada = $this->calcularFechaAsignada($request->tiempo_estimado);
    
        $appointment = Appointment::create([
            'bike_id' => $request->bike_id,
            'componente_id' => $request->componente_id,
            'prioridad' => $request->prioridad,
            'descripcion_problema' => $request->descripcion_problema,
            'estimacion_reparacion' => $request->estimacion_reparacion,
            'tiempo_estimado' => $request->tiempo_estimado,
            'estado' => 'pendiente',
            'fecha_asignada' => $fecha_asignada,
        ]);
    
        return redirect()->route('appointments.index')->with('success', 'Cita registrada correctamente.');
    }
    
    
        
    /**
     * Calcula la fecha en la que se podrá realizar la reparación
     */
    private function calcularFechaAsignada($tiempo_estimado, $inicioDesde = null)
    {
        $horarios_laborales = [
            'lunes'     => [['9:30', '14:00'], ['17:00', '20:00']],
            'martes'    => [['9:30', '14:00'], ['17:00', '20:00']],
            'miércoles' => [['9:30', '14:00'], ['17:00', '20:00']],
            'jueves'    => [['9:30', '14:00'], ['17:00', '20:00']],
            'viernes'   => [['9:30', '14:00'], ['17:00', '20:00']],
            'sábado'    => [['9:30', '14:00']], // Solo mañana
        ];
    
        $fecha_actual = $inicioDesde ? \Carbon\Carbon::parse($inicioDesde) : now();
        $intentos = 0;
        $max_intentos = 100; // Limitar bucle para evitar bloqueo
    
        while ($intentos < $max_intentos) {
            $intentos++;
    
            $dia_semana = strtolower($fecha_actual->isoFormat('dddd'));
    
            // Si es domingo o no hay horario, saltamos al siguiente día
            if (!isset($horarios_laborales[$dia_semana])) {
                $fecha_actual->addDay();
                continue;
            }
    
            foreach ($horarios_laborales[$dia_semana] as $horario) {
                [$hora_inicio, $hora_fin] = $horario;
    
                $inicio_minutos = \Carbon\Carbon::parse($fecha_actual->toDateString() . ' ' . $hora_inicio)->diffInMinutes($fecha_actual->copy()->startOfDay());
                $fin_minutos = \Carbon\Carbon::parse($fecha_actual->toDateString() . ' ' . $hora_fin)->diffInMinutes($fecha_actual->copy()->startOfDay());
    
                $minutos_ocupados = Appointment::whereDate('fecha_asignada', $fecha_actual->toDateString())->sum('tiempo_estimado');
    
                // Verifica si hay espacio en este bloque de horario
                if (($minutos_ocupados + $tiempo_estimado) <= ($fin_minutos - $inicio_minutos)) {
                    return $fecha_actual->toDateString();
                }
            }
    
            // Avanza al siguiente día hábil
            $fecha_actual->addDay();
        }
    
        return $fecha_actual->toDateString(); // Asigna la primera fecha disponible si no encontró antes
    }
    
    
    
    private function recalcularCitasPendientes()
    {
        $appointments = Appointment::where('estado', 'pendiente')
            ->orderByRaw("CASE WHEN prioridad = 'urgente' THEN 1 ELSE 2 END")
            ->orderBy('created_at', 'asc')
            ->get();
    
        $fecha_actual = now(); // Iniciamos en la fecha actual
    
        foreach ($appointments as $appointment) {
            $appointment->fecha_asignada = $this->calcularFechaAsignada($appointment->tiempo_estimado, $fecha_actual);
            $appointment->save();
            $fecha_actual = \Carbon\Carbon::parse($appointment->fecha_asignada)->addMinutes($appointment->tiempo_estimado);
        }
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
}
