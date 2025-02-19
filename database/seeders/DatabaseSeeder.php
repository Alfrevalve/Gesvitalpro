<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\Line;
use App\Models\User;
use App\Models\Equipment;
use App\Models\Institucion;
use App\Models\Medico;
use App\Models\Surgery;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Limpiar tablas existentes para evitar duplicados
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Role::truncate();
        User::truncate();
        Line::truncate();
        Equipment::truncate();
        Institucion::truncate();
        Medico::truncate();
        Surgery::truncate();
        DB::table('role_user')->truncate();
        DB::table('surgery_equipment')->truncate();
        DB::table('surgery_staff')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // 1. Crear roles básicos del sistema
        $roles = [
            'admin' => 'Administrador',
            'gerente' => 'Gerente',
            'jefe_linea' => 'Jefe de Línea',
            'instrumentista' => 'Instrumentista',
            'vendedor' => 'Vendedor',
            'storage' => 'Almacén',
            'dispatch' => 'Despacho'
        ];

        $createdRoles = [];
        foreach ($roles as $name => $display_name) {
            $createdRoles[$name] = Role::create([
                'name' => $name,
                'guard_name' => 'web'
            ]);
        }

        // 2. Crear líneas
        $lineaCraneo = Line::factory()->craneo()->create();
        $lineaColumna = Line::factory()->columna()->create();
        $lineaNeurocirugia = Line::factory()->neurocirugia()->create();
        $lineaCirugia = Line::factory()->cirugia()->create();

        // 3. Crear usuarios con roles específicos
        // Admin y Gerente (sin línea asignada)
        $admin = User::create([
            'name' => 'Administrador',
            'email' => 'admin@gesbio.com',
            'password' => Hash::make('admin123'), // Contraseña: admin123
            'email_verified_at' => now(),
        ]);
        $admin->roles()->attach($createdRoles['admin']);

        $gerente = User::create([
            'name' => 'Gerente',
            'email' => 'gerente@gesbio.com',
            'password' => Hash::make('gerente123'), // Contraseña: gerente123
            'email_verified_at' => now(),
        ]);
        $gerente->roles()->attach($createdRoles['gerente']);

        // Crear usuarios para cada línea
        $lines = [$lineaCraneo, $lineaColumna, $lineaNeurocirugia, $lineaCirugia];
        foreach ($lines as $line) {
            // Jefe de línea
            $jefeLine = User::factory()->jefeLinea()->create(['line_id' => $line->id]);
            $jefeLine->roles()->attach($createdRoles['jefe_linea']);

            // Instrumentistas (2-3 por línea)
            $instrumentistas = User::factory()->count(fake()->numberBetween(2, 3))
                ->instrumentista()
                ->create(['line_id' => $line->id]);
            foreach ($instrumentistas as $instrumentista) {
                $instrumentista->roles()->attach($createdRoles['instrumentista']);
            }

            // Vendedores (1-2 por línea)
            $vendedores = User::factory()->count(fake()->numberBetween(1, 2))
                ->vendedor()
                ->create(['line_id' => $line->id]);
            foreach ($vendedores as $vendedor) {
                $vendedor->roles()->attach($createdRoles['vendedor']);
            }
        }

        // 4. Crear equipos para cada línea
        foreach ($lines as $line) {
            // Crear 3-5 equipos por línea
            Equipment::factory()
                ->count(fake()->numberBetween(3, 5))
                ->create(['line_id' => $line->id]);
        }

        // 5. Crear instituciones y médicos
        $instituciones = Institucion::factory()
            ->count(10)
            ->create();

        foreach ($instituciones as $institucion) {
            // Crear 2-4 médicos por institución
            Medico::factory()
                ->count(fake()->numberBetween(2, 4))
                ->create(['institucion_id' => $institucion->id]);
        }

        // 6. Crear cirugías
        foreach ($lines as $line) {
            // Crear 5-10 cirugías por línea
            $surgeries = Surgery::factory()
                ->count(fake()->numberBetween(5, 10))
                ->create([
                    'line_id' => $line->id,
                    'institucion_id' => $instituciones->random()->id,
                    'medico_id' => Medico::inRandomOrder()->first()->id,
                ]);

            // Asignar equipos y personal a cada cirugía
            foreach ($surgeries as $surgery) {
                // Asignar 1-3 equipos de la línea
                $equipos = Equipment::where('line_id', $line->id)
                    ->inRandomOrder()
                    ->take(fake()->numberBetween(1, 3))
                    ->get();
                $surgery->equipment()->attach($equipos);

                // Asignar personal de la línea
                $personal = User::where('line_id', $line->id)
                    ->inRandomOrder()
                    ->take(fake()->numberBetween(2, 4))
                    ->get();
                $surgery->staff()->attach($personal);
            }
        }
    }
}
