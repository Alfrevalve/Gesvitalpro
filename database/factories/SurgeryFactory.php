<?php

namespace Database\Factories;

use App\Models\Surgery;
use App\Models\Line;
use App\Models\Institucion;
use App\Models\Medico;
use Illuminate\Database\Eloquent\Factories\Factory;

class SurgeryFactory extends Factory
{
    protected $model = Surgery::class;

    public function definition()
    {
        $surgeryTypes = [
            'Craneotomía',
            'Cirugía de Válvula',
            'Cirugía de Columna',
            'Neurocirugía',
            'Cirugía General'
        ];

        $surgeryDate = fake()->dateTimeBetween('now', '+3 months');
        $admissionDate = fake()->dateTimeBetween('-1 day', $surgeryDate);

        return [
            'line_id' => Line::factory(),
            'institucion_id' => Institucion::factory(),
            'medico_id' => Medico::factory(),
            'patient_name' => fake()->name(),
            'surgery_type' => fake()->randomElement($surgeryTypes),
            'surgery_date' => $surgeryDate,
            'admission_date' => $admissionDate,
            'description' => fake()->paragraph(),
            'notes' => fake()->optional()->text(),
            'status' => fake()->randomElement([
                Surgery::STATUS_PENDING,
                Surgery::STATUS_IN_PROGRESS,
                Surgery::STATUS_COMPLETED,
                Surgery::STATUS_CANCELLED,
                Surgery::STATUS_RESCHEDULED
            ]),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Estado para cirugías pendientes
     */
    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Surgery::STATUS_PENDING,
            ];
        });
    }

    /**
     * Estado para cirugías en progreso
     */
    public function inProgress()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Surgery::STATUS_IN_PROGRESS,
            ];
        });
    }

    /**
     * Estado para cirugías completadas
     */
    public function completed()
    {
        return $this->state(function (array $attributes) {
            $completionDate = fake()->dateTimeBetween('-1 month', 'now');
            $admissionDate = fake()->dateTimeBetween('-2 days', $completionDate);
            return [
                'status' => Surgery::STATUS_COMPLETED,
                'surgery_date' => $completionDate,
                'admission_date' => $admissionDate,
            ];
        });
    }

    /**
     * Estado para cirugías canceladas
     */
    public function cancelled()
    {
        return $this->state(function (array $attributes) {
            $cancelDate = fake()->dateTimeBetween('-1 month', '+1 month');
            $admissionDate = fake()->dateTimeBetween('-2 days', $cancelDate);
            return [
                'status' => Surgery::STATUS_CANCELLED,
                'surgery_date' => $cancelDate,
                'admission_date' => $admissionDate,
                'notes' => 'Cirugía cancelada: ' . fake()->sentence(),
            ];
        });
    }

    /**
     * Estado para cirugías reprogramadas
     */
    public function rescheduled()
    {
        return $this->state(function (array $attributes) {
            $originalDate = fake()->dateTimeBetween('-1 month', 'now');
            $newDate = fake()->dateTimeBetween('now', '+2 months');
            $admissionDate = fake()->dateTimeBetween('-2 days', $newDate);
            return [
                'status' => Surgery::STATUS_RESCHEDULED,
                'surgery_date' => $newDate,
                'admission_date' => $admissionDate,
                'notes' => sprintf(
                    'Cirugía reprogramada: Fecha original %s. %s',
                    $originalDate->format('Y-m-d'),
                    fake()->sentence()
                ),
            ];
        });
    }

    /**
     * Estado para cirugías de craneotomía
     */
    public function craneotomia()
    {
        return $this->state(function (array $attributes) {
            return [
                'surgery_type' => 'Craneotomía',
                'description' => 'Procedimiento de craneotomía con ' . fake()->randomElement([
                    'Craneotomo Azul',
                    'Craneotomo Verde',
                    'Craneotomo Rojo',
                    'Craneotomo Blanco',
                    'Craneotomo Morado',
                    'Craneotomo Mogro',
                    'Craneotomo Cayetano'
                ]),
            ];
        });
    }

    /**
     * Estado para cirugías de válvula
     */
    public function valvula()
    {
        return $this->state(function (array $attributes) {
            return [
                'surgery_type' => 'Cirugía de Válvula',
                'description' => 'Procedimiento de válvula utilizando ' . fake()->randomElement([
                    'Programador de Valvulas Manual',
                    'Programador de Valvulas Electronico'
                ]),
            ];
        });
    }
}
