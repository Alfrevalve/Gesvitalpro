<?php

namespace Database\Factories;

use App\Models\Institucion;
use Illuminate\Database\Eloquent\Factories\Factory;

class InstitucionFactory extends Factory
{
    protected $model = Institucion::class;

    public function definition()
    {
        $tipos = ['hospital', 'clinica'];
        $categorias = ['I-1', 'I-2', 'I-3', 'I-4', 'II-1', 'II-2', 'III-1', 'III-2'];
        $redes = [
            'Red de Salud Lima Norte',
            'Red de Salud Lima Sur',
            'Red de Salud Lima Este',
            'Red de Salud Lima Centro',
            'Red de Salud Callao'
        ];

        $latitudes = [-12.0464, -12.1464, -11.9464, -12.0564, -12.0364];
        $longitudes = [-77.0428, -77.0228, -77.0628, -77.0328, -77.0528];

        $index = rand(0, count($latitudes) - 1);

        return [
            'nombre' => fake()->company() . ' ' . fake()->randomElement(['Hospital', 'Clínica', 'Centro Médico']),
            'codigo_renipress' => fake()->unique()->numerify('R####'),
            'tipo_establecimiento' => fake()->randomElement($tipos),
            'categoria' => fake()->randomElement($categorias),
            'red_salud' => fake()->randomElement($redes),
            'latitud' => $latitudes[$index],
            'longitud' => $longitudes[$index],
            'datos_ubicacion' => [
                'distrito' => fake()->city(),
                'provincia' => 'Lima',
                'departamento' => 'Lima',
            ],
            'telefono' => fake()->phoneNumber(),
            'email' => fake()->unique()->companyEmail(),
            'direccion' => fake()->streetAddress(),
            'ciudad' => 'Lima',
            'estado' => fake()->randomElement(['activo', 'inactivo']),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Estado para hospitales
     */
    public function hospital()
    {
        return $this->state(function (array $attributes) {
            return [
                'tipo_establecimiento' => 'hospital',
                'categoria' => fake()->randomElement(['II-1', 'II-2', 'III-1', 'III-2']),
            ];
        });
    }

    /**
     * Estado para clínicas
     */
    public function clinica()
    {
        return $this->state(function (array $attributes) {
            return [
                'tipo_establecimiento' => 'clinica',
                'categoria' => fake()->randomElement(['I-1', 'I-2', 'I-3', 'I-4']),
            ];
        });
    }

    /**
     * Estado para instituciones activas
     */
    public function activo()
    {
        return $this->state(function (array $attributes) {
            return [
                'estado' => 'activo',
            ];
        });
    }

    /**
     * Estado para instituciones inactivas
     */
    public function inactivo()
    {
        return $this->state(function (array $attributes) {
            return [
                'estado' => 'inactivo',
            ];
        });
    }
}
