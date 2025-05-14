<?php

// app/Models/AlquilerMaterial.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlquilerMaterial extends Model
{
    use HasFactory;

    protected $table = 'alquiler_material';

    protected $fillable = [
        'alquiler_id',
        'material_id',
        'cantidad',
        'precio_unitario',
        'cantidad_dias',
        'subtotal',
        'descuento',
        'estado',
        'fecha_inicio',
        'fecha_fin',
        'reserva_precio'
        
    ];

    public function alquiler()
    {
        return $this->belongsTo(Alquiler::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}

