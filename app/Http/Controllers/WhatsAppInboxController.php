<?php

namespace App\Http\Controllers;

use App\Models\WhatsAppMessage;
use App\Models\User;
use App\Models\Bike;
use App\Models\Appointment;
use App\Services\WhatsAppCloudApiService;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
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
        $messageSearch = trim((string) $request->input('q', ''));

        $query = WhatsAppMessage::query()
            ->with(['user', 'bike', 'appointment'])
            ->where(function ($query) use ($normalizedPhone) {
                $query->where('from_phone', $normalizedPhone)
                    ->orWhere('to_phone', $normalizedPhone);
            })
            ->orderByRaw('COALESCE(received_at, sent_at, created_at) asc');

        if ($messageSearch !== '') {
            $query->where(function ($subQuery) use ($messageSearch) {
                $subQuery->where('body', 'like', '%' . $messageSearch . '%')
                    ->orWhere('status', 'like', '%' . $messageSearch . '%')
                    ->orWhere('message_type', 'like', '%' . $messageSearch . '%');
            });
        }

        $messages = $query->get();

        $conversationUser = $messages->firstWhere('user_id', '!=', null)?->user;
        if (!$conversationUser) {
            $conversationUser = $this->resolveUserByPhone($normalizedPhone);
        }

        $conversationBike = $messages->firstWhere('bike_id', '!=', null)?->bike;
        if (!$conversationBike && $conversationUser) {
            $conversationBike = Bike::query()->where('user_id', $conversationUser->id)->latest('id')->first();
        }

        $conversationAppointment = $messages->firstWhere('appointment_id', '!=', null)?->appointment;
        if (!$conversationAppointment && $conversationBike) {
            $conversationAppointment = Appointment::query()->where('bike_id', $conversationBike->id)->latest('id')->first();
        }

        $conversationTitle = $conversationUser?->name ?: $normalizedPhone;

        if ($request->boolean('partial')) {
            return view('whatsapp.partials.messages', compact('messages'));
        }

        return view('whatsapp.show', compact(
            'messages',
            'normalizedPhone',
            'messageSearch',
            'conversationUser',
            'conversationBike',
            'conversationAppointment',
            'conversationTitle'
        ));
    }

    public function reply(Request $request, string $phone)
    {
        $this->ensureMessagesTableExists();

        $data = $request->validate([
            'body' => ['nullable', 'string', 'max:4096', 'required_without:image'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120', 'required_without:body'],
        ]);

        $normalizedPhone = $this->normalizePhone($phone);
        $body = trim((string) ($data['body'] ?? ''));
        $isImage = $request->hasFile('image');

        Log::info('WhatsApp inbox: intento de envio desde chat', [
            'to' => $normalizedPhone,
            'body_length' => mb_strlen($body),
            'body_preview' => mb_substr($body, 0, 120),
            'has_image' => $isImage,
            'user_id' => auth()->id(),
        ]);

        try {
            $messageType = 'text';
            $storedBody = $body;
            $storedPayload = [];

            if ($isImage) {
                $image = $request->file('image');
                $storedImagePath = $image->store('public/whatsapp_outbound');
                $storedImageUrl = asset('storage/' . ltrim(str_replace('public/', '', $storedImagePath), '/'));

                $response = $this->whatsAppCloudApiService->sendImageMessageFromFile(
                    $normalizedPhone,
                    $body !== '' ? $body : null,
                    $image->getRealPath(),
                    $image->getClientOriginalName()
                );
                $messageType = 'image';
                $storedBody = $body !== '' ? '[imagen] ' . $body : '[imagen]';
                $storedPayload = array_merge($response, [
                    'local_image_url' => $storedImageUrl,
                    'local_image_path' => $storedImagePath,
                ]);
            } else {
                $response = $this->whatsAppCloudApiService->sendTextMessage($normalizedPhone, $body);
                $storedPayload = $response;
            }

            Log::info('WhatsApp inbox: respuesta de Meta al envio desde chat', [
                'to' => $normalizedPhone,
                'message_type' => $messageType,
                'response' => $response,
            ]);
        } catch (\Throwable $exception) {
            $metaError = [];

            if ($exception instanceof RequestException && $exception->response !== null) {
                $error = $exception->response->json('error');
                $metaError = is_array($error)
                    ? [
                        'http_status' => $exception->response->status(),
                        'message' => $error['message'] ?? null,
                        'type' => $error['type'] ?? null,
                        'code' => $error['code'] ?? null,
                        'error_subcode' => $error['error_subcode'] ?? null,
                        'details' => data_get($error, 'error_data.details'),
                        'fbtrace_id' => $error['fbtrace_id'] ?? null,
                    ]
                    : ['http_status' => $exception->response->status()];
            }

            Log::error('WhatsApp inbox: fallo al responder', [
                'phone' => $normalizedPhone,
                'error' => $exception->getMessage(),
                'meta_error' => $metaError,
            ]);

            return back()->with('error', 'No se ha podido enviar la respuesta. Revisa las credenciales o la ventana de 24 horas.');
        }

        WhatsAppMessage::create([
            'from_phone' => config('services.whatsapp.phone_number_id'),
            'to_phone' => $normalizedPhone,
            'direction' => 'outbound',
            'message_type' => $messageType,
            'body' => $storedBody,
            'status' => 'sent',
            'payload' => $storedPayload,
            'sent_at' => now(),
        ]);

        Log::info('WhatsApp inbox: mensaje guardado como enviado', [
            'to' => $normalizedPhone,
            'status' => 'sent',
            'user_id' => auth()->id(),
        ]);

        return back()->with('success', 'Respuesta enviada correctamente.');
    }

    public function media(WhatsAppMessage $message)
    {
        if ($message->message_type !== 'image') {
            abort(404);
        }

        $payload = is_array($message->payload) ? $message->payload : [];
        $mediaId = (string) data_get($payload, 'image.id');

        // Para imagenes enviadas desde el panel servimos el archivo local directamente.
        $localPath = (string) data_get($payload, 'local_image_path', '');
        if ($mediaId === '' && $localPath !== '') {
            if (!Storage::exists($localPath)) {
                abort(404);
            }

            $absolutePath = storage_path('app/' . ltrim($localPath, '/'));
            $mimeType = Storage::mimeType($localPath) ?: 'image/jpeg';

            if (!is_file($absolutePath)) {
                abort(404);
            }

            return response()->file($absolutePath, [
                'Content-Type' => $mimeType,
                'Cache-Control' => 'private, max-age=300',
            ]);
        }

        if ($mediaId === '') {
            abort(404);
        }

        $accessToken = (string) config('services.whatsapp.access_token');
        if ($accessToken === '') {
            abort(503);
        }

        try {
            $meta = Http::withToken($accessToken)
                ->acceptJson()
                ->get("https://graph.facebook.com/v23.0/{$mediaId}")
                ->throw()
                ->json();

            $downloadUrl = (string) ($meta['url'] ?? '');
            $mimeType = (string) ($meta['mime_type'] ?? 'image/jpeg');

            if ($downloadUrl === '') {
                abort(404);
            }

            $binary = Http::withToken($accessToken)
                ->get($downloadUrl)
                ->throw();

            return response($binary->body(), 200, [
                'Content-Type' => $mimeType,
                'Cache-Control' => 'private, max-age=300',
            ]);
        } catch (\Throwable $exception) {
            Log::warning('WhatsApp inbox: no se pudo recuperar media de Meta', [
                'message_id' => $message->id,
                'media_id' => $mediaId,
                'error' => $exception->getMessage(),
            ]);

            abort(404);
        }
    }

    private function resolveUserByPhone(string $normalizedPhone): ?User
    {
        if ($normalizedPhone === '') {
            return null;
        }

        $users = User::query()
            ->select('id', 'name', 'email', 'telefono')
            ->whereNotNull('telefono')
            ->get();

        foreach ($users as $user) {
            $userPhone = self::normalizePhone((string) $user->telefono);

            if ($this->phoneMatches($normalizedPhone, $userPhone)) {
                return $user;
            }
        }

        return null;
    }

    private function phoneMatches(string $a, string $b): bool
    {
        if ($a === '' || $b === '') {
            return false;
        }

        if ($a === $b) {
            return true;
        }

        $aLast9 = substr($a, -9);
        $bLast9 = substr($b, -9);

        return $aLast9 !== false
            && $bLast9 !== false
            && $aLast9 === $bLast9;
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