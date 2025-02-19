<?php

namespace Database\Factories;

use App\Models\Line;
use Illuminate\Database\Eloquent\Factories\Factory;

class LineFactory extends Factory
{
    protected $model = Line::class;

    public function definition()
    {
        $lineNames = [
            'Linea de Craneo',
            'Linea de Columna',
            'Linea de Neurocirugia',
            'Linea de Cirugia'
        ];

        return [
            'name' => fake()->unique()->randomElement($lineNames),
            'code' => function (array $attributes) {
                return strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $attributes['name']));
            },
            'description' => fake()->paragraph(),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Estado para Línea de Cráneo
     */
    public function craneo()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Linea de Craneo',
                'code' => 'linea_de_craneo',
                'description' => 'Línea especializada en procedimientos y equipamiento para cirugía craneal',
            ];
        });
    }

    /**
     * Estado para Línea de Columna
     */
    public function columna()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Linea de Columna',
                'code' => 'linea_de_columna',
                'description' => 'Línea especializada en procedimientos y equipamiento para cirugía de columna vertebral',
            ];
        });
    }

    /**
     * Estado para Línea de Neurocirugía
     */
    public function neurocirugia()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Linea de Neurocirugia',
                'code' => 'linea_de_neurocirugia',
                'description' => 'Línea especializada en procedimientos y equipamiento para neurocirugía',
            ];
        });
    }

    /**
     * Estado para Línea de Cirugía
     */
    public function cirugia()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Linea de Cirugia',
                'code' => 'linea_de_cirugia',
                'description' => 'Línea especializada en procedimientos y equipamiento para cirugía general',
            ];
        });
    }
}
