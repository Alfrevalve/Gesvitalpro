<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Attendance;
use App\Models\User;

class AttendanceRelationshipsTest extends TestCase
{
    protected $attendance;

    protected function setUp(): void
    {
        $this->attendance = Attendance::factory()->create(); // Assuming you have an Attendance factory
    }

    public function testAttendanceBelongsToUser()
    {
        $user = User::factory()->create();
        $this->attendance->user_id = $user->id;
        $this->attendance->save();

        $this->assertEquals($user->id, $this->attendance->user->id); // Ensure the attendance belongs to the user
    }

    // Add more tests for other relationships in Attendance model
}
