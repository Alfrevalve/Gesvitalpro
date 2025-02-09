<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Inventario;

class FetchAvailableEquipment extends Command
{
    protected $signature = 'fetch:available-equipment';
    protected $description = 'Fetch all available equipment';

    public function handle()
    {
        $equipment = Inventario::whereColumn('cantidad', '>', 'nivel_minimo')->get();
        $this->info('Available Equipment:');
        foreach ($equipment as $item) {
            $this->line("Product: {$item->nombre}, Quantity: {$item->cantidad}, Minimum Level: {$item->nivel_minimo}");
        }
    }
}
