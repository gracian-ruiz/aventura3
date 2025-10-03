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
        'tiempo_reparacion' 
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

    // Calcular la fecha en que se debe atender la cita
    public static function asignarFechas()
    {
        $horas_diarias = [
            1 => 7, // Lunes
            2 => 7, // Martes
            3 => 7, // Miércoles
            4 => 7, // Jueves
            5 => 7, // Viernes
            6 => 4, // Sábado
            7 => 0  // Domingo (cerrado)
        ];

        $fecha_actual = Carbon::now();
        $citas_pendientes = self::where('estado', 'pendiente')
            ->orderByRaw("CASE WHEN prioridad = 'urgente' THEN 1 ELSE 2 END")
            ->orderBy('created_at', 'asc')
            ->get();

        $horas_disponibles = 0;
        while ($horas_diarias[$fecha_actual->dayOfWeek] === 0) {
            $fecha_actual->addDay();
        }

        foreach ($citas_pendientes as $cita) {
            $horas_necesarias = ceil($cita->tiempo_estimado / 60);

            if ($horas_disponibles < $horas_necesarias) {
                do {
                    $fecha_actual->addDay();
                } while ($horas_diarias[$fecha_actual->dayOfWeek] === 0);

                $horas_disponibles = $horas_diarias[$fecha_actual->dayOfWeek];
            }

            $cita->fecha_asignada = $fecha_actual->toDateString();
            $cita->save();

            $horas_disponibles -= $horas_necesarias;
        }
    }
}
