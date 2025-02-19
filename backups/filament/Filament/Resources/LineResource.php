<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LineResource\Pages;
use App\Filament\Resources\LineResource\RelationManagers;
use App\Models\Line;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LineResource extends Resource
{
    protected static ?string $model = Line::class;
    protected static ?string $navigationIcon = 'heroicon-m-squares-2x2';
    protected static ?string $navigationGroup = 'Gestión de Líneas';
    protected static ?string $modelLabel = 'Línea';
    protected static ?string $pluralModelLabel = 'Líneas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Nombre'),
                Forms\Components\TextInput::make('description')
                    ->maxLength(255)
                    ->label('Descripción'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Nombre'),
                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->label('Descripción'),
                Tables\Columns\TextColumn::make('equipment_count')
                    ->counts('equipment')
                    ->label('Total Equipos'),
                Tables\Columns\TextColumn::make('equipment')
                    ->counts(function (Builder $query) {
                        $query->where('status', 'available');
                    })
                    ->label('Equipos Disponibles')
                    ->color('success'),
                Tables\Columns\TextColumn::make('equipment')
                    ->counts(function (Builder $query) {
                        $query->where('status', 'in_use');
                    })
                    ->label('Equipos en Uso')
                    ->color('primary'),
                Tables\Columns\TextColumn::make('equipment')
                    ->counts(function (Builder $query) {
                        $query->where('status', 'maintenance');
                    })
                    ->label('Equipos en Mantenimiento')
                    ->color('warning'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('view_equipment')
                    ->label('Ver Equipos')
                    ->icon('heroicon-m-wrench')
                    ->url(fn (Line $record) => route('filament.admin.resources.equipment.index', ['tableFilters[line][value]' => $record->id]))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\EquipmentRelationManager::class,
            RelationManagers\StaffRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLines::route('/'),
            'create' => Pages\CreateLine::route('/create'),
            'edit' => Pages\EditLine::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
