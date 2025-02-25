<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = ['bike_id', 'componente_id', 'prioridad', 'estado', 'tiempo_estimado'];

    public function bike()
    {
        return $this->belongsTo(Bike::class);
    }

    public function componente()
    {
        return $this->belongsTo(Component::class);
    }
}

