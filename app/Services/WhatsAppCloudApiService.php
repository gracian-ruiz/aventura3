<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class WhatsAppCloudApiService
{
    private const LOG_VERSION = 'whatsapp-cloud-service-v2-media-id';

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
                'meta_error' => $this->extractMetaError($exception),
            ]);

            throw $exception;
        }
    }

    public function messagesUrl(?string $phoneNumberId = null): string
    {
        $resolvedPhoneNumberId = $phoneNumberId ?: (string) config('services.whatsapp.phone_number_id');

        return sprintf('https://graph.facebook.com/v23.0/%s/messages', $resolvedPhoneNumberId);
    }

    public function sendDocumentMessage(string $to, string $caption, string $documentUrl, ?string $filename = null): array
    {
        $phoneNumberId = (string) config('services.whatsapp.phone_number_id');
        $accessToken = (string) config('services.whatsapp.access_token');

        if ($phoneNumberId === '' || $accessToken === '') {
            throw new RuntimeException('Faltan credenciales de WhatsApp Cloud API para enviar documentos.');
        }

        $normalizedTo = $this->normalizePhoneNumber($to);

        try {
            return Http::withToken($accessToken)
                ->acceptJson()
                ->post($this->messagesUrl($phoneNumberId), [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $normalizedTo,
                    'type' => 'document',
                    'document' => array_filter([
                        'link' => $documentUrl,
                        'caption' => $caption,
                        'filename' => $filename,
                    ], static fn ($value) => $value !== null && $value !== ''),
                ])
                ->throw()
                ->json();
        } catch (Throwable $exception) {
            Log::error('WhatsApp Cloud API: fallo al enviar documento', [
                'to' => $normalizedTo,
                'phone_number_id' => $phoneNumberId,
                'error' => $exception->getMessage(),
                'meta_error' => $this->extractMetaError($exception),
            ]);

            throw $exception;
        }
    }

    public function sendDocumentMessageFromFile(string $to, string $caption, string $filePath, ?string $filename = null): array
    {
        $phoneNumberId = (string) config('services.whatsapp.phone_number_id');
        $accessToken = (string) config('services.whatsapp.access_token');

        if ($phoneNumberId === '' || $accessToken === '') {
            throw new RuntimeException('Faltan credenciales de WhatsApp Cloud API para enviar documentos.');
        }

        if (!is_file($filePath)) {
            throw new RuntimeException('No existe el archivo PDF a enviar por WhatsApp: ' . $filePath);
        }

        $normalizedTo = $this->normalizePhoneNumber($to);
        $resolvedFilename = $filename ?: basename($filePath);

        Log::info('WhatsApp Cloud API: subiendo documento a Meta', [
            'version' => self::LOG_VERSION,
            'to' => $normalizedTo,
            'phone_number_id' => $phoneNumberId,
            'file_path' => $filePath,
            'filename' => $resolvedFilename,
        ]);

        try {
            $mediaResponse = Http::withToken($accessToken)
                ->attach('file', fopen($filePath, 'r'), $resolvedFilename)
                ->post($this->mediaUploadUrl($phoneNumberId), [
                    'messaging_product' => 'whatsapp',
                    'type' => 'application/pdf',
                ])
                ->throw()
                ->json();

            $mediaId = (string) ($mediaResponse['id'] ?? '');

            if ($mediaId === '') {
                throw new RuntimeException('Meta no devolvió un media_id al subir el PDF.');
            }

            Log::info('WhatsApp Cloud API: documento subido a Meta', [
                'version' => self::LOG_VERSION,
                'to' => $normalizedTo,
                'phone_number_id' => $phoneNumberId,
                'media_id' => $mediaId,
            ]);

            return Http::withToken($accessToken)
                ->acceptJson()
                ->post($this->messagesUrl($phoneNumberId), [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $normalizedTo,
                    'type' => 'document',
                    'document' => array_filter([
                        'id' => $mediaId,
                        'caption' => $caption,
                        'filename' => $resolvedFilename,
                    ], static fn ($value) => $value !== null && $value !== ''),
                ])
                ->throw()
                ->json();
        } catch (Throwable $exception) {
            Log::error('WhatsApp Cloud API: fallo al enviar documento desde archivo', [
                'to' => $normalizedTo,
                'phone_number_id' => $phoneNumberId,
                'file_path' => $filePath,
                'error' => $exception->getMessage(),
                'meta_error' => $this->extractMetaError($exception),
            ]);

            throw $exception;
        }
    }

    public function sendTemplateMessage(string $to, string $templateName, string $languageCode = 'es'): array
    {
        $phoneNumberId = (string) config('services.whatsapp.phone_number_id');
        $accessToken = (string) config('services.whatsapp.access_token');

        if ($phoneNumberId === '' || $accessToken === '') {
            throw new RuntimeException('Faltan credenciales de WhatsApp Cloud API para enviar plantillas.');
        }

        if (trim($templateName) === '') {
            throw new RuntimeException('Falta el nombre de plantilla de WhatsApp.');
        }

        $normalizedTo = $this->normalizePhoneNumber($to);

        Log::info('WhatsApp Cloud API: enviando plantilla', [
            'version' => self::LOG_VERSION,
            'to' => $normalizedTo,
            'phone_number_id' => $phoneNumberId,
            'template' => $templateName,
            'language' => $languageCode,
        ]);

        try {
            return Http::withToken($accessToken)
                ->acceptJson()
                ->post($this->messagesUrl($phoneNumberId), [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $normalizedTo,
                    'type' => 'template',
                    'template' => [
                        'name' => $templateName,
                        'language' => [
                            'code' => $languageCode,
                        ],
                    ],
                ])
                ->throw()
                ->json();
        } catch (Throwable $exception) {
            Log::error('WhatsApp Cloud API: fallo al enviar plantilla', [
                'to' => $normalizedTo,
                'phone_number_id' => $phoneNumberId,
                'template' => $templateName,
                'language' => $languageCode,
                'error' => $exception->getMessage(),
                'meta_error' => $this->extractMetaError($exception),
            ]);

            throw $exception;
        }
    }

    public function mediaUploadUrl(?string $phoneNumberId = null): string
    {
        $resolvedPhoneNumberId = $phoneNumberId ?: (string) config('services.whatsapp.phone_number_id');

        return sprintf('https://graph.facebook.com/v23.0/%s/media', $resolvedPhoneNumberId);
    }

    private function extractMetaError(Throwable $exception): array
    {
        if (!$exception instanceof RequestException || $exception->response === null) {
            return [];
        }

        $status = $exception->response->status();
        $json = $exception->response->json();
        $error = is_array($json) ? ($json['error'] ?? null) : null;

        if (!is_array($error)) {
            return ['http_status' => $status];
        }

        return [
            'http_status' => $status,
            'message' => $error['message'] ?? null,
            'type' => $error['type'] ?? null,
            'code' => $error['code'] ?? null,
            'error_subcode' => $error['error_subcode'] ?? null,
            'details' => data_get($error, 'error_data.details'),
            'fbtrace_id' => $error['fbtrace_id'] ?? null,
        ];
    }

    private function normalizePhoneNumber(string $phoneNumber): string
    {
        return preg_replace('/\D+/', '', $phoneNumber) ?? '';
    }
}