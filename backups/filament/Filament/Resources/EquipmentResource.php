<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EquipmentResource\Pages;
use App\Models\Equipment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EquipmentResource extends Resource
{
    protected static ?string $model = Equipment::class;
    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationGroup = 'Gestión de Equipos';
    protected static ?string $modelLabel = 'Equipo';
    protected static ?string $pluralModelLabel = 'Equipos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Nombre'),
                Forms\Components\TextInput::make('type')
                    ->required()
                    ->maxLength(255)
                    ->label('Tipo'),
                Forms\Components\TextInput::make('serial_number')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->label('Número de Serie'),
                Forms\Components\Select::make('line_id')
                    ->relationship('line', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Línea'),
                Forms\Components\Select::make('status')
                    ->options([
                        'available' => 'Disponible',
                        'in_use' => 'En Uso',
                        'maintenance' => 'En Mantenimiento',
                    ])
                    ->required()
                    ->default('available')
                    ->label('Estado'),
                Forms\Components\DateTimePicker::make('last_maintenance')
                    ->label('Último Mantenimiento'),
                Forms\Components\DateTimePicker::make('next_maintenance')
                    ->label('Próximo Mantenimiento')
                    ->afterOrEqual('last_maintenance'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->label('Nombre'),
                Tables\Columns\TextColumn::make('type')
                    ->searchable()
                    ->label('Tipo'),
                Tables\Columns\TextColumn::make('serial_number')
                    ->searchable()
                    ->label('Número de Serie'),
                Tables\Columns\TextColumn::make('line.name')
                    ->searchable()
                    ->sortable()
                    ->label('Línea'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'available',
                        'primary' => 'in_use',
                        'warning' => 'maintenance',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'available' => 'Disponible',
                        'in_use' => 'En Uso',
                        'maintenance' => 'En Mantenimiento',
                        default => $state,
                    })
                    ->label('Estado'),
                Tables\Columns\TextColumn::make('surgeries_count')
                    ->numeric()
                    ->sortable()
                    ->label('Cirugías'),
                Tables\Columns\TextColumn::make('last_maintenance')
                    ->dateTime()
                    ->sortable()
                    ->label('Último Mantenimiento'),
                Tables\Columns\TextColumn::make('next_maintenance')
                    ->dateTime()
                    ->sortable()
                    ->label('Próximo Mantenimiento'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'available' => 'Disponible',
                        'in_use' => 'En Uso',
                        'maintenance' => 'En Mantenimiento',
                    ])
                    ->label('Estado'),
                Tables\Filters\SelectFilter::make('line')
                    ->relationship('line', 'name')
                    ->label('Línea'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('maintenance')
                    ->label('Mantenimiento')
                    ->icon('heroicon-o-wrench')
                    ->color('warning')
                    ->action(function (Equipment $record) {
                        if ($record->status === 'maintenance') {
                            $record->completeMaintenance();
                        } else {
                            $record->scheduleMaintenance(now()->addDays(90));
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading(fn (Equipment $record) => 
                        $record->status === 'maintenance' 
                            ? 'Completar Mantenimiento' 
                            : 'Iniciar Mantenimiento'
                    )
                    ->modalDescription(fn (Equipment $record) => 
                        $record->status === 'maintenance'
                            ? '¿Está seguro de marcar este equipo como mantenimiento completado?' 
                            : '¿Está seguro de enviar este equipo a mantenimiento?'
                    ),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEquipment::route('/'),
            'create' => Pages\CreateEquipment::route('/create'),
            'edit' => Pages\EditEquipment::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'maintenance')->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }
}
