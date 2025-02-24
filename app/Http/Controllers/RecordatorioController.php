<?php

namespace App\Http\Controllers;

use App\Models\AvisoEnviado;
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
        // Obtener información de la bicicleta y del componente
        $bike = $revision->bike;
        $componente = $revision->componente;
        $user = $bike->user; // Obtener el dueño de la bicicleta
    
        // Verificar que existan datos
        if (!$bike || !$componente) {
            return;
        }
    
        // Configuración de Twilio desde .env
        $sid    = Config::get('services.twilio.sid');
        $token  = Config::get('services.twilio.token');
        $twilio = new Client($sid, $token);
    
        // 📩 Mensaje
        $mensaje = "🚴 ¡Hola {$user->name}! Recuerda que tienes una revisión programada para el {$revision->proxima_revision}.\n\n"
                 . "🔧 Componente a revisar: *{$componente->nombre}*\n"
                 . "🚲 Bicicleta: *{$bike->nombre}* ({$bike->marca} - {$bike->anio_modelo})\n\n"
                 . "¡No olvides acudir a tu revisión! 📆";       
    
        // Enviar el mensaje por WhatsApp
        $message = $twilio->messages->create(
            "whatsapp:+34{$user->telefono}", // ⚠ Aquí debes cambiarlo por el número del usuario
            [
                "from" => Config::get('services.twilio.whatsapp_from'),
                "body" => $mensaje
            ]
        );

        // Guardar en la base de datos
        AvisoEnviado::create([
            'user_id' => $user->id,
            'bike_id' => $bike->id,
            'revision_id' => $revision->id,
            'componente_id' => $componente->id,
            'telefono' => $user->telefono,
            'mensaje' => $mensaje,
            'enviado_en' => now(),
        ]);
    
        return $message->sid;
    }    
}
