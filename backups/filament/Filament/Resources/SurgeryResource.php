<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SurgeryResource\Pages\CreateSurgery;
use App\Filament\Resources\SurgeryResource\Pages\EditSurgery;
use App\Filament\Resources\SurgeryResource\Pages\ListSurgeries;
use App\Models\Surgery;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SurgeryResource extends Resource
{
    protected static ?string $model = Surgery::class;
    protected static ?string $navigationIcon = 'heroicon-o-heart';
    protected static ?string $navigationGroup = 'Gestión de Cirugías';
    protected static ?string $modelLabel = 'Cirugía';
    protected static ?string $pluralModelLabel = 'Cirugías';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('line_id')
                    ->relationship('line', 'name')
                    ->required()
                    ->label('Línea'),
                Forms\Components\Select::make('institucion_id')
                    ->relationship('institucion', 'nombre')
                    ->required()
                    ->label('Institución'),
                Forms\Components\Select::make('medico_id')
                    ->relationship('medico', 'nombre')
                    ->required()
                    ->label('Médico'),
                Forms\Components\TextInput::make('patient_name')
                    ->required()
                    ->maxLength(255)
                    ->label('Nombre del Paciente'),
                Forms\Components\TextInput::make('surgery_type')
                    ->required()
                    ->maxLength(255)
                    ->label('Tipo de Cirugía'),
                Forms\Components\DateTimePicker::make('surgery_date')
                    ->required()
                    ->label('Fecha de Cirugía'),
                Forms\Components\DateTimePicker::make('admission_date')
                    ->required()
                    ->label('Fecha de Admisión'),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pendiente',
                        'in_progress' => 'En Progreso',
                        'completed' => 'Completada',
                        'cancelled' => 'Cancelada',
                        'rescheduled' => 'Reprogramada',
                    ])
                    ->required()
                    ->label('Estado'),
                Forms\Components\Textarea::make('description')
                    ->maxLength(65535)
                    ->label('Descripción'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('line.name')
                    ->sortable()
                    ->searchable()
                    ->label('Línea'),
                Tables\Columns\TextColumn::make('institucion.nombre')
                    ->sortable()
                    ->searchable()
                    ->label('Institución'),
                Tables\Columns\TextColumn::make('medico.nombre')
                    ->sortable()
                    ->searchable()
                    ->label('Médico'),
                Tables\Columns\TextColumn::make('patient_name')
                    ->searchable()
                    ->label('Paciente'),
                Tables\Columns\TextColumn::make('surgery_type')
                    ->searchable()
                    ->label('Tipo'),
                Tables\Columns\TextColumn::make('surgery_date')
                    ->dateTime()
                    ->sortable()
                    ->label('Fecha'),
                Tables\Columns\SelectColumn::make('status')
                    ->options([
                        'pending' => 'Pendiente',
                        'in_progress' => 'En Progreso',
                        'completed' => 'Completada',
                        'cancelled' => 'Cancelada',
                        'rescheduled' => 'Reprogramada',
                    ])
                    ->sortable()
                    ->label('Estado'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('line')
                    ->relationship('line', 'name')
                    ->label('Línea'),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pendiente',
                        'in_progress' => 'En Progreso',
                        'completed' => 'Completada',
                        'cancelled' => 'Cancelada',
                        'rescheduled' => 'Reprogramada',
                    ])
                    ->label('Estado'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => ListSurgeries::route('/'),
            'create' => CreateSurgery::route('/create'),
            'edit' => EditSurgery::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }
}
