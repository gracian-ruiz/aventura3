<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Bike;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

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
        try {
            $this->ensureMessagesTableExists();

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

                            try {
                                DB::table('whatsapp_messages')->insert([
                                    'user_id' => $this->findUserIdByPhone((string) $from),
                                    'bike_id' => $this->findBikeIdByPhone((string) $from),
                                    'appointment_id' => $this->findAppointmentIdByPhone((string) $from),
                                    'wa_id' => $message['id'] ?? null,
                                    'from_phone' => WhatsAppInboxController::normalizePhone($message['from'] ?? ''),
                                    'to_phone' => WhatsAppInboxController::normalizePhone((string) data_get($value, 'metadata.display_phone_number')),
                                    'direction' => 'inbound',
                                    'message_type' => $message['type'] ?? 'text',
                                    'body' => $message['text']['body'] ?? null,
                                    'status' => $message['status'] ?? 'received',
                                    'payload' => json_encode($message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                                    'received_at' => now(),
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            } catch (\Throwable $exception) {
                                Log::warning('WhatsApp webhook no pudo guardar el mensaje entrante', [
                                    'from' => $from,
                                    'error' => $exception->getMessage(),
                                ]);
                            }
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

                        try {
                            DB::table('whatsapp_messages')->insert([
                                'user_id' => $this->findUserIdByPhone((string) data_get($status, 'recipient_id', '')),
                                'bike_id' => $this->findBikeIdByPhone((string) data_get($status, 'recipient_id', '')),
                                'appointment_id' => $this->findAppointmentIdByPhone((string) data_get($status, 'recipient_id', '')),
                                'wa_id' => $status['id'] ?? null,
                                'from_phone' => WhatsAppInboxController::normalizePhone((string) data_get($value, 'metadata.display_phone_number')),
                                'to_phone' => WhatsAppInboxController::normalizePhone((string) data_get($status, 'recipient_id', '')),
                                'direction' => 'outbound',
                                'message_type' => 'status',
                                'body' => $status['status'] ?? null,
                                'status' => $status['status'] ?? null,
                                'payload' => json_encode($status, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                                'sent_at' => now(),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        } catch (\Throwable $exception) {
                            Log::warning('WhatsApp webhook no pudo guardar el estado', [
                                'message_id' => $status['id'] ?? null,
                                'error' => $exception->getMessage(),
                            ]);
                        }
                    }
                }
            }
        } catch (\Throwable $exception) {
            Log::error('WhatsApp webhook fallo procesando payload', [
                'error' => $exception->getMessage(),
            ]);
        }

        // Meta espera siempre HTTP 200
        return response()->json(['status' => 'ok'], 200);
    }

    private function findUserIdByPhone(string $phone): ?int
    {
        $normalizedPhone = preg_replace('/\D+/', '', $phone) ?? '';

        if ($normalizedPhone === '') {
            return null;
        }

        return User::query()
            ->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(COALESCE(telefono, ''), '+', ''), ' ', ''), '-', ''), '(', ''), ')', '') = ?", [$normalizedPhone])
            ->value('id');
    }

    private function findBikeIdByPhone(string $phone): ?int
    {
        $userId = $this->findUserIdByPhone($phone);

        if (!$userId) {
            return null;
        }

        return Bike::query()->where('user_id', $userId)->latest('id')->value('id');
    }

    private function findAppointmentIdByPhone(string $phone): ?int
    {
        $bikeId = $this->findBikeIdByPhone($phone);

        if (!$bikeId) {
            return null;
        }

        return Appointment::query()->where('bike_id', $bikeId)->latest('id')->value('id');
    }

    private function ensureMessagesTableExists(): void
    {
        if (Schema::hasTable('whatsapp_messages')) {
            return;
        }

        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('bike_id')->nullable();
            $table->foreignId('appointment_id')->nullable();
            $table->string('wa_id')->nullable()->index();
            $table->string('from_phone')->nullable()->index();
            $table->string('to_phone')->nullable()->index();
            $table->string('direction', 20)->default('inbound');
            $table->string('message_type', 30)->default('text');
            $table->text('body')->nullable();
            $table->string('status', 30)->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });
    }
}
