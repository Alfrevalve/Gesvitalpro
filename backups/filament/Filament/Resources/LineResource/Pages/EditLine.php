<?php

namespace App\Filament\Resources\LineResource\Pages;

use App\Filament\Resources\LineResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLine extends EditRecord
{
    protected static string $resource = LineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
