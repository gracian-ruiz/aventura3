<?php

namespace App\Mail;

use App\Models\Alquiler;
use App\Models\UsuarioAlquiler;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReservaAlquilerMail extends Mailable
{
    use Queueable, SerializesModels;

    public $alquiler;
    public $usuario;
    public $bicicletas;
    public $observaciones;
    
    public function __construct($alquiler, $usuario, $bicicletas, $observaciones)
    {
        $this->alquiler = $alquiler;
        $this->usuario = $usuario;
        $this->bicicletas = $bicicletas;
        $this->observaciones = $observaciones;
    }
    
    

    public function build()
    {
        return $this->subject('Confirmación de reserva de bicicletas')
                    /* ->cc('copias@tuempresa.com') */
                    ->view('emails.reserva') // creamos este archivo en el paso siguiente
                    ->with([
                        'alquiler' => $this->alquiler,
                        'usuario' => $this->usuario,
                    ]);
    }
}
