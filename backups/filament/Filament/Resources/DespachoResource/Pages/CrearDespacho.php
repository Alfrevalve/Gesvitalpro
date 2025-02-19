<?php

namespace App\Filament\Resources\DespachoResource\Pages;

use App\Filament\Resources\DespachoResource;
use Filament\Resources\Pages\CreateRecord;

class CrearDespacho extends CreateRecord
{
    protected static string $resource = DespachoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Despacho creado exitosamente';
    }

    protected function getCreatedNotificationDescription(): ?string
    {
        return 'El registro de despacho ha sido creado y está listo para ser procesado.';
    }
}
