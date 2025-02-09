<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\ValidationService;

class ValidationServiceTest extends TestCase
{
    protected $validationService;

    protected function setUp(): void
    {
        $this->validationService = new ValidationService();
    }

    public function testValidatePersonal()
    {
        $data = []; // Add test data
        $result = $this->validationService->validatePersonal($data);
        $this->assertTrue($result); // Adjust assertion based on expected outcome
    }

    // Add more tests for other validation methods
}
