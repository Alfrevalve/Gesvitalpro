<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AlmacenResource\Pages;
use App\Models\StorageProcess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AlmacenResource extends Resource
{
    protected static ?string $model = StorageProcess::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationLabel = 'Almacén';

    protected static ?string $navigationGroup = 'Operaciones';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nombre')
                ->required()
                ->maxLength(255)
                ->label('Nombre'),
            Forms\Components\TextInput::make('descripcion')
                ->maxLength(255)
                ->label('Descripción'),
            Forms\Components\Select::make('estado')
                ->options([
                    'pendiente' => 'Pendiente',
                    'en_proceso' => 'En Proceso',
                    'completado' => 'Completado'
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
                ->label('Nombre'),
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
            'index' => Pages\ListarAlmacen::route('/'),
            'create' => Pages\CrearAlmacen::route('/crear'),
            'edit' => Pages\EditarAlmacen::route('/{record}/editar'),
        ];
    }
}
