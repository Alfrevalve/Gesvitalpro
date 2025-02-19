<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Surgery;
use App\Models\Equipment;
use App\Models\Line;
use App\Models\Medico;
use App\Models\Institucion;
use App\Services\SurgeryScheduler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

class SurgeryManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh --seed');
    }

    /** @test */
    public function can_create_surgery()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $line = Line::factory()->create();
        $medico = Medico::factory()->create();
        $institucion = Institucion::factory()->create();
        $equipment = Equipment::factory()->count(2)->create(['line_id' => $line->id]);

        $surgeryData = [
            'line_id' => $line->id,
            'medico_id' => $medico->id,
            'institucion_id' => $institucion->id,
            'surgery_date' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'patient_name' => 'Test Patient',
            'surgery_type' => 'Test Surgery',
            'equipment' => $equipment->pluck('id')->toArray(),
            'status' => 'pending'
        ];

        $response = $this->actingAs($user)
            ->post('/admin/surgeries', $surgeryData);

        $response->assertStatus(302);
        $this->assertDatabaseHas('surgeries', [
            'patient_name' => 'Test Patient',
            'status' => 'pending'
        ]);
    }

    /** @test */
    public function validates_surgery_scheduling_rules()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $scheduler = app(SurgeryScheduler::class);

        // Intentar programar cirugía sin anticipación mínima
        $response = $this->actingAs($user)
            ->post('/admin/surgeries', [
                'surgery_date' => now()->addHour()->format('Y-m-d H:i:s')
            ]);

        $response->assertSessionHasErrors('surgery_date');

        // Intentar programar cirugía con demasiada anticipación
        $response = $this->actingAs($user)
            ->post('/admin/surgeries', [
                'surgery_date' => now()->addDays(60)->format('Y-m-d H:i:s')
            ]);

        $response->assertSessionHasErrors('surgery_date');
    }

    /** @test */
    public function checks_equipment_availability()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $equipment = Equipment::factory()->create(['status' => 'in_use']);

        $response = $this->actingAs($user)
            ->post('/admin/surgeries', [
                'equipment' => [$equipment->id]
            ]);

        $response->assertSessionHasErrors('equipment');
    }

    /** @test */
    public function handles_surgery_status_changes()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $surgery = Surgery::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($user)
            ->patch("/admin/surgeries/{$surgery->id}/status", [
                'status' => 'in_progress'
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('surgeries', [
            'id' => $surgery->id,
            'status' => 'in_progress'
        ]);
    }

    /** @test */
    public function logs_surgery_activities()
    {
        Event::fake();
        $user = User::factory()->create(['role' => 'admin']);
        $surgery = Surgery::factory()->create();

        $this->actingAs($user)
            ->patch("/admin/surgeries/{$surgery->id}", [
                'status' => 'completed'
            ]);

        $this->assertDatabaseHas('activity_logs', [
            'model_type' => Surgery::class,
            'model_id' => $surgery->id,
            'action' => 'updated'
        ]);
    }

    /** @test */
    public function handles_equipment_assignments()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $surgery = Surgery::factory()->create();
        $equipment = Equipment::factory()->create(['status' => 'available']);

        $response = $this->actingAs($user)
            ->post("/admin/surgeries/{$surgery->id}/equipment", [
                'equipment_id' => $equipment->id
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('surgery_equipment', [
            'surgery_id' => $surgery->id,
            'equipment_id' => $equipment->id
        ]);
    }

    /** @test */
    public function validates_scheduling_conflicts()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $medico = Medico::factory()->create();
        $date = now()->addDays(2);

        // Crear primera cirugía
        Surgery::factory()->create([
            'medico_id' => $medico->id,
            'surgery_date' => $date,
            'status' => 'pending'
        ]);

        // Intentar crear segunda cirugía en el mismo horario
        $response = $this->actingAs($user)
            ->post('/admin/surgeries', [
                'medico_id' => $medico->id,
                'surgery_date' => $date
            ]);

        $response->assertSessionHasErrors();
    }

    /** @test */
    public function handles_surgery_cancellation()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $surgery = Surgery::factory()->create(['status' => 'pending']);
        $equipment = Equipment::factory()->create(['status' => 'in_use']);

        $surgery->equipment()->attach($equipment);

        $response = $this->actingAs($user)
            ->delete("/admin/surgeries/{$surgery->id}");

        $response->assertStatus(302);
        $this->assertDatabaseHas('surgeries', [
            'id' => $surgery->id,
            'status' => 'cancelled'
        ]);
        $this->assertDatabaseHas('equipment', [
            'id' => $equipment->id,
            'status' => 'available'
        ]);
    }

    /** @test */
    public function enforces_surgery_permissions()
    {
        $user = User::factory()->create(['role' => 'user']);
        $surgery = Surgery::factory()->create();

        // Intentar editar sin permisos
        $response = $this->actingAs($user)
            ->patch("/admin/surgeries/{$surgery->id}");

        $response->assertStatus(403);

        // Intentar cancelar sin permisos
        $response = $this->actingAs($user)
            ->delete("/admin/surgeries/{$surgery->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function handles_bulk_operations()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $surgeries = Surgery::factory()->count(3)->create(['status' => 'pending']);

        $response = $this->actingAs($user)
            ->post('/admin/surgeries/bulk-update', [
                'ids' => $surgeries->pluck('id')->toArray(),
                'action' => 'cancel'
            ]);

        $response->assertStatus(200);
        $this->assertEquals(0, Surgery::where('status', 'pending')->count());
        $this->assertEquals(3, Surgery::where('status', 'cancelled')->count());
    }
}
