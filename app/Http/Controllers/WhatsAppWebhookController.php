<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    /**
     * Verificación del webhook por Meta (GET).
     * Meta llama a esta URL cuando configuras el webhook en el panel de Meta for Developers.
     */
    public function verify(Request $request)
    {
        $mode      = $request->query('hub_mode');
        $token     = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        if ($mode === 'subscribe' && $token === config('services.whatsapp.verify_token')) {
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        return response('Token de verificación no válido', 403);
    }

    /**
     * Recibe eventos/mensajes entrantes de Meta (POST).
     */
    public function receive(Request $request)
    {
        $payload = $request->all();

        Log::channel('stack')->info('WhatsApp webhook recibido', $payload);

        // Iterar sobre los mensajes entrantes
        $entries = $payload['entry'] ?? [];

        foreach ($entries as $entry) {
            $changes = $entry['changes'] ?? [];

            foreach ($changes as $change) {
                $value    = $change['value'] ?? [];
                $messages = $value['messages'] ?? [];

                foreach ($messages as $message) {
                    $from = $message['from'] ?? null; // número del remitente
                    $type = $message['type'] ?? null;

                    if ($type === 'text') {
                        $text = $message['text']['body'] ?? '';
                        Log::info("Mensaje de WhatsApp de {$from}: {$text}");

                        // Aquí puedes añadir lógica: guardar en BD, responder automáticamente, etc.
                    }
                }
            }
        }

        // Meta espera siempre HTTP 200
        return response()->json(['status' => 'ok'], 200);
    }
}
