<?php

namespace App\Filament\Resources\LineResource\Pages;

use App\Filament\Resources\LineResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLines extends ListRecords
{
    protected static string $resource = LineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Crear nueva línea')
                ->color('success')
                ->size('lg')
                ->extraAttributes([
                    'class' => 'font-bold shadow-lg hover:shadow-xl transition-all bg-success-600 hover:bg-success-500 px-6 py-2 rounded-lg'
                ]),
        ];
    }
}
