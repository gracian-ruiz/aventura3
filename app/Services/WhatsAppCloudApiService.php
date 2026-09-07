<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class WhatsAppCloudApiService
{
    public function sendTextMessage(string $to, string $body): array
    {
        $phoneNumberId = (string) config('services.whatsapp.phone_number_id');
        $accessToken = (string) config('services.whatsapp.access_token');

        $missingCredentials = [];

        if ($phoneNumberId === '') {
            $missingCredentials[] = 'WHATSAPP_PHONE_NUMBER_ID';
        }

        if ($accessToken === '') {
            $missingCredentials[] = 'WHATSAPP_ACCESS_TOKEN';
        }

        if (!empty($missingCredentials)) {
            Log::warning('WhatsApp Cloud API: faltan credenciales para enviar mensaje', [
                'missing' => $missingCredentials,
                'to' => $this->normalizePhoneNumber($to),
            ]);

            throw new RuntimeException('Faltan credenciales de WhatsApp Cloud API: ' . implode(', ', $missingCredentials));
        }

        $normalizedTo = $this->normalizePhoneNumber($to);

        Log::info('WhatsApp Cloud API: enviando mensaje de prueba', [
            'to' => $normalizedTo,
            'phone_number_id' => $phoneNumberId,
            'body_length' => mb_strlen($body),
        ]);

        try {
            return Http::withToken($accessToken)
                ->acceptJson()
                ->post($this->messagesUrl($phoneNumberId), [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $normalizedTo,
                    'type' => 'text',
                    'text' => [
                        'preview_url' => false,
                        'body' => $body,
                    ],
                ])
                ->throw()
                ->json();
        } catch (Throwable $exception) {
            Log::error('WhatsApp Cloud API: fallo al enviar mensaje', [
                'to' => $normalizedTo,
                'phone_number_id' => $phoneNumberId,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function messagesUrl(?string $phoneNumberId = null): string
    {
        $resolvedPhoneNumberId = $phoneNumberId ?: (string) config('services.whatsapp.phone_number_id');

        return sprintf('https://graph.facebook.com/v23.0/%s/messages', $resolvedPhoneNumberId);
    }

    private function normalizePhoneNumber(string $phoneNumber): string
    {
        return preg_replace('/\D+/', '', $phoneNumber) ?? '';
    }
}