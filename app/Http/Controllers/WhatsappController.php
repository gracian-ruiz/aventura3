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
        // Buscar el cliente
        $cliente = DB::table('users')->where('id', $clienteId)->first();
        if (!$cliente) {
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

        $presupuestoUrl = url("confirmacion/presupuesto/{$presupuestoId}?token={$presupuesto->token_presupuesto}");

        if (!$presupuesto) {
            return response()->json(['error' => 'Presupuesto no encontrado'], 404);
        }

        if (!$this->whatsappTestGateAllows($cliente->email ?? null, $cliente->telefono ?? null)) {
            Log::warning('WhatsApp presupuesto bloqueado por filtro de prueba', [
                'cliente_id' => $cliente->id ?? null,
                'email' => $cliente->email ?? null,
                'telefono' => $cliente->telefono ?? null,
            ]);

            return back()->with('error', 'El envío de prueba por WhatsApp solo está permitido para el cliente autorizado.');
        }

        // 1. GENERAR Y GUARDAR EL PDF
        $pdfPath = $this->generarPDF($presupuestoId);
        $pdfUrl = url('storage/presupuestos/' . basename($pdfPath));

        // 2. ENVIAR POR WHATSAPP
        if (!empty($cliente->telefono)) {
            // 2. ENVIAR POR WHATSAPP
            if (!empty($cliente->telefono)) {
                $mensaje = "📄 ¡Hola {$cliente->name}! Te escribo de Aventura Bike, te envío el presupuesto para arreglar tu bicicleta '{$presupuesto->bicicleta_nombre}'.\n\n"
                    . "🔗 Puedes confirmar el presupuesto aquí: {$presupuestoUrl}";

                $this->enviarMensajeWhatsApp($cliente->telefono, $mensaje, $pdfUrl, $presupuestoId);
            }

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

        return storage_path("app/$rutaAlmacenamiento");
    }

    private function enviarMensajeWhatsApp($telefono, $mensaje, $pdfUrl, $presupuestoId)
    {
        try {
            $this->whatsAppCloudApiService->sendDocumentMessage($telefono, $mensaje, $pdfUrl, basename($pdfUrl));

            DB::table('appointments')
                ->where('id', $presupuestoId)
                ->update(['presupuesto_enviado' => true]);

        } catch (\Exception $e) {
            Log::error("Error al enviar mensaje de WhatsApp: " . $e->getMessage());
        }
    }

    private function whatsappTestGateAllows(?string $email, ?string $telefono): bool
    {
        $allowedEmail = trim((string) config('services.whatsapp.notice_email_gate'));
        $allowedPhone = $this->normalizePhone((string) config('services.whatsapp.notice_phone_gate'));

        if ($allowedEmail === '' || $allowedPhone === '') {
            return false;
        }

        return strtolower(trim((string) $email)) === strtolower($allowedEmail)
            && $this->normalizePhone((string) $telefono) === $allowedPhone;
    }

    private function normalizePhone(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone) ?? '';
    }
}
