<?php

namespace App\Http\Controllers;

use App\Models\WhatsAppMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Client\RequestException;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\WhatsAppCloudApiService;

class WhatsAppController extends Controller
{
    private const WHATSAPP_PRESUPUESTO_LOG_VERSION = 'presupuesto-whatsapp-v3-template';
    private const PRESUPUESTO_TEMPLATE_NAME = 'presupuesto_reparacion';
    private const PRESUPUESTO_TEMPLATE_LANG = 'es';

    public function __construct(private readonly WhatsAppCloudApiService $whatsAppCloudApiService)
    {
    }

    public function enviarPresupuestoWhatsApp($clienteId, $presupuestoId)
    {
        Log::info('WhatsApp presupuesto: inicio de envio desde listado', [
            'version' => self::WHATSAPP_PRESUPUESTO_LOG_VERSION,
            'cliente_id' => $clienteId,
            'presupuesto_id' => $presupuestoId,
            'auth_user_id' => auth()->id(),
        ]);

        // Buscar el presupuesto y la bicicleta
        $presupuesto = DB::table('appointments')
            ->join('bikes', 'appointments.bike_id', '=', 'bikes.id')
            ->join('users', 'bikes.user_id', '=', 'users.id')
            ->where('appointments.id', $presupuestoId)
            ->select(
                'appointments.*',
                'bikes.nombre as bicicleta_nombre',
                'users.id as usuario_id',
                'users.name as usuario_nombre',
                'users.telefono as usuario_telefono'
            )
            ->first();

        if (!$presupuesto) {
            Log::warning('WhatsApp presupuesto: presupuesto no encontrado', [
                'cliente_id' => $clienteId,
                'presupuesto_id' => $presupuestoId,
            ]);

            return response()->json(['error' => 'Presupuesto no encontrado'], 404);
        }

        $presupuestoUrl = url("confirmacion/presupuesto/{$presupuestoId}?token={$presupuesto->token_presupuesto}");

        Log::info('WhatsApp presupuesto: datos cargados', [
            'version' => self::WHATSAPP_PRESUPUESTO_LOG_VERSION,
            'cliente_id' => $presupuesto->usuario_id ?? null,
            'cliente_nombre' => $presupuesto->usuario_nombre ?? null,
            'cliente_telefono' => $presupuesto->usuario_telefono ?? null,
            'presupuesto_estado' => $presupuesto->estado ?? null,
            'presupuesto_enviado' => $presupuesto->presupuesto_enviado ?? null,
        ]);

        // Enviar plantilla por WhatsApp
        $telefonoDestino = $presupuesto->usuario_telefono ?? null;

        if (!empty($telefonoDestino)) {
            Log::info('WhatsApp presupuesto: enviando plantilla por Cloud API', [
                'version' => self::WHATSAPP_PRESUPUESTO_LOG_VERSION,
                'presupuesto_id' => $presupuestoId,
                'to' => $this->normalizePhone((string) $telefonoDestino),
                'template' => self::PRESUPUESTO_TEMPLATE_NAME,
                'language' => self::PRESUPUESTO_TEMPLATE_LANG,
                'presupuesto_url' => $presupuestoUrl,
            ]);

            try {
                $this->enviarPlantillaWhatsApp(
                    $telefonoDestino,
                    $presupuestoId,
                    [
                        (string) ($presupuesto->usuario_nombre ?? ''),
                        (string) $presupuestoUrl,
                    ]
                );
            } catch (\Throwable $exception) {
                $metaError = $this->extractMetaErrorFromException($exception);
                $metaCode = (int) ($metaError['code'] ?? 0);

                if ($metaCode === 190) {
                    return back()->with('error', 'No se pudo enviar WhatsApp: token de Meta inválido o expirado (código 190). Actualiza WHATSAPP_ACCESS_TOKEN.');
                }

                if ($metaCode === 132000) {
                    return back()->with('error', 'No se pudo enviar WhatsApp: la plantilla requiere parámetros que no coinciden con los enviados (código 132000). Revisa la estructura de presupuesto_reparacion en Meta.');
                }

                return back()->with('error', 'No se pudo enviar la plantilla de WhatsApp. Revisa logs de WhatsApp Cloud API para más detalle.');
            }
        } else {
            Log::warning('WhatsApp presupuesto: cliente sin telefono', [
                'cliente_id' => $presupuesto->usuario_id ?? null,
                'presupuesto_id' => $presupuestoId,
            ]);

            return back()->with('error', 'El cliente de este presupuesto no tiene teléfono registrado para WhatsApp.');
        }

        return back()->with('success', '📩 Presupuesto enviado por WhatsApp.');
    }

