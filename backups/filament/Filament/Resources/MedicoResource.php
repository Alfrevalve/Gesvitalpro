<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MedicoResource\Pages;
use App\Models\Medico;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MedicoResource extends Resource
{
    protected static ?string $model = Medico::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Gestión de Visitas';
    protected static ?string $modelLabel = 'Médico';
    protected static ?string $pluralModelLabel = 'Médicos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(255)
                    ->label('Nombre'),
                Forms\Components\TextInput::make('especialidad')
                    ->required()
                    ->maxLength(255)
                    ->label('Especialidad'),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->label('Email'),
                Forms\Components\TextInput::make('telefono')
                    ->tel()
                    ->required()
                    ->maxLength(255)
                    ->label('Teléfono'),
                Forms\Components\Select::make('institucion_id')
                    ->relationship('institucion', 'nombre')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Institución'),
                Forms\Components\Select::make('estado')
                    ->options([
                        'active' => 'Activo',
                        'inactive' => 'Inactivo',
                    ])
                    ->required()
                    ->default('active')
                    ->label('Estado'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable()
                    ->sortable()
                    ->label('Nombre'),
                Tables\Columns\TextColumn::make('especialidad')
                    ->searchable()
                    ->sortable()
                    ->label('Especialidad'),
                Tables\Columns\TextColumn::make('institucion.nombre')
                    ->searchable()
                    ->sortable()
                    ->label('Institución'),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->label('Email'),
                Tables\Columns\TextColumn::make('telefono')
                    ->searchable()
                    ->label('Teléfono'),
                Tables\Columns\BadgeColumn::make('estado')
                    ->colors([
                        'success' => 'active',
                        'danger' => 'inactive',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Activo',
                        'inactive' => 'Inactivo',
                        default => $state,
                    })
                    ->label('Estado'),
                Tables\Columns\TextColumn::make('visitas_count')
                    ->counts('visitas')
                    ->label('Visitas'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'active' => 'Activo',
                        'inactive' => 'Inactivo',
                    ])
                    ->label('Estado'),
                Tables\Filters\SelectFilter::make('institucion')
                    ->relationship('institucion', 'nombre')
                    ->label('Institución'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('toggle_status')
                    ->label(fn (Medico $record): string => 
                        $record->estado === 'active' ? 'Desactivar' : 'Activar'
                    )
                    ->icon(fn (Medico $record): string => 
                        $record->estado === 'active' ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle'
                    )
                    ->color(fn (Medico $record): string => 
                        $record->estado === 'active' ? 'danger' : 'success'
                    )
                    ->action(fn (Medico $record) => 
                        $record->update(['estado' => $record->estado === 'active' ? 'inactive' : 'active'])
                    )
                    ->requiresConfirmation(),
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
            'index' => Pages\ListMedicos::route('/'),
            'create' => Pages\CreateMedico::route('/create'),
            'edit' => Pages\EditMedico::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('estado', 'active')->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'success';
    }
}
