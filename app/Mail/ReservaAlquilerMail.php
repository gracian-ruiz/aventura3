<?php

namespace App\Mail;

use App\Models\Alquiler;
use App\Models\UsuarioAlquiler;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

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
        // Log para depuración de tipos recibidos
        Log::info('Tipos de bicicleta recibidos en el mail de reserva', [
            'tipos' => collect($this->bicicletas)->pluck('tipo')->all()
        ]);

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
        // Función para normalizar el tipo
        $normalizar = function($tipo) {
            $tipo = mb_strtolower($tipo, 'UTF-8');
            $tipo = str_replace(['á','é','í','ó','ú','ñ',' '], ['a','e','i','o','u','n',''], $tipo);
            return preg_replace('/[^a-z0-9_]/', '', $tipo);
        };
        // Convertir el tipo de cada bicicleta a nombre bonito, normalizando
        $bicicletas = collect($this->bicicletas)->map(function($bici) use ($tipos, $normalizar) {
            $tipo_normalizado = $normalizar($bici['tipo']);
            $bici['tipo_bonito'] = $tipos[$tipo_normalizado] ?? $bici['tipo'];
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
