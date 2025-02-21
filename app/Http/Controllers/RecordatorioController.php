<?php

namespace App\Http\Controllers;

use App\Models\Revision;
use App\Models\Component;
use Illuminate\Http\Request;
use Twilio\Rest\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

class RecordatorioController extends Controller
{
    public function enviarRecordatorios()
    {
        // Obtener revisiones con su componente relacionado
        $revisiones = Revision::with('componente')->get();

        foreach ($revisiones as $revision) {
            if ($revision->proxima_revision && $revision->componente) {
                $fechaProximaRevision = Carbon::parse($revision->proxima_revision);
                $diasPreaviso = $revision->componente->fecha_preaviso; // Días antes para el recordatorio
                $fechaRecordatorio = $fechaProximaRevision->subDays($diasPreaviso);

                // Si hoy es la fecha del recordatorio, enviar WhatsApp
                if (Carbon::today()->equalTo($fechaRecordatorio)) {
                    $this->enviarMensajeWhatsApp($revision);
                }
            }
        }

        return response()->json([
            'message' => 'Los recordatorios de revisión se han enviado correctamente.'
        ]);
    }

    private function enviarMensajeWhatsApp($revision)
    {
        try {
            // Obtener credenciales de Twilio desde el .env
            $sid    = Config::get('services.twilio.sid');
            $token  = Config::get('services.twilio.token');
            $twilio = new Client($sid, $token);
    
            // Verificar que el usuario tiene teléfono
            if (!$revision->bike->user->telefono) {
                throw new \Exception("El usuario no tiene número de teléfono registrado.");
            }
    
            // Construir número de WhatsApp
            $numeroDestino = "whatsapp:+34" . $revision->bike->user->telefono;
    
            // Mensaje
            $mensaje = "📅 Recuerda que tienes una revisión programada para el {$revision->proxima_revision}. ¡No olvides pasar a realizarla! 🚴";
    
            // Enviar mensaje de WhatsApp
            $message = $twilio->messages->create(
                $numeroDestino, // 📩 Número del cliente
                [
                    "from" => Config::get('services.twilio.whatsapp_from'),
                    "body" => $mensaje
                ]
            );
    
            return response()->json([
                'message' => 'Mensaje enviado correctamente',
                'twilio_response' => $message->sid
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al enviar mensaje',
                'details' => $e->getMessage()
            ]);
        }
    }
    
}
