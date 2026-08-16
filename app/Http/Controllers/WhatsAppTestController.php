<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppCloudApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class WhatsAppTestController extends Controller
{
    private const TARGET_NUMBER = '+31 637319765';

    public function __construct(private readonly WhatsAppCloudApiService $whatsAppCloudApiService)
    {
    }

    public function index()
    {
        return view('pruebas', [
            'targetNumber' => self::TARGET_NUMBER,
            'webhookUrl' => url('/api/whatsapp/webhook'),
            'verifyToken' => config('services.whatsapp.verify_token'),
        ]);
    }

    public function send(): RedirectResponse
    {
        try {
            $response = $this->whatsAppCloudApiService->sendTextMessage(
                self::TARGET_NUMBER,
                'Mensaje de prueba enviado desde Aventura3 con WhatsApp Cloud API de Meta.'
            );

            Log::info('Mensaje de prueba de WhatsApp enviado', [
                'target' => self::TARGET_NUMBER,
                'response' => $response,
            ]);

            return back()->with('success', 'Mensaje enviado correctamente a ' . self::TARGET_NUMBER . '.');
        } catch (Throwable $exception) {
            Log::error('Error enviando mensaje de prueba por WhatsApp Cloud API', [
                'target' => self::TARGET_NUMBER,
                'error' => $exception->getMessage(),
            ]);

            return back()->withErrors([
                'whatsapp' => 'No se pudo enviar el mensaje de prueba: ' . $exception->getMessage(),
            ]);
        }
    }
}