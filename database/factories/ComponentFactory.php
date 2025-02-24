<?php

namespace Database\Factories;

use App\Models\Component;
use Illuminate\Database\Eloquent\Factories\Factory;

class ComponentFactory extends Factory
{
    protected $model = Component::class;

    public function definition()
    {
        return [
            'nombre' => $this->faker->word(),
            'fecha_preaviso' => $this->faker->randomDigitNotNull(),
        ];
    }
}
