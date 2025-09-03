<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class PresupuestoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $presupuesto;
    public $items;

    /**
     * Crear nueva instancia
     */
    public function __construct($presupuesto, $items)
    {
        $this->presupuesto = $presupuesto;
        $this->items = $items;
    }

    /**
     * Construcción del mensaje
     */
    public function build()
    {
        // URL de confirmación con token
        $presupuestoId  = $this->presupuesto->id;
        $presupuestoUrl = url("confirmacion/presupuesto/{$presupuestoId}?token={$this->presupuesto->token_presupuesto}");

        // Mensaje base (puedes ajustarlo)
        $mensaje = "📄 ¡Hola {$this->presupuesto->usuario_nombre}! Te escribo de Aventura Bike, te envío el presupuesto para arreglar tu bicicleta '{$this->presupuesto->bicicleta_nombre}'.\n\n"
            . "📎 Adjuntamos el PDF con los detalles.\n\n"
            . "🔗 Puedes confirmar el presupuesto pinchando aquí: si no estás de acuerdo, dime qué quieres que hagamos y te mando nuevo presupuesto. Gracias: {$presupuestoUrl}";

        // Generar PDF con la misma vista que usas en descargarPDF
        $pdf = Pdf::loadView('pdf.presupuesto', [
            'presupuesto' => $this->presupuesto,
            'items'       => $this->items
        ]);

        // Nombre de archivo amigable
        $limpiar = fn($t) => preg_replace('/[^A-Za-z0-9_\-]/', '_', (string) $t);
        $usuarioLimpio   = $limpiar($this->presupuesto->usuario_nombre ?? 'cliente');
        $bicicletaLimpia = $limpiar($this->presupuesto->bicicleta_nombre ?? 'bicicleta');
        $fecha           = date('Y-m-d', strtotime($this->presupuesto->created_at ?? now()));
        $nombreArchivo   = "Presupuesto_{$usuarioLimpio}_{$bicicletaLimpia}_{$fecha}.pdf";

        return $this
            // ->from('presupuestos@aventurabike.com', 'Aventura Bike') // opcional si quieres sobrescribir el remitente
            // ->cc('taller@aventurabike.com')                           // opcional: copia
            // ->bcc('admin@aventurabike.com')                           // opcional: copia oculta
            ->subject("Presupuesto #{$this->presupuesto->id} - {$this->presupuesto->bicicleta_nombre}")
            ->view('emails.presupuesto')
            ->with([
                'presupuesto'    => $this->presupuesto,
                'items'          => $this->items,
                'mensaje'        => $mensaje,
                'presupuestoUrl' => $presupuestoUrl,
            ])
            ->attachData(
                $pdf->output(),
                $nombreArchivo,
                ['mime' => 'application/pdf']
            );
    }
}
