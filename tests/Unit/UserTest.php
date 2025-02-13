<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserTest extends TestCase
{
    public function testUserCreation()
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ];
        
        $user = User::create($data);
        $this->assertNotNull($user->id); // Ensure the user was created
    }

    public function testUserCreationFailsWithoutEmail()
    {
        $this->expectException(ModelNotFoundException::class);
        
        $data = [
            'name' => 'Test User',
            'password' => bcrypt('password123'),
        ];
        
        User::create($data);
    }

    public function testUserCreationFailsWithDuplicateEmail()
    {
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->expectException(\Exception::class); // Adjust based on your validation logic
        
        User::create([
            'name' => 'Another User',
            'email' => 'test@example.com',
            'password' => bcrypt('password456'),
        ]);
    }
}
