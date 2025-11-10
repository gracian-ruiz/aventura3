<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Appointment extends Model
{
    use HasFactory;

    protected $table = 'appointments';

    protected $casts = [
        'asignacion_taller' => 'array',
    ];

    protected $fillable = [
        'bike_id', 
        'user_id',
        'presupuesto_id',
        'prioridad', 
        'estado', 
        'tiempo_estimado', 
        'descripcion_problema', 
        'estimacion_reparacion', 
        'fecha_asignada',
        'usuario_taller_id',
        'checked',
        'token_presupuesto',
        'presupuesto_enviado',
        'reparacion_enviado',
        'horas_total',
        'precio_total',
        'descuento',
        'asignacion_taller',
        'idprograma',
        'calendario',
        'tiempo_reparacion',
        'fecha_fija',
        'descripcion_cliente' 
    ];


    public function bike()
    {
        return $this->belongsTo(Bike::class, 'bike_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function componentes(): BelongsToMany
    {
        return $this->belongsToMany(Component::class, 'appointment_component', 'appointment_id', 'componente_id')
            ->withPivot('horas_trabajo', 'total_precio', 'texto','checked','usuario_taller_id');
    }

    public function scopeBuscar($query, $search)
    {
        if (!$search) return $query;

        return $query->where(function ($q) use ($search) {
            $q->whereHas('bike', function ($q2) use ($search) {
                $q2->where('nombre', 'like', "%{$search}%")
                ->orWhere('marca', 'like', "%{$search}%")
                ->orWhereHas('user', function ($qq) use ($search) {
                        $qq->where('name', 'like', "%{$search}%");
                });
            })
            ->orWhere('idprograma', 'like', "%{$search}%");
        });
    }


}
