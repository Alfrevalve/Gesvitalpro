<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GeolocalizacionResource\Pages;
use App\Models\Zona;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GeolocalizacionResource extends Resource
{
    protected static ?string $model = Zona::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationLabel = 'Geolocalización';

    protected static ?string $navigationGroup = 'Operaciones';

    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nombre')
                ->required()
                ->maxLength(255)
                ->label('Nombre de la Zona'),
            Forms\Components\TextInput::make('descripcion')
                ->maxLength(255)
                ->label('Descripción'),
            Forms\Components\TextInput::make('coordenadas')
                ->required()
                ->label('Coordenadas'),
            Forms\Components\Select::make('estado')
                ->options([
                    'activa' => 'Activa',
                    'inactiva' => 'Inactiva'
                ])
                ->required()
                ->label('Estado')
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('nombre')
                ->searchable()
                ->sortable()
                ->label('Nombre de la Zona'),
            Tables\Columns\TextColumn::make('estado')
                ->searchable()
                ->sortable()
                ->label('Estado'),
            Tables\Columns\TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->label('Fecha de Creación'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListarGeolocalizacion::route('/'),
            'create' => Pages\CrearGeolocalizacion::route('/crear'),
            'edit' => Pages\EditarGeolocalizacion::route('/{record}/editar'),
        ];
    }

    public static function getModelLabel(): string
    {
        return 'Zona';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Zonas';
    }
}
