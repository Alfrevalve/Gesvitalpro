<?php

namespace Database\Factories;

use App\Models\Medico;
use App\Models\Institucion;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicoFactory extends Factory
{
    protected $model = Medico::class;

    public function definition()
    {
        $especialidades = [
            'Neurocirugía',
            'Cirugía de Columna',
            'Neurocirugía Pediátrica',
            'Cirugía de Cráneo',
            'Cirugía General'
        ];

        return [
            'nombre' => fake()->name(),
            'especialidad' => fake()->randomElement($especialidades),
            'email' => fake()->unique()->safeEmail(),
            'telefono' => fake()->phoneNumber(),
            'estado' => fake()->randomElement(['active', 'inactive']),
            'institucion_id' => Institucion::factory(),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Estado para médicos activos
     */
    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'estado' => 'active',
            ];
        });
    }

    /**
     * Estado para médicos inactivos
     */
    public function inactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'estado' => 'inactive',
            ];
        });
    }

    /**
     * Estado para neurocirujanos
     */
    public function neurocirugia()
    {
        return $this->state(function (array $attributes) {
            return [
                'especialidad' => 'Neurocirugía',
            ];
        });
    }

    /**
     * Estado para cirujanos de columna
     */
    public function cirugiaColumna()
    {
        return $this->state(function (array $attributes) {
            return [
                'especialidad' => 'Cirugía de Columna',
            ];
        });
    }

    /**
     * Estado para neurocirujanos pediátricos
     */
    public function neurocirugiaPediatrica()
    {
        return $this->state(function (array $attributes) {
            return [
                'especialidad' => 'Neurocirugía Pediátrica',
            ];
        });
    }

    /**
     * Estado para cirujanos de cráneo
     */
    public function cirugiaCraneo()
    {
        return $this->state(function (array $attributes) {
            return [
                'especialidad' => 'Cirugía de Cráneo',
            ];
        });
    }

    /**
     * Estado para cirujanos generales
     */
    public function cirugiaGeneral()
    {
        return $this->state(function (array $attributes) {
            return [
                'especialidad' => 'Cirugía General',
            ];
        });
    }
}
