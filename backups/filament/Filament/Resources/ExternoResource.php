<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExternoResource\Pages;
use App\Models\Externo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ExternoResource extends Resource
{
    protected static ?string $model = Externo::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Externos';

    protected static ?string $navigationGroup = 'Gestión';

    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExternos::route('/'),
            'create' => Pages\CreateExterno::route('/create'),
            'edit' => Pages\EditExterno::route('/{record}/edit'),
        ];
    }
}
