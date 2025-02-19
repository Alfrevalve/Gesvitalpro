<?php

namespace Database\Factories;

use App\Models\Equipment;
use App\Models\Line;
use Illuminate\Database\Eloquent\Factories\Factory;

class EquipmentFactory extends Factory
{
    protected $model = Equipment::class;

    public function definition()
    {
        $equipmentTypes = [
            'Linea de Craneo' => [
                'Craneotomo Azul',
                'Craneotomo Verde',
                'Craneotomo Rojo',
                'Craneotomo Blanco',
                'Craneotomo Morado',
                'Craneotomo Mogro',
                'Craneotomo Cayetano'
            ],
            'Linea de Columna' => [
                'Set de Instrumentación Vertebral',
                'Cajas Intersomáticas',
                'Sistema de Fijación Pedicular',
                'Instrumental de Microdiscectomía'
            ],
            'Linea de Neurocirugia' => [
                'Programador de Valvulas Manual',
                'Programador de Valvulas Electronico'
            ],
            'Linea de Cirugia' => [
                'Set de Cirugía General',
                'Sistema de Laparoscopía',
                'Instrumental Quirúrgico Básico',
                'Equipos de Esterilización'
            ]
        ];

        $line = Line::inRandomOrder()->first();
        $equipmentName = fake()->randomElement($equipmentTypes[$line->name]);
        $statuses = ['available', 'in_use', 'maintenance'];

        return [
            'name' => $equipmentName,
            'code' => function (array $attributes) {
                return strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $attributes['name'])) . '_' . strtolower(fake()->bothify('??##'));
            },
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement($statuses),
            'line_id' => $line->id,
            'serial_number' => strtoupper(fake()->bothify('EQ-####-????')),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Estado para equipos disponibles
     */
    public function available()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'available',
            ];
        });
    }

    /**
     * Estado para equipos en uso
     */
    public function inUse()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'in_use',
            ];
        });
    }

    /**
     * Estado para equipos en mantenimiento
     */
    public function maintenance()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'maintenance',
            ];
        });
    }

    /**
     * Estados específicos para cada tipo de Craneotomo
     */
    public function craneotomoAzul()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Craneotomo Azul',
                'code' => 'craneotomo_azul',
                'description' => 'Craneotomo de alta precisión - Serie Azul',
            ];
        });
    }

    public function craneotomoVerde()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Craneotomo Verde',
                'code' => 'craneotomo_verde',
                'description' => 'Craneotomo de alta precisión - Serie Verde',
            ];
        });
    }

    public function craneotomoRojo()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Craneotomo Rojo',
                'code' => 'craneotomo_rojo',
                'description' => 'Craneotomo de alta precisión - Serie Rojo',
            ];
        });
    }

    public function craneotomoBlanco()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Craneotomo Blanco',
                'code' => 'craneotomo_blanco',
                'description' => 'Craneotomo de alta precisión - Serie Blanco',
            ];
        });
    }

    public function craneotomoMorado()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Craneotomo Morado',
                'code' => 'craneotomo_morado',
                'description' => 'Craneotomo de alta precisión - Serie Morado',
            ];
        });
    }

    public function craneotomoMogro()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Craneotomo Mogro',
                'code' => 'craneotomo_mogro',
                'description' => 'Craneotomo de alta precisión - Serie Mogro',
            ];
        });
    }

    public function craneotomoCayetano()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Craneotomo Cayetano',
                'code' => 'craneotomo_cayetano',
                'description' => 'Craneotomo de alta precisión - Serie Cayetano',
            ];
        });
    }
}
