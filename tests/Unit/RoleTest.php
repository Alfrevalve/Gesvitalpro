<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Role;

class RoleTest extends TestCase
{
    protected $role;

    protected function setUp(): void
    {
        $this->role = new Role();
    }

    public function testRoleCreation()
    {
        $data = [
            'name' => 'Test Role',
            'description' => 'Test role description',
        ];
        
        $role = $this->role->create($data);
        $this->assertNotNull($role->id); // Ensure the role was created
    }

    // Add more tests for other methods in Role model
}
