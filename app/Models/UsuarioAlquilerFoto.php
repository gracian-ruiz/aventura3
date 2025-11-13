<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuarioAlquilerFoto extends Model
{
    use HasFactory;

    protected $table = 'usuario_alquiler_fotos';

    protected $fillable = ['alquiler_id', 'ruta', 'tipo'];

    public function alquiler()
    {
        return $this->belongsTo(Alquiler::class, 'alquiler_id');
    }
}
