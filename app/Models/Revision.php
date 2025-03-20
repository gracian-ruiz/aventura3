<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Component; // 👈 Verificar este nombre

class Revision extends Model
{
    use HasFactory;

    protected $fillable = ['bike_id', 'componente_id', 'fecha_revision', 'descripcion', 'proxima_revision'];
    protected $table = 'revisions';

    public function bike()
    {
        return $this->belongsTo(Bike::class, 'bike_id');
    }

    public function componente()
    {
        return $this->belongsTo(Component::class, 'componente_id');
    }
}

