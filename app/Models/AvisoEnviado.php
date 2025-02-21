<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class AvisoEnviado extends Model
{
    use HasFactory;

    protected $table = 'avisos_enviados'; // Asegúrate de que la tabla es correcta

    protected $fillable = [
        'user_id',
        'bike_id',
        'revision_id',
        'componente_id',
        'telefono',
        'mensaje',
        'enviado_en',
    ];

    // Indica que `enviado_en` debe tratarse como un objeto Carbon (fecha)
    protected $dates = ['enviado_en'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bike()
    {
        return $this->belongsTo(Bike::class);
    }

    public function revision()
    {
        return $this->belongsTo(Revision::class);
    }

    public function componente()
    {
        return $this->belongsTo(Component::class, 'componente_id');
    }
}
