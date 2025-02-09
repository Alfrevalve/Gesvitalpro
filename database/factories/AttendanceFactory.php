<?php

namespace Database\Factories;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition()
    {
        return [
            'user_id' => \App\Models\User::factory(), // Asumiendo que hay una relación con User
            'created_at' => now(),
            'updated_at' => now(),
            // Agrega más campos según sea necesario
        ];
    }
}
