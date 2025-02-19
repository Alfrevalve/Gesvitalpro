<?php

namespace App\Filament\Resources\GeolocalizacionResource\Pages;

use App\Filament\Resources\GeolocalizacionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditarGeolocalizacion extends EditRecord
{
    protected static string $resource = GeolocalizacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Eliminar')
                ->modalHeading('Eliminar zona')
                ->modalDescription('¿Está seguro que desea eliminar esta zona de geolocalización? Esta acción no se puede deshacer.')
                ->modalCancelActionLabel('Cancelar')
                ->modalSubmitActionLabel('Sí, eliminar'),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Zona actualizada exitosamente';
    }

    protected function getSavedNotificationDescription(): ?string
    {
        return 'Los datos de la zona de geolocalización han sido actualizados correctamente.';
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $data;
    }
}
