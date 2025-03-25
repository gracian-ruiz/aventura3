<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presupuesto extends Model
{
    use HasFactory;

    protected $table = 'presupuestos';

    protected $fillable = [
        'user_id',
        'bike_id',
        'horas_total',
        'precio_total',
        'estado',
        'token_presupuesto',
        'mensaje_enviado'
    ];

    public function items()
    {
        return $this->hasMany(PresupuestoItem::class, 'presupuesto_id');
    }

    public function bike()
    {
        return $this->belongsTo(Bike::class, 'bike_id');
    }
}
