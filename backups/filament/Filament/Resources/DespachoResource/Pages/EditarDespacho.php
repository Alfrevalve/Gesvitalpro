<?php

namespace App\Filament\Resources\DespachoResource\Pages;

use App\Filament\Resources\DespachoResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditarDespacho extends EditRecord
{
    protected static string $resource = DespachoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Eliminar')
                ->modalHeading('Eliminar despacho')
                ->modalDescription('¿Está seguro que desea eliminar este registro de despacho? Esta acción no se puede deshacer.')
                ->modalCancelActionLabel('Cancelar')
                ->modalSubmitActionLabel('Sí, eliminar'),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Despacho actualizado exitosamente';
    }

    protected function getSavedNotificationDescription(): ?string
    {
        return 'Los cambios en el registro de despacho han sido guardados.';
    }
}
