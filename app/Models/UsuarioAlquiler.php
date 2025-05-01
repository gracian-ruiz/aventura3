<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuarioAlquiler extends Model
{
    use HasFactory;

    protected $table = 'usuarios_alquiler';
    protected $primaryKey = 'id';

    protected $fillable = [
        'nombre',
        'email',
        'telefono',
        'dni',
        'direccion'
    ];

    public function alquileres()
    {
        return $this->hasMany(Alquiler::class, 'usuario_id');
    }
}

