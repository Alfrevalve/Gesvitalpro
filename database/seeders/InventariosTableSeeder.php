<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Inventario;

class InventariosTableSeeder extends Seeder
{
    public function run()
    {
        Inventario::create([
            'nombre' => 'Termómetro',
            'categoria' => 'Equipos médicos',
            'cantidad' => 50,
            'nivel_minimo' => 10,
            'ubicacion' => 'Almacén 1',
            'fecha_mantenimiento' => '2023-12-01',
        ]);

        // Agrega más registros según sea necesario
    }
}