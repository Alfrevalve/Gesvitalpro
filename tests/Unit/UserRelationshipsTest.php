<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\User;
use App\Models\Attendance;

class UserRelationshipsTest extends TestCase
{
    protected $user;

    protected function setUp(): void
    {
        $this->user = User::factory()->create(); // Assuming you have a User factory
    }

    public function testUserHasAttendances()
    {
        $attendance = Attendance::factory()->create(['user_id' => $this->user->id]);
        
        $this->assertTrue($this->user->attendances->contains($attendance)); // Ensure the user has the attendance
    }

    // Add more tests for other relationships in User model
}
