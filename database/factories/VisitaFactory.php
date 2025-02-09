<?php

namespace Database\Factories;

use App\Models\Visita;
use Illuminate\Database\Eloquent\Factories\Factory;

class VisitaFactory extends Factory
{
    protected $model = Visita::class;

    public function definition()
    {
        return [
            'fecha' => $this->faker->date(),
            'hora' => $this->faker->time(),
            'paciente_id' => \App\Models\Paciente::factory(), // Asumiendo que hay una relación con Paciente
            'descripcion' => $this->faker->sentence(),
            // Agrega más campos según sea necesario
        ];
    }
}
