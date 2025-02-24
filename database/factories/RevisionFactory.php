<?php

namespace Database\Factories;

use App\Models\Revision;
use App\Models\Bike;
use App\Models\Component;
use Illuminate\Database\Eloquent\Factories\Factory;

class RevisionFactory extends Factory
{
    protected $model = Revision::class;

    public function definition()
    {
        return [
            'bike_id' => Bike::factory(),
            'componente_id' => Component::factory(),
            'fecha_revision' => $this->faker->date(),
            'descripcion' => $this->faker->sentence(),
            'proxima_revision' => $this->faker->date(),
        ];
    }
}
