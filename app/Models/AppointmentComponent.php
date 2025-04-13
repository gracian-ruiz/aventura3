<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentComponent extends Model
{
    use HasFactory;

    protected $table = 'appointment_component'; // Nombre exacto de la tabla

    protected $fillable = [
        'appointment_id',
        'componente_id',
        'texto',
        'total_precio',
        'horas_trabajo',
        'checked',
        'usuario_taller_id',
        'descuento'
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function component()
    {
        return $this->belongsTo(Component::class, 'componente_id');
    }
}
