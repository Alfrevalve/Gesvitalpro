<?php

namespace Database\Factories;

use App\Models\Paciente;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    protected $model = Paciente::class;

    public function definition()
    {
        return [
            'nombre' => $this->faker->name(),
            'apellido' => $this->faker->lastName(),
            'institucion' => $this->faker->company(),
            'email' => $this->faker->unique()->safeEmail(),
            'creado_en' => now(),
            'actualizado_en' => now(),
        ];
    }
}
