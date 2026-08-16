<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class WhatsAppCloudApiService
{
    public function sendTextMessage(string $to, string $body): array
    {
        $phoneNumberId = (string) config('services.whatsapp.phone_number_id');
        $accessToken = (string) config('services.whatsapp.access_token');

        if ($phoneNumberId === '' || $accessToken === '') {
            throw new RuntimeException('Faltan credenciales de WhatsApp Cloud API en la configuración.');
        }

        return Http::withToken($accessToken)
            ->acceptJson()
            ->post($this->messagesUrl($phoneNumberId), [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $this->normalizePhoneNumber($to),
                'type' => 'text',
                'text' => [
                    'preview_url' => false,
                    'body' => $body,
                ],
            ])
            ->throw()
            ->json();
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