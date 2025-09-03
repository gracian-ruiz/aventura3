<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class RecordatorioRevisionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $revision;

    public function __construct($revision)
    {
        $this->revision = $revision;
    }

    public function build()
    {
        $user  = optional($this->revision->bike->user)->name;
        $email = optional($this->revision->bike->user)->email;
        $bike  = optional($this->revision->bike)->nombre;
        $marca = optional($this->revision->bike)->marca;
        $comp  = optional($this->revision->componente)->nombre;

        $compDesc = optional($this->revision->componente)->descripcion;

        $fecha = Carbon::parse($this->revision->proxima_revision)
            ->timezone(config('app.timezone'))
            ->format('d/m/Y');

        return $this->subject("Recordatorio de revisión: {$comp} — {$bike}")
            ->view('emails.recordatorio_revision')
            ->with(compact('user', 'email', 'bike', 'marca', 'comp', 'fecha','compDesc'));
    }
}
