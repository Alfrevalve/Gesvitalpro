<?php

namespace App\Filament\Resources\AlmacenResource\Pages;

use App\Filament\Resources\AlmacenResource;
use Filament\Resources\Pages\CreateRecord;

class CrearAlmacen extends CreateRecord
{
    protected static string $resource = AlmacenResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Registro creado exitosamente';
    }
}
