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
        // Mapeo de tipos a nombres bonitos
        $tipos = [
            'mtb26' => 'MTB 26',
            'mtb29' => 'MTB 29',
            'mtb29doble' => 'MTB 29 Doble',
            'electricapaseo' => 'Eléctrica Paseo',
            'electricadoble' => 'Eléctrica Doble',
            'electricarigida' => 'Eléctrica Rígida',
            'carretera' => 'Carretera',
            'paseo' => 'Paseo',
            'niños' => 'Niños',
            'ninos' => 'Niños',
            'casco' => 'Casco',
            'material' => 'Material',
            'bidones' => 'Bidones',
            'reparacion' => 'Reparación',
            'bombin' => 'Bombín',
            'kit_reparacion' => 'Kit Reparación',
        ];
        // Convertir el tipo de cada bicicleta a nombre bonito
        $bicicletas = collect($this->bicicletas)->map(function($bici) use ($tipos) {
            $bici['tipo_bonito'] = $tipos[$bici['tipo']] ?? $bici['tipo'];
            return $bici;
        });
        return $this->subject('Confirmación de reserva de bicicletas')
                    ->bcc(['aventurabikepk@gmail.com']) 
                    ->view('emails.reserva')
                    ->with([
                        'alquiler' => $this->alquiler,
                        'usuario' => $this->usuario,
                        'bicicletas' => $bicicletas,
                        'observaciones' => $this->observaciones,
                    ]);
    }
}
