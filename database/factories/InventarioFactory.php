<?php

namespace Database\Factories;

use App\Models\Inventario;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventarioFactory extends Factory
{
    protected $model = Inventario::class;

    public function definition()
    {
        return [
            'nombre' => $this->faker->word(),
            'categoria' => $this->faker->word(),
            'cantidad' => $this->faker->numberBetween(1, 100),
            'nivel_minimo' => $this->faker->numberBetween(1, 10),
            'ubicacion' => $this->faker->word(),
            'fecha_mantenimiento' => $this->faker->date(),
            // Agrega más campos según sea necesario
        ];
    }
}
