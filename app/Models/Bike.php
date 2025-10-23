<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bike extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'nombre', 'marca', 'anio_modelo', 'kilometros', 'color'];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }


    public function revisions()
    {
        return $this->hasMany(Revision::class);
    }

    public function components()
    {
        return $this->hasMany(Component::class);
    }
    public function appointments()
    {
        return $this->hasMany(\App\Models\Appointment::class, 'bike_id');
    }
}
