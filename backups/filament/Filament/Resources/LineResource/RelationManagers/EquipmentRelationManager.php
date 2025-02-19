<?php

namespace App\Filament\Resources\LineResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class EquipmentRelationManager extends RelationManager
{
    protected static string $relationship = 'equipment';
    protected static ?string $title = 'Equipos';
    protected static ?string $modelLabel = 'Equipo';
    protected static ?string $pluralModelLabel = 'Equipos';

    public function form(Form $form): Form
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
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
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Nuevo Equipo'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('maintenance')
                    ->label('Mantenimiento')
                    ->icon('heroicon-o-wrench')
                    ->color('warning')
                    ->action(function ($record) {
                        if ($record->status === 'maintenance') {
                            $record->completeMaintenance();
                        } else {
                            $record->scheduleMaintenance(now()->addDays(90));
                        }
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
