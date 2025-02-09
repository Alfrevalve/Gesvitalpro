<?php

namespace App\Imports;

use App\Models\Inventario;
use Maatwebsite\Excel\Concerns\ToModel;

class InventarioImport implements ToModel
{
    public function model(array $row)
    {
        return new Inventario([
            'nombre' => $row[0],
            'cantidad' => $row[1],
            'nivel_minimo' => $row[2],
            // Agregar más campos según el formato del archivo Excel
        ]);
    }
}
