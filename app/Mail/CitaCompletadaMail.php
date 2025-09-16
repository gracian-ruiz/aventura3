<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CitaCompletadaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $mensaje;

    public function __construct($mensaje)
    {
        $this->mensaje = $mensaje;
    }

    public function build()
    {
        return $this->subject('✅ Tu bicicleta ya está lista')
                    ->view('emails.cita_completada')
                    ->with(['mensaje' => $this->mensaje]);
    }
}
