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
    public $bicicletasProcesadas;
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
        // Log 1: Array original recibido
        Log::info('🔵 INICIO - ReservaAlquilerMail build()', [
            'bicicletas_originales' => $this->bicicletas
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
        
        // Log 2: Mapeo de tipos disponibles
        Log::info('🔵 Mapeo de tipos disponibles', ['tipos' => $tipos]);
        
        // Función para normalizar el tipo
        $normalizar = function($tipo) {
            $original = $tipo;
            $tipo = mb_strtolower($tipo, 'UTF-8');
            $tipo = str_replace(['á','é','í','ó','ú','ñ',' '], ['a','e','i','o','u','n',''], $tipo);
            $tipo = preg_replace('/[^a-z0-9_]/', '', $tipo);
            Log::info('🔵 Normalizando tipo', [
                'original' => $original,
                'normalizado' => $tipo
            ]);
            return $tipo;
        };
        
        // Convertir el tipo de cada bicicleta a nombre bonito, normalizando
        $bicicletas = collect($this->bicicletas)->map(function($bici) use ($tipos, $normalizar) {
            Log::info('🔵 Procesando bicicleta', ['bici_antes' => $bici]);
            
            $tipo_normalizado = $normalizar($bici['tipo']);
            $bici['tipo_bonito'] = $tipos[$tipo_normalizado] ?? $bici['tipo'];
            
            Log::info('🔵 Bicicleta procesada', [
                'tipo_original' => $bici['tipo'],
                'tipo_normalizado' => $tipo_normalizado,
                'tipo_bonito' => $bici['tipo_bonito'],
                'bici_completa' => $bici
            ]);
            
            return $bici;
        })->toArray();
        
        // Log 3: Array final que se pasa a la vista
        Log::info('🔵 Array final de bicicletas (después de toArray)', [
            'bicicletas_finales' => $bicicletas,
            'tipo_bicicletas' => gettype($bicicletas),
            'count' => count($bicicletas)
        ]);
        
        // Asignar a propiedad pública para evitar problemas con SerializesModels
        $this->bicicletasProcesadas = $bicicletas;
        
        Log::info('🔵 Asignado a propiedad pública', [
            'bicicletasProcesadas' => $this->bicicletasProcesadas
        ]);
        
        return $this->subject('Confirmación de reserva de bicicletas')
                    ->bcc(['aventurabikepk@gmail.com']) 
                    ->view('emails.reserva2');
    }
}
