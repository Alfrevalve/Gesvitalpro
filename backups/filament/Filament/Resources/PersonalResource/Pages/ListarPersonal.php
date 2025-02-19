<?php

namespace App\Filament\Resources\PersonalResource\Pages;

use App\Filament\Resources\PersonalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListarPersonal extends ListRecords
{
    protected static string $resource = PersonalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Agregar Personal')
        ];
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return 'No hay personal registrado';
    }

    protected function getTableEmptyStateDescription(): ?string
    {
        return 'Aquí aparecerán los registros del personal cuando se agreguen.';
    }
}
