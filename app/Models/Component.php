<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Component extends Model // <-- Aquí debe ser el nombre correcto
{
    use HasFactory;

    protected $fillable = ['nombre', 'fecha_preaviso'];

    public function revisiones()
    {
        return $this->hasMany(Revision::class);
    }
}

