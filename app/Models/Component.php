<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Component extends Model // <-- Aquí debe ser el nombre correcto
{
    use HasFactory;

    protected $fillable = ['nombre', 'fecha_preaviso', 'fecha_revision', 'hora_taller', 'precio','orden','descripcion'];
    protected $table = 'components';

    public function revisiones()
    {
        return $this->hasMany(Revision::class, 'componente_id'); // 🔥 Asegúrate de especificar la clave foránea correcta
    }

    public function bike()
    {
        return $this->belongsTo(Bike::class, 'bike_id');
    }
    public function appointments()
    {
        return $this->belongsToMany(Appointment::class, 'appointment_component', 'component_id', 'appointment_id');
    }
}
