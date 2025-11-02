<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Bike;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    /**
     * Mostrar el perfil del cliente autenticado con sus bicicletas.
     */
    public function perfil()
    {
        $user = Auth::user();
        $bikes = $user->bikes; // ya funciona perfectamente
        return view('cliente.perfil', compact('user', 'bikes'));
    }


    /**
     * Mostrar las revisiones de una bicicleta específica del cliente autenticado.
     */
    public function revisiones($bike_id)
    {
        // Obtener la bicicleta que pertenece al cliente autenticado
        $bike = Bike::where('id', $bike_id)
            ->where('user_id', Auth::id()) // seguridad: solo sus propias bicis
            ->with('revisions')
            ->firstOrFail();

        $revisiones = $bike->revisions()->latest('fecha_revision')->get();

        return view('cliente.revisiones', compact('bike', 'revisiones'));
    }

    // 🔹 Mostrar historial (todas las reparaciones de su bicicleta)
    public function historial($bike_id)
    {
        $bike = Bike::where('id', $bike_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $historial = Appointment::with('componentes')
            ->where('bike_id', $bike->id)
            ->orderByDesc('fecha_asignada')
            ->get();

        return view('cliente.historial', compact('bike', 'historial'));
    }

    // 🔹 Mostrar reparación completa (igual que en showReparacion del taller)
    public function reparacionCompleta($appointment_id)
    {
        $appointment = Appointment::findOrFail($appointment_id);

        // Seguridad: el cliente solo puede ver sus propias bicis
        if ($appointment->bike->user_id !== Auth::id()) {
            abort(403, 'No tienes permiso para ver esta reparación.');
        }

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
                'appointments.horas_total',
                'appointments.precio_total',
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

        return view('cliente.reparacion_completa', compact('appointment', 'data'));
    }

    public function cita($bike_id)
    {
        $bike = Bike::where('id', $bike_id)
            ->where('user_id', Auth::id()) // seguridad: solo sus bicis
            ->firstOrFail();

        // Generar los próximos 14 días hábiles (lunes a sábado)
        $fechasDisponibles = [];
        $fecha = now();

        for ($i = 0; $i < 14; $i++) {
            $diaSemana = strtolower($fecha->format('l'));
            if ($diaSemana !== 'sunday') {
                $fechasDisponibles[] = $fecha->format('Y-m-d');
            }
            $fecha->addDay();
        }

        return view('cliente.cita', compact('bike', 'fechasDisponibles'));
    }

    public function guardarCita(Request $request)
    {
        $request->validate([
            'bike_id' => 'required|exists:bikes,id',
            'fecha' => 'required|date|after_or_equal:today',
            'descripcion_problema' => 'required|string|max:1000',
        ]);

        Appointment::create([
            'bike_id' => $request->bike_id,
            'user_id' => Auth::id(),
            'fecha_asignada' => $request->fecha,
            'descripcion_problema' => $request->descripcion_problema,
            'estado' => 'presupuesto',
            'prioridad' => 'normal',
            'estimacion_reparacion' => 0,
            'calendario' => $request->fecha,
            'fecha_fija' => true,
        ]);

        return redirect()->route('cliente.perfil')->with('success', '✅ Cita creada correctamente.');
    }
}