    private function enviarPlantillaWhatsApp(string $telefono, int|string $presupuestoId, array $bodyParams = []): void
    {
        try {
            $response = $this->whatsAppCloudApiService->sendTemplateMessage(
                $telefono,
                self::PRESUPUESTO_TEMPLATE_NAME,
                self::PRESUPUESTO_TEMPLATE_LANG,
                $bodyParams
            );

            Log::info('WhatsApp presupuesto: Meta acepto la plantilla', [
                'version' => self::WHATSAPP_PRESUPUESTO_LOG_VERSION,
                'presupuesto_id' => $presupuestoId,
                'to' => $this->normalizePhone((string) $telefono),
                'template' => self::PRESUPUESTO_TEMPLATE_NAME,
                'body_params_count' => count($bodyParams),
                'response' => $response,
            ]);

            DB::table('appointments')
                ->where('id', $presupuestoId)
                ->update(['presupuesto_enviado' => true]);

            WhatsAppMessage::create([
                'user_id' => DB::table('appointments')->where('id', $presupuestoId)->value('user_id'),
                'bike_id' => DB::table('appointments')->where('id', $presupuestoId)->value('bike_id'),
                'appointment_id' => $presupuestoId,
                'wa_id' => data_get($response, 'messages.0.id'),
                'from_phone' => (string) config('services.whatsapp.phone_number_id'),
                'to_phone' => $this->normalizePhone((string) $telefono),
                'direction' => 'outbound',
                'message_type' => 'template',
                'body' => 'Plantilla presupuesto_reparacion enviada por WhatsApp',
                'status' => 'sent',
                'payload' => $response,
                'sent_at' => now(),
            ]);

            Log::info('WhatsApp presupuesto: marcado como enviado en BD', [
                'version' => self::WHATSAPP_PRESUPUESTO_LOG_VERSION,
                'presupuesto_id' => $presupuestoId,
            ]);
        } catch (\Throwable $e) {
            Log::error('WhatsApp presupuesto: fallo al enviar plantilla', [
                'version' => self::WHATSAPP_PRESUPUESTO_LOG_VERSION,
                'presupuesto_id' => $presupuestoId,
                'to' => $this->normalizePhone((string) $telefono),
                'template' => self::PRESUPUESTO_TEMPLATE_NAME,
                'body_params_count' => count($bodyParams),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function generarPDF($presupuestoId)
    {
        // Obtener datos del presupuesto
        $presupuesto = DB::table('appointments')
            ->join('bikes', 'appointments.bike_id', '=', 'bikes.id')
            ->join('users', 'bikes.user_id', '=', 'users.id')
            ->where('appointments.id', $presupuestoId)
            ->select('appointments.*', 'bikes.nombre as bicicleta_nombre','bikes.marca as marca', 'users.name as usuario_nombre')
            ->first();

        Log::info('WhatsApp presupuesto: preparando datos de PDF', [
            'version' => self::WHATSAPP_PRESUPUESTO_LOG_VERSION,
            'presupuesto_id' => $presupuestoId,
            'presupuesto_found' => (bool) $presupuesto,
        ]);
            

        $items = DB::table('appointment_component')
            ->join('components', 'appointment_component.componente_id', '=', 'components.id')
            ->where('appointment_component.appointment_id', $presupuestoId)
            ->select('appointment_component.*', 'components.nombre as componente_nombre')
            ->get();

        // Nombre del archivo PDF basado en el ID del presupuesto
        $nombreArchivo = "presupuesto_{$presupuestoId}.pdf";
        $rutaAlmacenamiento = "public/presupuestos/$nombreArchivo";

        // Generar y guardar el PDF
        $pdf = Pdf::loadView('pdf.presupuesto2', compact('presupuesto', 'items'));
        Storage::put($rutaAlmacenamiento, $pdf->output());

        Log::info('WhatsApp presupuesto: PDF guardado', [
            'version' => self::WHATSAPP_PRESUPUESTO_LOG_VERSION,
            'presupuesto_id' => $presupuestoId,
            'storage_path' => $rutaAlmacenamiento,
            'items_count' => $items->count(),
        ]);

        return storage_path("app/$rutaAlmacenamiento");
    }

    private function enviarMensajeWhatsApp($telefono, $mensaje, $pdfPath, $presupuestoId)
    {
        try {
            $response = $this->whatsAppCloudApiService->sendDocumentMessageFromFile($telefono, $mensaje, $pdfPath, basename($pdfPath));

            Log::info('WhatsApp presupuesto: Meta acepto el envio', [
                'version' => self::WHATSAPP_PRESUPUESTO_LOG_VERSION,
                'presupuesto_id' => $presupuestoId,
                'to' => $this->normalizePhone((string) $telefono),
                'response' => $response,
            ]);

            DB::table('appointments')
                ->where('id', $presupuestoId)
                ->update(['presupuesto_enviado' => true]);

            WhatsAppMessage::create([
                'user_id' => $clienteId = DB::table('appointments')->where('id', $presupuestoId)->value('user_id'),
                'bike_id' => DB::table('appointments')->where('id', $presupuestoId)->value('bike_id'),
                'appointment_id' => $presupuestoId,
                'wa_id' => data_get($response, 'messages.0.id'),
                'from_phone' => (string) config('services.whatsapp.phone_number_id'),
                'to_phone' => $this->normalizePhone((string) $telefono),
                'direction' => 'outbound',
                'message_type' => 'document',
                'body' => 'PDF de presupuesto enviado por WhatsApp',
                'status' => 'sent',
                'payload' => $response,
                'sent_at' => now(),
            ]);

            Log::info('WhatsApp presupuesto: marcado como enviado en BD', [
                'version' => self::WHATSAPP_PRESUPUESTO_LOG_VERSION,
                'presupuesto_id' => $presupuestoId,
            ]);

        } catch (\Exception $e) {
            Log::error('WhatsApp presupuesto: fallo al enviar mensaje', [
                'presupuesto_id' => $presupuestoId,
                'to' => $this->normalizePhone((string) $telefono),
                'pdf_path' => $pdfPath,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function normalizePhone(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone) ?? '';
    }

    private function extractMetaErrorFromException(\Throwable $exception): array
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

}
