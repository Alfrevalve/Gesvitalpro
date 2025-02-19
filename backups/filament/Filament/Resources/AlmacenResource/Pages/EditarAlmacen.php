<?php

namespace App\Filament\Resources\AlmacenResource\Pages;

use App\Filament\Resources\AlmacenResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditarAlmacen extends EditRecord
{
    protected static string $resource = AlmacenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Eliminar')
                ->modalHeading('Eliminar registro')
                ->modalDescription('¿Está seguro que desea eliminar este registro? Esta acción no se puede deshacer.')
                ->modalCancelActionLabel('Cancelar')
                ->modalSubmitActionLabel('Sí, eliminar'),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Cambios guardados exitosamente';
    }
}
