<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
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
    private const PRESUPUESTO_TEMPLATE_KEY = 'presupuesto_reparacion';
    private const PRESUPUESTO_TEMPLATE_NAME = 'presupuesto_reparacion';
    private const PRESUPUESTO_REMINDER_TEMPLATE_KEY = 'repetir_envio_presupuesto';
    private const PRESUPUESTO_REMINDER_TEMPLATE_NAME = 'repetir_envio_presupuesto';
    private const PRESUPUESTO_TEMPLATE_LANG = 'es';

    public function __construct(private readonly WhatsAppCloudApiService $whatsAppCloudApiService)
    {
    }

    public function enviarPresupuestoWhatsApp($clienteId, $presupuestoId)
    {
        return $this->enviarPlantillaPresupuestoWhatsApp(
            $clienteId,
            $presupuestoId,
            self::PRESUPUESTO_TEMPLATE_KEY,
            true,
            '📩 Presupuesto enviado por WhatsApp.'
        );
    }

    public function reenviarPresupuestoWhatsApp($clienteId, $presupuestoId)
    {
        return $this->enviarPlantillaPresupuestoWhatsApp(
            $clienteId,
            $presupuestoId,
            self::PRESUPUESTO_REMINDER_TEMPLATE_KEY,
            false,
            '📩 Recordatorio de presupuesto enviado por WhatsApp.'
        );
    }

    private function enviarPlantillaPresupuestoWhatsApp(
        int|string $clienteId,
        int|string $presupuestoId,
        string $templateKey,
        bool $withDocumentHeader,
        string $successMessage
    )
    {
        $templateConfig = $this->resolveConversationTemplateByKey($templateKey);
        $templateName = $templateConfig['template_name'];
        $templateLanguage = $templateConfig['language'];

        Log::info('WhatsApp presupuesto: inicio de envio desde listado', [
            'version' => self::WHATSAPP_PRESUPUESTO_LOG_VERSION,
            'cliente_id' => $clienteId,
            'presupuesto_id' => $presupuestoId,
            'auth_user_id' => auth()->id(),
            'template_key' => $templateKey,
            'template' => $templateName,
        ]);

        // Buscar el presupuesto y la bicicleta
        $presupuesto = DB::table('appointments')
            ->join('bikes', 'appointments.bike_id', '=', 'bikes.id')
            ->join('users', 'bikes.user_id', '=', 'users.id')
            ->where('appointments.id', $presupuestoId)
            ->select(
                'appointments.*',
                'bikes.marca as bicicleta_marca',
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

        $presupuestoModel = Appointment::query()->find($presupuestoId);
        $presupuestoToken = $presupuestoModel?->token_presupuesto ?? $presupuesto->token_presupuesto ?? '';
        $presupuestoUrl = $presupuestoModel?->confirmationUrl()
            ?? Appointment::buildConfirmationUrl($presupuestoId, $presupuestoToken);
        $presupuestoUrlPath = Appointment::buildConfirmationPath($presupuestoId, $presupuestoToken);
        $presupuestoPdfPublicoUrl = route('presupuestos.pdf.publico', [
            'presupuestoId' => $presupuestoId,
            'token' => $presupuestoToken,
        ]);

        Log::info('WhatsApp presupuesto: datos cargados', [
            'version' => self::WHATSAPP_PRESUPUESTO_LOG_VERSION,
            'route_presupuesto_id' => $presupuestoId,
            'db_presupuesto_id' => $presupuesto->id ?? null,
            'db_token_presupuesto' => $presupuesto->token_presupuesto ?? null,
            'model_token_presupuesto' => $presupuestoModel?->token_presupuesto,
            'generated_confirmation_url' => $presupuestoUrl,
            'generated_pdf_public_url' => $presupuestoPdfPublicoUrl,
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
                'template_key' => $templateKey,
                'template' => $templateName,
                'language' => $templateLanguage,
                'presupuesto_url' => $presupuestoUrl,
                'pdf_public_url' => $presupuestoPdfPublicoUrl,
                'with_document_header' => $withDocumentHeader,
            ]);

            $nombreCliente = trim((string) ($presupuesto->usuario_nombre ?? ''));
            $nombreBicicleta = trim((string) ($presupuesto->bicicleta_marca ?? '') . ' ' . (string) ($presupuesto->bicicleta_nombre ?? ''));

            $buttonComponent = [
                'type' => 'button',
                'sub_type' => 'url',
                'index' => '0',
                'parameters' => [
                    [
                        'type' => 'text',
                        'text' => $presupuestoUrlPath,
                    ],
                ],
            ];

            try {
                $this->enviarPlantillaWhatsApp(
                    $telefonoDestino,
                    $presupuestoId,
                    [
                        $nombreCliente,
                        $nombreBicicleta,
                    ],
                    $withDocumentHeader ? [
                        'link' => $presupuestoPdfPublicoUrl,
                        'filename' => "presupuesto_{$presupuestoId}.pdf",
                    ] : null,
                    $withDocumentHeader ? $presupuestoPdfPublicoUrl : null,
                    $buttonComponent,
                    $templateName,
                    $templateLanguage,
                    "Plantilla {$templateName} enviada por WhatsApp"
                );
            } catch (\Throwable $exception) {
                $metaError = $this->extractMetaErrorFromException($exception);
                $metaCode = (int) ($metaError['code'] ?? 0);

                if ($metaCode === 132001 && $templateKey === self::PRESUPUESTO_REMINDER_TEMPLATE_KEY) {
                    Log::warning('WhatsApp presupuesto: fallback de plantilla de insistencia a plantilla principal', [
                        'version' => self::WHATSAPP_PRESUPUESTO_LOG_VERSION,
                        'presupuesto_id' => $presupuestoId,
                        'failed_template_key' => $templateKey,
                        'failed_template' => $templateName,
                        'meta_error' => $metaError,
                    ]);

                    try {
                        $fallbackTemplateConfig = $this->resolveConversationTemplateByKey(self::PRESUPUESTO_TEMPLATE_KEY);

                        $this->enviarPlantillaWhatsApp(
                            $telefonoDestino,
                            $presupuestoId,
                            [
                                $nombreCliente,
                                $nombreBicicleta,
                            ],
                            [
                                'link' => $presupuestoPdfPublicoUrl,
                                'filename' => "presupuesto_{$presupuestoId}.pdf",
                            ],
                            $presupuestoPdfPublicoUrl,
                            $buttonComponent,
                            $fallbackTemplateConfig['template_name'],
                            $fallbackTemplateConfig['language'],
                            "Plantilla {$fallbackTemplateConfig['template_name']} enviada por WhatsApp (fallback desde insistencia)"
                        );

                        return back()->with('success', '📩 Recordatorio enviado por WhatsApp con plantilla alternativa.');
                    } catch (\Throwable $fallbackException) {
                        Log::error('WhatsApp presupuesto: también falló el fallback de plantilla', [
                            'version' => self::WHATSAPP_PRESUPUESTO_LOG_VERSION,
                            'presupuesto_id' => $presupuestoId,
                            'fallback_template_key' => self::PRESUPUESTO_TEMPLATE_KEY,
                            'error' => $fallbackException->getMessage(),
                        ]);
                    }
                }

                if ($metaCode === 190) {
                    return back()->with('error', 'No se pudo enviar WhatsApp: token de Meta inválido o expirado (código 190). Actualiza WHATSAPP_ACCESS_TOKEN.');
                }

                if ($metaCode === 132001) {
                    return back()->with('error', 'No se pudo enviar WhatsApp: la plantilla no existe o no está aprobada en ese idioma (código 132001). Revisa nombre/idioma en Meta.');
                }

                if ($metaCode === 132000) {
                    return back()->with('error', 'No se pudo enviar WhatsApp: la plantilla requiere parámetros que no coinciden con los enviados (código 132000). Revisa la estructura de la plantilla en Meta.');
                }

                if ($metaCode === 132012) {
                    return back()->with('error', 'No se pudo enviar WhatsApp: formato de plantilla inválido (código 132012). Revisa componentes y formato de cabecera de la plantilla en Meta.');
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

        return back()->with('success', $successMessage);
    }

    private function enviarPlantillaWhatsApp(
        string $telefono,
        int|string $presupuestoId,
        array $bodyParams = [],
        ?array $headerDocument = null,
        ?string $documentPublicUrl = null,
        ?array $buttonComponent = null,
        string $templateName = self::PRESUPUESTO_TEMPLATE_NAME,
        string $templateLanguage = self::PRESUPUESTO_TEMPLATE_LANG,
        string $messageBody = 'Plantilla presupuesto_reparacion enviada por WhatsApp'
    ): void
    {
        try {
            $response = $this->whatsAppCloudApiService->sendTemplateMessage(
                $telefono,
                $templateName,
                $templateLanguage,
                $bodyParams,
                $headerDocument,
                $buttonComponent
            );

            Log::info('WhatsApp presupuesto: Meta acepto la plantilla', [
                'version' => self::WHATSAPP_PRESUPUESTO_LOG_VERSION,
                'presupuesto_id' => $presupuestoId,
                'to' => $this->normalizePhone((string) $telefono),
                'template' => $templateName,
                'language' => $templateLanguage,
                'body_params_count' => count($bodyParams),
                'has_header_document' => is_array($headerDocument),
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
                'body' => $messageBody,
                'status' => 'sent',
                'is_read' => true,
                'read_at' => now(),
                'payload' => array_merge($response, [
                    'document_public_url' => $documentPublicUrl,
                    'document_filename' => "presupuesto_{$presupuestoId}.pdf",
                    'document_kind' => 'pdf',
                ]),
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
                'template' => $templateName,
                'language' => $templateLanguage,
                'body_params_count' => count($bodyParams),
                'has_header_document' => is_array($headerDocument),
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
                'is_read' => true,
                'read_at' => now(),
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
        $normalized = preg_replace('/\D+/', '', $phone) ?? '';

        if (strlen($normalized) === 9) {
            return '34' . $normalized;
        }

        if (str_starts_with($normalized, '0034')) {
            return '34' . substr($normalized, 4);
        }

        return $normalized;
    }

    private function resolveConversationTemplateByKey(string $templateKey): array
    {
        $templates = config('services.whatsapp.conversation_templates', []);

        if (is_array($templates)) {
            foreach ($templates as $template) {
                if (!is_array($template)) {
                    continue;
                }

                if (trim((string) ($template['key'] ?? '')) !== $templateKey) {
                    continue;
                }

                $resolvedName = trim((string) ($template['template_name'] ?? ''));
                $resolvedLanguage = trim((string) ($template['language'] ?? self::PRESUPUESTO_TEMPLATE_LANG));

                if ($resolvedName !== '') {
                    return [
                        'template_name' => $resolvedName,
                        'language' => $resolvedLanguage !== '' ? $resolvedLanguage : self::PRESUPUESTO_TEMPLATE_LANG,
                    ];
                }
            }
        }

        if ($templateKey === self::PRESUPUESTO_REMINDER_TEMPLATE_KEY) {
            return [
                'template_name' => self::PRESUPUESTO_REMINDER_TEMPLATE_NAME,
                'language' => self::PRESUPUESTO_TEMPLATE_LANG,
            ];
        }

        return [
            'template_name' => self::PRESUPUESTO_TEMPLATE_NAME,
            'language' => self::PRESUPUESTO_TEMPLATE_LANG,
        ];
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
