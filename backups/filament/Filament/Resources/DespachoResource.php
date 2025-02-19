<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DespachoResource\Pages;
use App\Models\DispatchProcess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DespachoResource extends Resource
{
    protected static ?string $model = DispatchProcess::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Despacho';

    protected static ?string $navigationGroup = 'Operaciones';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('codigo_envio')
                ->required()
                ->maxLength(255)
                ->label('Código de Envío'),
            Forms\Components\Select::make('estado')
                ->options([
                    'pendiente' => 'Pendiente',
                    'en_ruta' => 'En Ruta',
                    'entregado' => 'Entregado'
                ])
                ->required()
                ->label('Estado'),
            Forms\Components\DateTimePicker::make('fecha_entrega')
                ->label('Fecha de Entrega')
                ->nullable(),
            Forms\Components\Textarea::make('notas')
                ->maxLength(500)
                ->label('Notas')
                ->nullable()
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('codigo_envio')
                ->searchable()
                ->sortable()
                ->label('Código de Envío'),
            Tables\Columns\TextColumn::make('estado')
                ->searchable()
                ->sortable()
                ->label('Estado'),
            Tables\Columns\TextColumn::make('fecha_entrega')
                ->dateTime()
                ->sortable()
                ->label('Fecha de Entrega'),
            Tables\Columns\TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->label('Fecha de Creación'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListarDespacho::route('/'),
            'create' => Pages\CrearDespacho::route('/crear'),
            'edit' => Pages\EditarDespacho::route('/{record}/editar'),
        ];
    }
}
