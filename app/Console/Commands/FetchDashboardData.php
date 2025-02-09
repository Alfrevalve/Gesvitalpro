<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cirugia;
use App\Models\Inventario;

class FetchDashboardData extends Command
{
    protected $signature = 'fetch:dashboard-data';
    protected $description = 'Fetch data for the dashboard';

    public function handle()
    {
        // Fetch assigned surgeries
        $surgeries = Cirugia::all();
        $this->info('Assigned Surgeries:');
        foreach ($surgeries as $surgery) {
            $this->line("Surgery ID: {$surgery->id}, Date: {$surgery->fecha_hora}, Hospital: {$surgery->hospital}, Assigned Personnel: {$surgery->personal_asignado}");
        }

        // Fetch available equipment
        $equipment = Inventario::whereColumn('cantidad', '>', 'nivel_minimo')->get();
        $this->info('Available Equipment:');
        foreach ($equipment as $item) {
            $this->line("Product: {$item->nombre}, Quantity: {$item->cantidad}, Minimum Level: {$item->nivel_minimo}");
        }
    }
}
