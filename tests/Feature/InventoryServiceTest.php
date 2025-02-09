<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\InventoryService;
use App\Services\ReportService;
use App\Models\Inventario;
use App\Models\Visita;
use App\Models\Cirugia;

class InventoryServiceTest extends TestCase
{
    protected $inventoryService;
    protected $reportService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->inventoryService = new InventoryService();
        $this->reportService = new ReportService();
    }

    public function testCheckCriticalLevels()
    {
        // Assuming there are some items in the database
        $criticalItems = $this->inventoryService->checkCriticalLevels();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $criticalItems);
    }

    public function testUpdateInventory()
    {
        $inventario = Inventario::factory()->create(['cantidad' => 10]);
        $this->inventoryService->updateInventory($inventario->id, 5);
        $this->assertEquals(5, $inventario->fresh()->cantidad);
    }

    public function testGenerateVisitsReport()
    {
        $visits = $this->reportService->generateVisitsReport();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $visits);
    }

    public function testGenerateInventoryReport()
    {
        $inventory = $this->reportService->generateInventoryReport();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $inventory);
    }

    public function testGenerateSurgeriesReport()
    {
        $surgeries = $this->reportService->generateSurgeriesReport();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $surgeries);
    }
}
