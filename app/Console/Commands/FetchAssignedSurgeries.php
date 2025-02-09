<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cirugia;

class FetchAssignedSurgeries extends Command
{
    protected $signature = 'fetch:assigned-surgeries';
    protected $description = 'Fetch all assigned surgeries';

    public function handle()
    {
        $surgeries = Cirugia::all();
        $this->info('Assigned Surgeries:');
        foreach ($surgeries as $surgery) {
            $this->line("Surgery ID: {$surgery->id}, Date: {$surgery->fecha_hora}, Hospital: {$surgery->hospital}");
        }
    }
}
