<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Line;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('password'), // password por defecto para todos los usuarios
            'remember_token' => Str::random(10),
            'line_id' => Line::factory(),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Estado para administrador
     */
    public function admin()
    {
        return $this->state(function (array $attributes) {
            return [
                'email' => 'admin@gesbio.com',
                'name' => 'Administrador',
            ];
        });
    }

    /**
     * Estado para gerente
     */
    public function gerente()
    {
        return $this->state(function (array $attributes) {
            return [
                'email' => 'gerente@gesbio.com',
                'name' => 'Gerente',
            ];
        });
    }

    /**
     * Estado para jefe de línea
     */
    public function jefeLinea()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => fake()->name() . ' (Jefe de Línea)',
            ];
        });
    }

    /**
     * Estado para instrumentista
     */
    public function instrumentista()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => fake()->name() . ' (Instrumentista)',
            ];
        });
    }

    /**
     * Estado para vendedor
     */
    public function vendedor()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => fake()->name() . ' (Vendedor)',
            ];
        });
    }

    /**
     * Estado para usuario sin verificar
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }

    /**
     * Estado para usuario sin línea asignada
     */
    public function withoutLine()
    {
        return $this->state(function (array $attributes) {
            return [
                'line_id' => null,
            ];
        });
    }
}
