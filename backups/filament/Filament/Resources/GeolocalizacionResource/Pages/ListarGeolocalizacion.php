<?php

namespace App\Filament\Resources\GeolocalizacionResource\Pages;

use App\Filament\Resources\GeolocalizacionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListarGeolocalizacion extends ListRecords
{
    protected static string $resource = GeolocalizacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Crear Nueva Zona')
        ];
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return 'No hay zonas registradas';
    }

    protected function getTableEmptyStateDescription(): ?string
    {
        return 'Las zonas de geolocalización aparecerán aquí cuando se creen.';
    }
}
