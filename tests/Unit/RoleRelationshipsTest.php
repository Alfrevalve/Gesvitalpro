<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Role;
use App\Models\User;

class RoleRelationshipsTest extends TestCase
{
    protected $role;

    protected function setUp(): void
    {
        $this->role = Role::factory()->create(); // Assuming you have a Role factory
    }

    public function testRoleHasUsers()
    {
        $user = User::factory()->create(['role_id' => $this->role->id]);
        
        $this->assertTrue($this->role->users->contains($user)); // Ensure the role has the user
    }

    // Add more tests for other relationships in Role model
}
