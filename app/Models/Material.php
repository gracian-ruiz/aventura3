<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'tipo',
        'nombre',
        'talla',
        'stock',
        'stock_disponible',
        'estado',
        'descripcion',
        'categoria',
        'observaciones',
        'precio_dia',
    ];

    public function alquileres()
    {
        return $this->belongsToMany(Alquiler::class, 'alquiler_material')
                    ->withPivot('precio_unitario', 'cantidad_dias', 'subtotal')
                    ->withTimestamps();
    }
}
