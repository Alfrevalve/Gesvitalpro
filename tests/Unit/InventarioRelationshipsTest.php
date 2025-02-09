<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Inventario;
use App\Models\User;

class InventarioRelationshipsTest extends TestCase
{
    protected $inventario;

    protected function setUp(): void
    {
        $this->inventario = Inventario::factory()->create(); // Assuming you have an Inventario factory
    }

    public function testInventarioBelongsToUser()
    {
        $user = User::factory()->create();
        $this->inventario->user_id = $user->id;
        $this->inventario->save();

        $this->assertEquals($user->id, $this->inventario->user->id); // Ensure the inventario belongs to the user
    }

    // Add more tests for other relationships in Inventario model
}
