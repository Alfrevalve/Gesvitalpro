<?php

namespace App\Services;

use App\Models\Visita;
use App\Models\Cirugia;
use App\Models\Inventario;

class ReportService
{
    public function generateVisitsReport($perPage = 10)
    {
        return Visita::with('relacion')->paginate($perPage); // Implementar eager loading y paginación
    }

    public function generateUserReport($perPage = 10)
    {
        return User::with('role')->paginate($perPage); // Implementar eager loading y paginación
    }

    public function exportToCSV($data)
    {
        // Implementar lógica para exportar datos a CSV
    }

    public function exportToPDF($data)
    {
        // Implementar lógica para exportar datos a PDF
    }

    public function generateInventoryReport($perPage = 10)
    {
        return Inventario::with('relacion')->paginate($perPage); // Implementar eager loading y paginación
    }

    public function generateSurgeriesReport($perPage = 10)
    {
        return Cirugia::with('relacion')->paginate($perPage); // Implementar eager loading y paginación
    }
}
