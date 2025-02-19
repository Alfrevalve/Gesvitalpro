<?php

namespace App\Filament\Resources\EquipmentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SurgeriesRelationManager extends RelationManager
{
    protected static string $relationship = 'surgeries';
    protected static ?string $title = 'Cirugías';
    protected static ?string $modelLabel = 'Cirugía';
    protected static ?string $pluralModelLabel = 'Cirugías';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DateTimePicker::make('surgery_date')
                    ->required()
                    ->label('Fecha de Cirugía'),
                Forms\Components\Select::make('line_id')
                    ->relationship('line', 'name')
                    ->required()
                    ->label('Línea'),
                Forms\Components\Select::make('status')
                    ->options([
                        'programmed' => 'Programada',
                        'in_progress' => 'En Progreso',
                        'completed' => 'Completada',
                        'cancelled' => 'Cancelada',
                    ])
                    ->required()
                    ->default('programmed')
                    ->label('Estado'),
                Forms\Components\TextInput::make('description')
                    ->maxLength(255)
                    ->label('Descripción'),
                Forms\Components\Select::make('staff')
                    ->multiple()
                    ->relationship('staff', 'name')
                    ->preload()
                    ->label('Personal'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('surgery_date')
            ->columns([
                Tables\Columns\TextColumn::make('surgery_date')
                    ->dateTime()
                    ->sortable()
                    ->label('Fecha de Cirugía'),
                Tables\Columns\TextColumn::make('line.name')
                    ->sortable()
                    ->label('Línea'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'primary' => 'programmed',
                        'warning' => 'in_progress',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'programmed' => 'Programada',
                        'in_progress' => 'En Progreso',
                        'completed' => 'Completada',
                        'cancelled' => 'Cancelada',
                        default => $state,
                    })
                    ->label('Estado'),
                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->label('Descripción'),
                Tables\Columns\TextColumn::make('staff_count')
                    ->counts('staff')
                    ->label('Personal'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'programmed' => 'Programada',
                        'in_progress' => 'En Progreso',
                        'completed' => 'Completada',
                        'cancelled' => 'Cancelada',
                    ])
                    ->label('Estado'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Nueva Cirugía'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Editar'),
                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Eliminar Seleccionados'),
                ]),
            ]);
    }
}
