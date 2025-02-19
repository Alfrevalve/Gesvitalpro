<?php

namespace App\Filament\Resources\DespachoResource\Pages;

use App\Filament\Resources\DespachoResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListarDespacho extends ListRecords
{
    protected static string $resource = DespachoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Crear Nuevo')
        ];
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return 'No hay registros de despacho';
    }

    protected function getTableEmptyStateDescription(): ?string
    {
        return 'Los registros de despacho aparecerán aquí cuando se creen.';
    }
}
