<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VisitaResource\Pages;
use App\Models\Visita;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VisitaResource extends Resource
{
    protected static ?string $model = Visita::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationGroup = 'Gestión de Visitas';
    protected static ?string $modelLabel = 'Visita';
    protected static ?string $pluralModelLabel = 'Visitas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DateTimePicker::make('fecha_hora')
                    ->required()
                    ->label('Fecha y Hora'),
                Forms\Components\Select::make('institucion_id')
                    ->relationship('institucion', 'nombre')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Institución'),
                Forms\Components\Select::make('medico_id')
                    ->relationship('medico', 'nombre')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Médico'),
                Forms\Components\TextInput::make('motivo')
                    ->required()
                    ->maxLength(255)
                    ->label('Motivo'),
                Forms\Components\Textarea::make('observaciones')
                    ->maxLength(65535)
                    ->columnSpanFull()
                    ->label('Observaciones'),
                Forms\Components\Select::make('estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'completada' => 'Completada',
                        'cancelada' => 'Cancelada',
                    ])
                    ->required()
                    ->default('pendiente')
                    ->label('Estado'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('fecha_hora')
                    ->dateTime()
                    ->sortable()
                    ->label('Fecha y Hora'),
                Tables\Columns\TextColumn::make('institucion.nombre')
                    ->searchable()
                    ->sortable()
                    ->label('Institución'),
                Tables\Columns\TextColumn::make('medico.nombre')
                    ->searchable()
                    ->sortable()
                    ->label('Médico'),
                Tables\Columns\TextColumn::make('motivo')
                    ->searchable()
                    ->label('Motivo'),
                Tables\Columns\BadgeColumn::make('estado')
                    ->colors([
                        'warning' => 'pendiente',
                        'success' => 'completada',
                        'danger' => 'cancelada',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pendiente' => 'Pendiente',
                        'completada' => 'Completada',
                        'cancelada' => 'Cancelada',
                        default => $state,
                    })
                    ->label('Estado'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'completada' => 'Completada',
                        'cancelada' => 'Cancelada',
                    ])
                    ->label('Estado'),
                Tables\Filters\SelectFilter::make('institucion')
                    ->relationship('institucion', 'nombre')
                    ->label('Institución'),
                Tables\Filters\SelectFilter::make('medico')
                    ->relationship('medico', 'nombre')
                    ->label('Médico'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('completar')
                    ->label('Completar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(fn (Visita $record) => $record->completar())
                    ->requiresConfirmation()
                    ->visible(fn (Visita $record): bool => $record->estado === 'pendiente'),
                Tables\Actions\Action::make('cancelar')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->action(fn (Visita $record) => $record->cancelar())
                    ->requiresConfirmation()
                    ->visible(fn (Visita $record): bool => $record->estado === 'pendiente'),
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
            'index' => Pages\ListVisitas::route('/'),
            'create' => Pages\CreateVisita::route('/create'),
            'edit' => Pages\EditVisita::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('estado', 'pendiente')->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }
}
