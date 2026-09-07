<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    private function normalizeWebhookErrors(mixed $errors): array
    {
        if (!is_array($errors)) {
            return [];
        }

        $normalized = [];

        foreach ($errors as $error) {
            if (is_array($error)) {
                $normalized[] = [
                    'code' => $error['code'] ?? null,
                    'title' => $error['title'] ?? null,
                    'message' => $error['message'] ?? null,
                    'error_data' => $error['error_data'] ?? null,
                    'details_json' => json_encode($error, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ];
                continue;
            }

            $normalized[] = [
                'message' => (string) $error,
            ];
        }

        return $normalized;
    }

    /**
     * Verificación del webhook por Meta (GET).
     * Meta llama a esta URL cuando configuras el webhook en el panel de Meta for Developers.
     */
    public function verify(Request $request)
    {
        $mode = $request->query('hub.mode', $request->query('hub_mode'));
        $token = $request->query('hub.verify_token', $request->query('hub_verify_token'));
        $challenge = $request->query('hub.challenge', $request->query('hub_challenge'));

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
        $entries = $payload['entry'] ?? [];

        Log::channel('stack')->info('WhatsApp webhook recibido', [
            'object' => $payload['object'] ?? null,
            'entry_count' => count($entries),
        ]);

        // Iterar sobre los mensajes entrantes y estados

        foreach ($entries as $entry) {
            $changes = $entry['changes'] ?? [];

            foreach ($changes as $change) {
                $value    = $change['value'] ?? [];
                $messages = $value['messages'] ?? [];
                $statuses = $value['statuses'] ?? [];

                foreach ($messages as $message) {
                    $from = $message['from'] ?? null; // número del remitente
                    $type = $message['type'] ?? null;

                    if ($type === 'text') {
                        $text = $message['text']['body'] ?? '';
                        Log::info("Mensaje de WhatsApp de {$from}: {$text}");

                        // Aquí puedes añadir lógica: guardar en BD, responder automáticamente, etc.
                    }
                }

                foreach ($statuses as $status) {
                    $statusValue = $status['status'] ?? null;
                    $statusContext = [
                        'message_id' => $status['id'] ?? null,
                        'status' => $statusValue,
                        'recipient_id' => $status['recipient_id'] ?? null,
                        'recipient_user_id' => $status['recipient_user_id'] ?? null,
                        'timestamp' => $status['timestamp'] ?? null,
                        'errors' => $this->normalizeWebhookErrors($status['errors'] ?? []),
                    ];

                    if ($statusValue === 'failed') {
                        Log::warning('WhatsApp webhook status fallido', $statusContext);
                    } else {
                        Log::info('WhatsApp webhook status', $statusContext);
                    }
                }
            }
        }

        // Meta espera siempre HTTP 200
        return response()->json(['status' => 'ok'], 200);
    }
}
