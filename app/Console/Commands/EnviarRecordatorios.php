<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\RecordatorioController;

class EnviarRecordatorios extends Command
{
    // Nombre del comando para ejecutarlo en la terminal
    protected $signature = 'recordatorios:enviar';

    // Descripción del comando
    protected $description = 'Envia recordatorios de revisiones por WhatsApp';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Instancia del controlador para ejecutar la función
        $recordatorioController = new RecordatorioController();
        $recordatorioController->enviarRecordatorios();

        $this->info('Los recordatorios de revision se han enviado correctamente.');
    }
}
