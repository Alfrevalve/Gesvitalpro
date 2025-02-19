<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition()
    {
        $roles = [
            ['name' => 'Administrador', 'slug' => 'admin'],
            ['name' => 'Gerente', 'slug' => 'gerente'],
            ['name' => 'Jefe de Línea', 'slug' => 'jefe_linea'],
            ['name' => 'Instrumentista', 'slug' => 'instrumentista'],
            ['name' => 'Vendedor', 'slug' => 'vendedor'],
        ];

        $role = fake()->unique()->randomElement($roles);

        return [
            'name' => $role['name'],
            'slug' => $role['slug'],
            'description' => fake()->sentence(),
        ];
    }

    public function admin()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Administrador',
                'slug' => 'admin',
                'description' => 'Administrador del sistema con acceso total',
            ];
        });
    }

    public function gerente()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Gerente',
                'slug' => 'gerente',
                'description' => 'Gerente con acceso a gestión y reportes generales',
            ];
        });
    }

    public function jefeLinea()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Jefe de Línea',
                'slug' => 'jefe_linea',
                'description' => 'Jefe de línea responsable de la gestión de equipos y personal',
            ];
        });
    }

    public function instrumentista()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Instrumentista',
                'slug' => 'instrumentista',
                'description' => 'Instrumentista encargado de la preparación y manejo de equipos',
            ];
        });
    }

    public function vendedor()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Vendedor',
                'slug' => 'vendedor',
                'description' => 'Vendedor encargado de la gestión comercial y relación con clientes',
            ];
        });
    }
}
