<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'nombre' => $this->faker->name(), // Cambiar 'name' a 'nombre'
            'email' => $this->faker->unique()->safeEmail(),
            'contrasena' => bcrypt('password'), // Cambiar 'password' a 'contrasena'
            'rol_id' => 1, // Asignar un rol existente
            'remember_token' => \Str::random(10),
        ];
    }
}