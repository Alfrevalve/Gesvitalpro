<?php

namespace App\Filament\Resources\SurgeryResource\Pages;

use App\Filament\Resources\SurgeryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSurgery extends CreateRecord
{
    protected static string $resource = SurgeryResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
