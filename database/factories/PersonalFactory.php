<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Personal>
 */
class PersonalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
        'nombre' => $this->faker->name(),
        'cargo' => $this->faker->word(),
        'especialidad' => $this->faker->word(),
        'contacto' => $this->faker->phoneNumber(),
        'cirugias_asignadas' => $this->faker->sentence(),
        ];
    }
}
