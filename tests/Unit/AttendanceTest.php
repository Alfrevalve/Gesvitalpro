<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Attendance;

class AttendanceTest extends TestCase
{
    protected $attendance;

    protected function setUp(): void
    {
        $this->attendance = new Attendance();
    }

    public function testAttendanceCreation()
    {
        $data = [
            'user_id' => 1, // Assuming a user with ID 1 exists
            'date' => '2023-10-01',
            'status' => 'Present',
        ];
        
        $attendance = $this->attendance->create($data);
        $this->assertNotNull($attendance->id); // Ensure the attendance record was created
    }

    // Add more tests for other methods in Attendance model
}
