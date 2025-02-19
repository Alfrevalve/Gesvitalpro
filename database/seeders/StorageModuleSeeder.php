<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Surgery;
use App\Models\SurgeryMaterial;
use App\Models\SurgeryMaterialPreparation;
use App\Models\SurgeryMaterialDelivery;
use Carbon\Carbon;

class StorageModuleSeeder extends Seeder
{
    public function run(): void
    {
        $surgeries = Surgery::whereIn('estado', ['programada', 'confirmada', 'en_preparacion'])->get();

        $materiales = [
            'Tornillos titanio' => ['tipo' => 'implante', 'unidad' => 'unidad'],
            'Placas de fijación' => ['tipo' => 'implante', 'unidad' => 'unidad'],
            'Gasas estériles' => ['tipo' => 'insumo', 'unidad' => 'paquete'],
            'Suturas absorbibles' => ['tipo' => 'insumo', 'unidad' => 'unidad'],
            'Prótesis de rodilla' => ['tipo' => 'implante', 'unidad' => 'unidad'],
            'Instrumental básico' => ['tipo' => 'instrumental', 'unidad' => 'set'],
            'Guantes quirúrgicos' => ['tipo' => 'insumo', 'unidad' => 'par'],
            'Vendas elásticas' => ['tipo' => 'insumo', 'unidad' => 'rollo']
        ];

        foreach ($surgeries as $surgery) {
            // Seleccionar 3-5 materiales aleatorios
            $selectedMateriales = array_rand($materiales, rand(3, 5));

            foreach ($selectedMateriales as $materialNombre) {
                $material = $materiales[$materialNombre];

                // Crear material de cirugía
                $surgeryMaterial = SurgeryMaterial::create([
                    'surgery_id' => $surgery->id,
                    'nombre' => $materialNombre,
                    'tipo' => $material['tipo'],
                    'cantidad_requerida' => rand(1, 5),
                    'unidad' => $material['unidad'],
                    'estado' => 'pendiente',
                    'notas' => 'Material de prueba para cirugía'
                ]);

                // Si la cirugía está en preparación, crear registro de preparación
                if ($surgery->estado === 'en_preparacion') {
                    SurgeryMaterialPreparation::create([
                        'surgery_material_id' => $surgeryMaterial->id,
                        'cantidad_preparada' => $surgeryMaterial->cantidad_requerida,
                        'estado' => 'en_proceso',
                        'notas' => 'Preparación en proceso',
                        'fecha_inicio' => now(),
                        'fecha_estimada_fin' => now()->addHours(rand(1, 4))
                    ]);
                }

                // Si la cirugía está confirmada o en preparación, crear algunos registros de entrega
                if (in_array($surgery->estado, ['confirmada', 'en_preparacion'])) {
                    SurgeryMaterialDelivery::create([
                        'surgery_material_id' => $surgeryMaterial->id,
                        'cantidad_entregada' => rand(1, $surgeryMaterial->cantidad_requerida),
                        'estado' => 'pendiente',
                        'notas' => 'Entrega programada',
                        'fecha_programada' => $surgery->fecha_programada->subHours(2)
                    ]);
                }
            }
        }

        // Crear algunos materiales específicos para pruebas
        $testSurgery = Surgery::where('estado', 'programada')->first();
        if ($testSurgery) {
            // Material completamente preparado
            $material1 = SurgeryMaterial::create([
                'surgery_id' => $testSurgery->id,
                'nombre' => 'Kit Quirúrgico Completo',
                'tipo' => 'instrumental',
                'cantidad_requerida' => 1,
                'unidad' => 'set',
                'estado' => 'preparado',
                'notas' => 'Material de prueba - Completamente preparado'
            ]);

            SurgeryMaterialPreparation::create([
                'surgery_material_id' => $material1->id,
                'cantidad_preparada' => 1,
                'estado' => 'completado',
                'notas' => 'Preparación completada',
                'fecha_inicio' => now()->subHours(2),
                'fecha_fin' => now()->subHour()
            ]);

            // Material en proceso de preparación
            $material2 = SurgeryMaterial::create([
                'surgery_id' => $testSurgery->id,
                'nombre' => 'Implantes Especiales',
                'tipo' => 'implante',
                'cantidad_requerida' => 3,
                'unidad' => 'unidad',
                'estado' => 'en_preparacion',
                'notas' => 'Material de prueba - En preparación'
            ]);

            SurgeryMaterialPreparation::create([
                'surgery_material_id' => $material2->id,
                'cantidad_preparada' => 2,
                'estado' => 'en_proceso',
                'notas' => 'Preparación en proceso',
                'fecha_inicio' => now()->subHour(),
                'fecha_estimada_fin' => now()->addHours(2)
            ]);
        }
    }
}
