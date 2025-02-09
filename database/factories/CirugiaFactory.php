<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cirugia>
 */
class CirugiaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
        'fecha_hora' => $this->faker->dateTime(),
        'hospital' => $this->faker->company(),
        'equipo_requerido' => $this->faker->word(),
        'consumibles' => $this->faker->sentence(),
        'personal_asignado' => $this->faker->name(),
        'tiempo_estimado' => $this->faker->numberBetween(30, 120), // en minutos
        'patient_id' => \App\Models\Patient::factory(), // Asumiendo que hay una fábrica para Patient
        ];
    }
}
