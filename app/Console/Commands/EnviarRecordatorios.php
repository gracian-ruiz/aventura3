<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\EnviarCorreosController;
class EnviarRecordatorios extends Command
{
    /**
     * Nombre del comando para ejecutarlo en la terminal
     */
    protected $signature = 'recordatorios:enviar';

    /**
     * Descripción del comando (actualizada a email si ya migraste)
     */
    protected $description = 'Envía recordatorios de revisiones por email';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $recordatorioController = new EnviarCorreosController();
        $recordatorioController->enviarRecordatorios();

        $this->info('Los recordatorios de revisión se han gestionado correctamente.');
        return self::SUCCESS;
    }
}
