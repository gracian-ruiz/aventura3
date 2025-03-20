<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PresupuestoItem extends Model
{
    use HasFactory;

    protected $table = 'presupuesto_items';

    protected $fillable = [
        'componente_id',
        'presupuesto_id',
        'texto',
        'total_precio', // <- AGREGAR ESTE CAMPO
        'horas_trabajo',
    ];
    
    

    public function presupuesto()
    {
        return $this->belongsTo(Presupuesto::class, 'presupuesto_id');
    }
    public function componente()
    {
        return $this->belongsTo(Component::class, 'componente_id');
    }
}
