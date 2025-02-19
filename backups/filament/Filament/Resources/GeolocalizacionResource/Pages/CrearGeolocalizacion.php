<?php

namespace App\Filament\Resources\GeolocalizacionResource\Pages;

use App\Filament\Resources\GeolocalizacionResource;
use Filament\Resources\Pages\CreateRecord;

class CrearGeolocalizacion extends CreateRecord
{
    protected static string $resource = GeolocalizacionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Zona creada exitosamente';
    }

    protected function getCreatedNotificationDescription(): ?string
    {
        return 'La nueva zona de geolocalización ha sido registrada en el sistema.';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data;
    }
}
