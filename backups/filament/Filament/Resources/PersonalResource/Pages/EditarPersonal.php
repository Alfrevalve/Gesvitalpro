<?php

namespace App\Filament\Resources\PersonalResource\Pages;

use App\Filament\Resources\PersonalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditarPersonal extends EditRecord
{
    protected static string $resource = PersonalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Eliminar')
                ->modalHeading('Eliminar personal')
                ->modalDescription('¿Está seguro que desea eliminar este miembro del personal? Esta acción no se puede deshacer.')
                ->modalCancelActionLabel('Cancelar')
                ->modalSubmitActionLabel('Sí, eliminar'),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Información actualizada exitosamente';
    }

    protected function getSavedNotificationDescription(): ?string
    {
        return 'Los datos del personal han sido actualizados correctamente.';
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['password']) && $data['password']) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        return $data;
    }
}
