<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'guard_name' => 'web', // Asumiendo que se usa el guard 'web'
            // Agrega más campos según sea necesario
        ];
    }
}
