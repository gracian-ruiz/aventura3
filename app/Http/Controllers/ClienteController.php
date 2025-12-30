<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Bike;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ClienteController extends Controller
{
    /**
     * Mostrar el perfil del cliente autenticado con sus bicicletas.
     */
    public function perfil()
    {
        $user = Auth::user();

        // 🔹 Cargar bicicletas del usuario con SOLO la última cita (por ID más reciente)
        $bikes = Bike::where('user_id', $user->id)
            ->with(['appointments' => function ($q) {
                $q->whereIn('estado', ['presupuesto', 'pendiente', 'en proceso', 'completada'])
                  ->orderByDesc('id') // Ordenar por ID descendente
                  ->limit(1); // Solo la última cita
            }])
            ->get();
            
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

        // 🔹 Verificar si el usuario es premium (ajusta el campo según tu tabla)
        $esPremium = Auth::user()->role === 'premium';

        // 🔹 Obtener la última fecha asignada en appointments
        $ultimaFecha = DB::table('appointments')->max('fecha_asignada');

        if ($ultimaFecha) {
            $ultimaFecha = \Carbon\Carbon::parse($ultimaFecha);
        } else {
            $ultimaFecha = now();
        }

        // 🔹 Determinar desde cuándo puede empezar a elegir
        $fechaInicio = $esPremium ? now() : $ultimaFecha->copy()->addDays(2);

        // 🔹 Generar las fechas disponibles (14 días para no premium, 30 días para premium)
        $fechasDisponibles = [];
        $fecha = $fechaInicio->copy();
        $limiteDias = $esPremium ? 30 : 14; // premium puede elegir más rango

        for ($i = 0; $i < $limiteDias; $i++) {
            $diaSemana = strtolower($fecha->format('l'));
            if ($diaSemana !== 'sunday') { // excluir domingos
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

        // 🔹 Determinar la prioridad según el tipo de usuario
        $user = Auth::user();
        $prioridad = ($user->role === 'premium') ? 'premium' : 'normal';
        // 🔸 Cambia "tipo" por el nombre real del campo en tu tabla users (ej: role, nivel, is_premium...)

        Appointment::create([
            'bike_id' => $request->bike_id,
            'user_id' => $user->id,
            'descripcion_cliente' => $request->descripcion_problema,
            'estado' => 'presupuesto',
            'prioridad' => $prioridad, // 👈 cambia según el tipo de usuario
            'estimacion_reparacion' => 0,
            'calendario' => $request->fecha,
            'fecha_fija' => true,
            'web' => true, // 🌐 Marca que viene desde la web (cliente)
        ]);

        $fechaElegida = \Carbon\Carbon::parse($request->fecha)->format('d/m/Y');
        return redirect()->route('cliente.perfil')->with('success', '✅ Cita creada correctamente para el día ' . $fechaElegida . '.');
    }

    public function verPresupuesto($appointment_id)
    {
        $appointment = Appointment::findOrFail($appointment_id);

        // Seguridad: el cliente solo puede ver sus propias bicis
        if ($appointment->bike->user_id !== Auth::id()) {
            abort(403, 'No tienes permiso para ver este presupuesto.');
        }

        // Obtener los componentes del presupuesto
        $data = DB::table('appointment_component')
            ->join('components', 'appointment_component.componente_id', '=', 'components.id')
            ->where('appointment_component.appointment_id', $appointment->id)
            ->select(
                'appointment_component.id',
                'appointment_component.texto',
                'appointment_component.total_precio',
                'appointment_component.descuento',
                'appointment_component.horas_trabajo',
                'components.nombre as componente_nombre'
            )
            ->get();

        return view('cliente.presupuesto', compact('appointment', 'data'));
    }

    public function aprobarPresupuesto($appointment_id)
    {
        $appointment = Appointment::findOrFail($appointment_id);

        // Seguridad: el cliente solo puede aprobar sus propias bicis
        if ($appointment->bike->user_id !== Auth::id()) {
            abort(403, 'No tienes permiso para aprobar este presupuesto.');
        }

        // Cambiar estado a "pendiente" para que entre en la cola del taller
        $appointment->update([
            'estado' => 'pendiente',
        ]);

        return redirect()->route('cliente.perfil')->with('success', '✅ Presupuesto aprobado. Tu bicicleta entrará en reparación próximamente.');
    }

    public function denegarPresupuesto(Request $request, $appointment_id)
    {
        $appointment = Appointment::findOrFail($appointment_id);

        // Seguridad: el cliente solo puede denegar sus propias bicis
        if ($appointment->bike->user_id !== Auth::id()) {
            abort(403, 'No tienes permiso para denegar este presupuesto.');
        }

        // Eliminar la cita si se deniega el presupuesto
        $appointment->delete();

        return redirect()->route('cliente.perfil')->with('success', 'Presupuesto denegado. La cita ha sido cancelada.');
    }

    public function descargarPresupuestoPDF($appointment_id)
    {
        $appointment = Appointment::findOrFail($appointment_id);

        // Seguridad: el cliente solo puede descargar sus propias bicis
        if ($appointment->bike->user_id !== Auth::id()) {
            abort(403, 'No tienes permiso para descargar este presupuesto.');
        }

        // Obtener los datos igual que en el presupuesto del taller
        $presupuesto = DB::table('appointments')
            ->join('bikes', 'appointments.bike_id', '=', 'bikes.id')
            ->join('users', 'bikes.user_id', '=', 'users.id')
            ->where('appointments.id', $appointment_id)
            ->select('appointments.*', 'bikes.nombre as bicicleta_nombre', 'bikes.marca as marca', 'users.name as usuario_nombre')
            ->first();

        $items = DB::table('appointment_component')
            ->join('components', 'appointment_component.componente_id', '=', 'components.id')
            ->where('appointment_component.appointment_id', $appointment_id)
            ->select('appointment_component.*', 'components.nombre as componente_nombre')
            ->get();

        if (!$presupuesto) {
            abort(404, 'Presupuesto no encontrado');
        }

        // Función para limpiar nombres de archivo
        $limpiarNombre = fn($texto) => preg_replace('/[^A-Za-z0-9_\-]/', '_', $texto);

        $usuarioLimpio = $limpiarNombre($presupuesto->usuario_nombre);
        $bicicletaLimpia = $limpiarNombre($presupuesto->bicicleta_nombre);
        $fecha = date('Y-m-d', strtotime($presupuesto->created_at));

        $nombreArchivo = "Presupuesto_{$usuarioLimpio}_{$bicicletaLimpia}_{$fecha}.pdf";

        $pdf = Pdf::loadView('pdf.presupuesto', compact('presupuesto', 'items'));

        return $pdf->download($nombreArchivo);
    }
}
