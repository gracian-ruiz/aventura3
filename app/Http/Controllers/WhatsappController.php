<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\WhatsAppCloudApiService;

class WhatsAppController extends Controller
{
    public function __construct(private readonly WhatsAppCloudApiService $whatsAppCloudApiService)
    {
    }

    public function enviarPresupuestoWhatsApp($clienteId, $presupuestoId)
    {
        Log::info('WhatsApp presupuesto: inicio de envio desde listado', [
            'cliente_id' => $clienteId,
            'presupuesto_id' => $presupuestoId,
            'auth_user_id' => auth()->id(),
        ]);

        // Buscar el cliente
        $cliente = DB::table('users')->where('id', $clienteId)->first();
        if (!$cliente) {
            Log::warning('WhatsApp presupuesto: cliente no encontrado', [
                'cliente_id' => $clienteId,
                'presupuesto_id' => $presupuestoId,
            ]);

            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        // Buscar el presupuesto y la bicicleta
        $presupuesto = DB::table('appointments')
            ->join('bikes', 'appointments.bike_id', '=', 'bikes.id')
            ->join('users', 'bikes.user_id', '=', 'users.id')
            ->where('appointments.id', $presupuestoId)
            ->select(
                'appointments.*',
                'bikes.nombre as bicicleta_nombre',
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
            'cliente_id' => $cliente->id ?? null,
            'cliente_email' => $cliente->email ?? null,
            'cliente_telefono' => $cliente->telefono ?? null,
            'presupuesto_estado' => $presupuesto->estado ?? null,
            'presupuesto_enviado' => $presupuesto->presupuesto_enviado ?? null,
        ]);

        if (!$this->whatsappTestGateAllows($cliente->email ?? null, $cliente->telefono ?? null)) {
            Log::warning('WhatsApp presupuesto bloqueado por filtro de prueba', [
                'cliente_id' => $cliente->id ?? null,
                'email' => $cliente->email ?? null,
                'telefono' => $cliente->telefono ?? null,
                'allowed_emails' => $this->allowedEmails(),
                'allowed_phone' => $this->normalizePhone((string) config('services.whatsapp.notice_phone_gate')),
            ]);

            return back()->with('error', 'El envío de prueba por WhatsApp solo está permitido para el cliente autorizado.');
        }

        // 1. GENERAR Y EXPONER EL PDF CON URL PUBLICA FIRMADA POR TOKEN
        $pdfPath = $this->generarPDF($presupuestoId);
        $pdfUrl = route('presupuestos.pdf.publico', [
            'presupuestoId' => $presupuestoId,
            'token' => $presupuesto->token_presupuesto,
        ]);

        Log::info('WhatsApp presupuesto: PDF generado', [
            'presupuesto_id' => $presupuestoId,
            'pdf_path' => $pdfPath,
            'pdf_url' => $pdfUrl,
        ]);

        // 2. ENVIAR POR WHATSAPP
        if (!empty($cliente->telefono)) {
            $mensaje = "📄 ¡Hola {$cliente->name}! Te escribo de Aventura Bike, te envío el presupuesto para arreglar tu bicicleta '{$presupuesto->bicicleta_nombre}'.\n\n"
                . "🔗 Puedes confirmar el presupuesto aquí: {$presupuestoUrl}";

            Log::info('WhatsApp presupuesto: enviando documento por Cloud API', [
                'presupuesto_id' => $presupuestoId,
                'to' => $this->normalizePhone((string) $cliente->telefono),
                'body_length' => mb_strlen($mensaje),
            ]);

            $this->enviarMensajeWhatsApp($cliente->telefono, $mensaje, $pdfUrl, $presupuestoId);
        } else {
            Log::warning('WhatsApp presupuesto: cliente sin telefono', [
                'cliente_id' => $cliente->id ?? null,
                'presupuesto_id' => $presupuestoId,
            ]);
        }

        return back()->with('success', '📩 Presupuesto enviado por WhatsApp.');
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
            'presupuesto_id' => $presupuestoId,
            'storage_path' => $rutaAlmacenamiento,
            'items_count' => $items->count(),
        ]);

        return storage_path("app/$rutaAlmacenamiento");
    }

    private function enviarMensajeWhatsApp($telefono, $mensaje, $pdfUrl, $presupuestoId)
    {
        try {
            $response = $this->whatsAppCloudApiService->sendDocumentMessage($telefono, $mensaje, $pdfUrl, basename($pdfUrl));

            Log::info('WhatsApp presupuesto: Meta acepto el envio', [
                'presupuesto_id' => $presupuestoId,
                'to' => $this->normalizePhone((string) $telefono),
                'response' => $response,
            ]);

            DB::table('appointments')
                ->where('id', $presupuestoId)
                ->update(['presupuesto_enviado' => true]);

            Log::info('WhatsApp presupuesto: marcado como enviado en BD', [
                'presupuesto_id' => $presupuestoId,
            ]);

        } catch (\Exception $e) {
            Log::error('WhatsApp presupuesto: fallo al enviar mensaje', [
                'presupuesto_id' => $presupuestoId,
                'to' => $this->normalizePhone((string) $telefono),
                'pdf_url' => $pdfUrl,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function whatsappTestGateAllows(?string $email, ?string $telefono): bool
    {
        $allowedEmails = $this->allowedEmails();
        $allowedPhone = $this->normalizePhone((string) config('services.whatsapp.notice_phone_gate'));

        if (empty($allowedEmails) || $allowedPhone === '') {
            return false;
        }

        return in_array(strtolower(trim((string) $email)), $allowedEmails, true)
            && $this->normalizePhone((string) $telefono) === $allowedPhone;
    }

    private function allowedEmails(): array
    {
        $list = (string) config('services.whatsapp.notice_email_gate_list', '');
        $single = (string) config('services.whatsapp.notice_email_gate', '');
        $hardcoded = [
            'gracianmiguel1995@gmail.com',
            'graciancristales@hotmail.com',
        ];

        $emails = array_filter(array_map(
            static fn (string $value): string => strtolower(trim($value)),
            array_merge(
                $list !== '' ? explode(',', $list) : [],
                $single !== '' ? [$single] : [],
                $hardcoded
            )
        ));

        return array_values(array_unique($emails));
    }

    private function normalizePhone(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone) ?? '';
    }
}
