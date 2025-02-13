<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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

    public function testAttendanceCreationFailsWithoutUserId()
    {
        $this->expectException(ModelNotFoundException::class);
        
        $data = [
            'date' => '2023-10-01',
            'status' => 'Present',
        ];
        
        $this->attendance->create($data);
    }

    public function testAttendanceCreationFailsWithFutureDate()
    {
        $data = [
            'user_id' => 1,
            'date' => '2025-10-01', // Future date
            'status' => 'Present',
        ];
        
        $this->expectException(\Exception::class); // Adjust based on your validation logic
        $this->attendance->create($data);
    }

    public function testAttendanceCreationFailsWithInvalidStatus()
    {
        $data = [
            'user_id' => 1,
            'date' => '2023-10-01',
            'status' => 'InvalidStatus', // Invalid status
        ];
        
        $this->expectException(\Exception::class); // Adjust based on your validation logic
        $this->attendance->create($data);
    }
}
