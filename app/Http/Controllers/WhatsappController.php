<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\Rest\Client;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\AvisoEnviado;

class WhatsAppController extends Controller
{
    public function enviarPresupuestoWhatsApp($clienteId, $presupuestoId)
    {
        // Buscar el cliente
        $cliente = DB::table('users')->where('id', $clienteId)->first();
        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        // Buscar el presupuesto y la bicicleta
        $presupuesto = DB::table('appointments')
            ->join('bikes', 'appointments.bike_id', '=', 'bikes.id')
            ->join('users', 'bikes.user_id', '=', 'users.id')
            ->where('appointments.id', $presupuestoId)
            ->select(
                'appointments.*',
                'bikes.nombre as bicicleta_nombre',
                'users.name as usuario_nombre',
                'users.telefono as usuario_telefono'
            )
            ->first();

        $presupuestoUrl = url("confirmacion/presupuesto/{$presupuestoId}?token={$presupuesto->token_presupuesto}");

        if (!$presupuesto) {
            return response()->json(['error' => 'Presupuesto no encontrado'], 404);
        }

        // 1. GENERAR Y GUARDAR EL PDF
        $pdfPath = $this->generarPDF($presupuestoId);

        // 2. ENVIAR POR WHATSAPP
        if (!empty($cliente->telefono)) {
            // 2. ENVIAR POR WHATSAPP
            if (!empty($cliente->telefono)) {
                $mensaje = "📄 ¡Hola {$cliente->name}! Te escribo de Aventura Bike, te envío el presupuesto para arreglar tu bicicleta '{$presupuesto->bicicleta_nombre}'.\n\n"
                    . "📎 Adjuntamos el PDF con los detalles.\n\n" // Doble salto de línea aquí
                    . "🔗 Puedes confirmar el presupuesto pinchando aquí: si no estás de acuerdo dime que quieres que hagamos y te mando nuevo presupuesto. Gracias: {$presupuestoUrl}";

                $this->enviarMensajeWhatsApp($cliente->telefono, $mensaje, $pdfPath, $presupuestoId);
            }

        }

        return back()->with('success', '📩 Presupuesto enviado por WhatsApp.');
    }

    private function generarPDF($presupuestoId)
    {
        // Obtener datos del presupuesto
        $presupuesto = DB::table('appointments')
            ->join('bikes', 'appointments.bike_id', '=', 'bikes.id')
            ->join('users', 'bikes.user_id', '=', 'users.id')
            ->where('appointments.id', $presupuestoId)
            ->select('appointments.*', 'bikes.nombre as bicicleta_nombre','bikes.marca as marca', 'users.name as usuario_nombre')
            ->first();
            

        $items = DB::table('appointment_component')
            ->join('components', 'appointment_component.componente_id', '=', 'components.id')
            ->where('appointment_component.appointment_id', $presupuestoId)
            ->select('appointment_component.*', 'components.nombre as componente_nombre')
            ->get();

        // Nombre del archivo PDF basado en el ID del presupuesto
        $nombreArchivo = "presupuesto_{$presupuestoId}.pdf";
        $rutaAlmacenamiento = "public/presupuestos/$nombreArchivo";

        // Generar y guardar el PDF
        $pdf = Pdf::loadView('pdf.presupuesto2', compact('presupuesto', 'items'));
        Storage::put($rutaAlmacenamiento, $pdf->output());

        return storage_path("app/$rutaAlmacenamiento");
    }

    private function enviarMensajeWhatsApp($telefono, $mensaje, $pdfPath, $presupuestoId)
    {
        try {
            // Configurar Twilio
            $twilio = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));

            // Obtener URL del PDF
            $pdfUrl = url("storage/presupuestos/" . basename($pdfPath));

            // Enviar mensaje con PDF adjunto
            $twilio->messages->create(
                "whatsapp:+34$telefono",
                [
                    "from" => env('TWILIO_WHATSAPP_FROM'),
                    "body" => $mensaje,
                    "mediaUrl" => [$pdfUrl]
                ]
            );


                DB::table('appointments')
                    ->where('id', $presupuestoId)
                    ->update(['presupuesto_enviado' => true]);

        } catch (\Exception $e) {
            Log::error("Error al enviar mensaje de WhatsApp: " . $e->getMessage());
        }
    }
}
