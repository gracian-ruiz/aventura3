<?php

namespace App\Http\Controllers;

use App\Models\WhatsAppMessage;
use App\Services\WhatsAppCloudApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class WhatsAppInboxController extends Controller
{
    public function __construct(private readonly WhatsAppCloudApiService $whatsAppCloudApiService)
    {
    }

    public function index(Request $request)
    {
        $this->ensureMessagesTableExists();

        $search = (string) $request->input('search', '');

        $messages = WhatsAppMessage::query()
            ->with(['user', 'bike', 'appointment'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('body', 'like', '%' . $search . '%')
                        ->orWhere('from_phone', 'like', '%' . $search . '%')
                        ->orWhere('to_phone', 'like', '%' . $search . '%')
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', '%' . $search . '%')
                                ->orWhere('email', 'like', '%' . $search . '%');
                        });
                });
            })
            ->orderByRaw('COALESCE(received_at, sent_at, created_at) desc')
            ->limit(300)
            ->get();

        $conversations = $messages->groupBy(function (WhatsAppMessage $message) {
            return $this->conversationKey($message->direction === 'inbound' ? ($message->from_phone ?? '') : ($message->to_phone ?? ''));
        })->map(function ($group) {
            $latest = $group->first();

            return [
                'phone' => $latest->direction === 'inbound' ? $latest->from_phone : $latest->to_phone,
                'user' => $latest->user,
                'bike' => $latest->bike,
                'appointment' => $latest->appointment,
                'last_message' => $latest,
                'count' => $group->count(),
            ];
        })->values();

        return view('whatsapp.index', compact('conversations', 'search'));
    }

    public function show(Request $request, string $phone)
    {
        $this->ensureMessagesTableExists();

        $normalizedPhone = $this->normalizePhone($phone);

        $messages = WhatsAppMessage::query()
            ->with(['user', 'bike', 'appointment'])
            ->where(function ($query) use ($normalizedPhone) {
                $query->where('from_phone', $normalizedPhone)
                    ->orWhere('to_phone', $normalizedPhone);
            })
            ->orderByRaw('COALESCE(received_at, sent_at, created_at) asc')
            ->get();

        return view('whatsapp.show', compact('messages', 'normalizedPhone'));
    }

    public function reply(Request $request, string $phone)
    {
        $this->ensureMessagesTableExists();

        $data = $request->validate([
            'body' => ['required', 'string', 'max:4096'],
        ]);

        $normalizedPhone = $this->normalizePhone($phone);

        Log::info('WhatsApp inbox: intento de envio desde chat', [
            'to' => $normalizedPhone,
            'body_length' => mb_strlen($data['body']),
            'body_preview' => mb_substr($data['body'], 0, 120),
            'user_id' => auth()->id(),
        ]);

        try {
            $response = $this->whatsAppCloudApiService->sendTextMessage($normalizedPhone, $data['body']);

            Log::info('WhatsApp inbox: respuesta de Meta al envio desde chat', [
                'to' => $normalizedPhone,
                'response' => $response,
            ]);
        } catch (\Throwable $exception) {
            Log::error('WhatsApp inbox: fallo al responder', [
                'phone' => $normalizedPhone,
                'error' => $exception->getMessage(),
            ]);

            return back()->with('error', 'No se ha podido enviar la respuesta. Revisa las credenciales o la ventana de 24 horas.');
        }

        WhatsAppMessage::create([
            'from_phone' => config('services.whatsapp.phone_number_id'),
            'to_phone' => $normalizedPhone,
            'direction' => 'outbound',
            'message_type' => 'text',
            'body' => $data['body'],
            'status' => 'sent',
            'payload' => $response,
            'sent_at' => now(),
        ]);

        Log::info('WhatsApp inbox: mensaje guardado como enviado', [
            'to' => $normalizedPhone,
            'status' => 'sent',
            'user_id' => auth()->id(),
        ]);

        return back()->with('success', 'Respuesta enviada correctamente.');
    }

    public static function storeIncoming(array $message, array $context = []): WhatsAppMessage
    {
        self::ensureMessagesTableExistsStatic();

        return WhatsAppMessage::create([
            'user_id' => $context['user_id'] ?? null,
            'bike_id' => $context['bike_id'] ?? null,
            'appointment_id' => $context['appointment_id'] ?? null,
            'wa_id' => $message['id'] ?? null,
            'from_phone' => self::normalizePhone($message['from'] ?? ''),
            'to_phone' => self::normalizePhone($context['to_phone'] ?? ''),
            'direction' => 'inbound',
            'message_type' => $message['type'] ?? 'text',
            'body' => data_get($message, 'text.body'),
            'status' => $message['status'] ?? 'received',
            'payload' => $message,
            'received_at' => now(),
        ]);
    }

    public static function storeStatus(array $status, array $context = []): WhatsAppMessage
    {
        self::ensureMessagesTableExistsStatic();

        return WhatsAppMessage::create([
            'user_id' => $context['user_id'] ?? null,
            'bike_id' => $context['bike_id'] ?? null,
            'appointment_id' => $context['appointment_id'] ?? null,
            'wa_id' => $status['id'] ?? null,
            'from_phone' => self::normalizePhone($context['from_phone'] ?? ''),
            'to_phone' => self::normalizePhone($status['recipient_id'] ?? ''),
            'direction' => 'outbound',
            'message_type' => 'status',
            'body' => $status['status'] ?? null,
            'status' => $status['status'] ?? null,
            'payload' => $status,
            'sent_at' => now(),
        ]);
    }

    private function conversationKey(string $phone): string
    {
        return self::normalizePhone($phone);
    }

    public static function normalizePhone(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone) ?? '';
    }

    private function ensureMessagesTableExists(): void
    {
        self::ensureMessagesTableExistsStatic();
    }

    private static function ensureMessagesTableExistsStatic(): void
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