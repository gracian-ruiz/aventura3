<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alquiler extends Model
{
    use HasFactory;

    protected $table = 'alquileres';

    protected $fillable = [
        'usuario_id',
        'fecha_inicio',
        'fecha_fin',
        'total_dias',
        'total_precio',
        'descuento', // ¡Ojo! este campo tiene un typo en la migración, debería ser 'descuento'
        'estado',
        'observaciones',
        'reserva_precio',
        'web',
        'incidencia',
        'fallo',
        'notificacion',
    ];

    public function usuario()
    {
        return $this->belongsTo(UsuarioAlquiler::class, 'usuario_id');
    }

    public function materiales()
    {
        return $this->belongsToMany(Material::class, 'alquiler_material')
                    ->withPivot('id','precio_unitario', 'subtotal', 'descuento','estado','fecha_inicio','reserva_precio',
                    'fecha_fin',)
                    ->withTimestamps();
    }

    public function fotos()
    {
        return $this->hasMany(UsuarioAlquilerFoto::class, 'alquiler_id');
    }

}

