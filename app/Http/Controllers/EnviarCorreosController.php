<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Models\Appointment;
use App\Mail\PresupuestoMail;
use App\Mail\CitaCompletadaMail;
use App\Mail\RecordatorioRevisionMail;
use App\Models\Revision;
use Illuminate\Support\Facades\Log;


class EnviarCorreosController extends Controller
{
    public function enviarPresupuestoCorreo($id)
    {
        // Obtener el presupuesto
        // App\Http\Controllers\EnviarCorreosController.php

        $presupuesto = DB::table('appointments')
            ->join('bikes', 'appointments.bike_id', '=', 'bikes.id')
            ->join('users', 'bikes.user_id', '=', 'users.id')
            ->where('appointments.id', $id)
            ->select(
                'appointments.*',
                'bikes.nombre as bicicleta_nombre',
                'bikes.marca as marca',            // <-- AÑADE ESTO
                'users.name as usuario_nombre',
                'users.email as usuario_email'
            )
            ->first();


        if (!$presupuesto) {
            return redirect()->back()->with('error', 'Presupuesto no encontrado');
        }

        // Obtener los items del presupuesto
        $items = DB::table('appointment_component')
            ->join('components', 'appointment_component.componente_id', '=', 'components.id')
            ->where('appointment_component.appointment_id', $id)
            ->select('appointment_component.*', 'components.nombre as componente_nombre')
            ->get();

        // Enviar correo al email del usuario
        Mail::to($presupuesto->usuario_email)
            ->send(new PresupuestoMail($presupuesto, $items));

        return redirect()->back()->with('success', '📧 Presupuesto enviado correctamente.');
    }

    public function enviarRecordatorios(): int
    {
        $enviados = 0;
        $hoy = now()->toDateString(); // Europe/Madrid si lo tienes en config

        // 1) Filtra en BD: solo candidatas de HOY
        $ids = DB::table('revisions as r')
            ->join('components as c', 'r.componente_id', '=', 'c.id')
            ->where('r.enviado', false)
            ->whereNotNull('r.proxima_revision')
            ->whereBetween('c.fecha_preaviso', [0, 60]) // 0..60 días de preaviso
            ->where(function ($q) {
                $q->whereNull('r.descripcion')
                    ->orWhereRaw('LOWER(r.descripcion) NOT LIKE ?', ['%material%']); // excluir "material"
            })
            // Día de aviso = HOY  ⇔  DATEDIFF(proxima_revision, HOY) = fecha_preaviso
            ->whereRaw('DATEDIFF(r.proxima_revision, ?) = c.fecha_preaviso', [$hoy])
            ->pluck('r.id');

        if ($ids->isEmpty()) {
            return 0;
        }

        // 2) Carga modelos + relaciones SOLO de esos IDs
        $revisiones = \App\Models\Revision::with(['componente', 'bike.user'])
            ->whereIn('id', $ids)
            ->get();

        // 3) Envía y marca "enviado" de forma atómica
        foreach ($revisiones as $revision) {
            $email = data_get($revision, 'bike.user.email');
            if (!$email) continue;

            Mail::to($email)->send(new \App\Mail\RecordatorioRevisionMail($revision));

            $updated = \App\Models\Revision::whereKey($revision->id)
                ->where('enviado', false)
                ->update(['enviado' => true, 'updated_at' => now()]);

            if ($updated) {
                $enviados++;
            }
        }

        return $enviados;
    }
    public function enviarCompletado($id): void
    {
        $appointment = Appointment::with('bike.user')->findOrFail($id);

        $cliente   = $appointment->bike->user;
        $bicicleta = $appointment->bike;

        $mensaje = "✅ ¡Hola {$cliente->name}! Tu bicicleta {$bicicleta->nombre} ya está lista.\n";
        $mensaje .= "Puedes pasar a recogerla en nuestro horario habitual. ¡Gracias! 🚴\n\n";
        $mensaje .= "📞 Teléfono: {$cliente->telefono}\n";
        $mensaje .= "👤 Cliente: {$cliente->name}";

        Mail::to($cliente->email)->send(new \App\Mail\CitaCompletadaMail($mensaje));

    try {
        Mail::to($cliente->email)->send(new \App\Mail\CitaCompletadaMail($mensaje));
    } catch (\Exception $e) {
        Log::error("Error al enviar correo de cita completada: ".$e->getMessage());
    }
    }
}
