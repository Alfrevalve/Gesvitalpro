<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InstitucionResource\Pages;
use App\Models\Institucion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InstitucionResource extends Resource
{
    protected static ?string $model = Institucion::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Gestión de Visitas';
    protected static ?string $modelLabel = 'Institución';
    protected static ?string $pluralModelLabel = 'Instituciones';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(255)
                    ->label('Nombre'),
                Forms\Components\TextInput::make('direccion')
                    ->required()
                    ->maxLength(255)
                    ->label('Dirección'),
                Forms\Components\TextInput::make('ciudad')
                    ->required()
                    ->maxLength(255)
                    ->label('Ciudad'),
                Forms\Components\TextInput::make('telefono')
                    ->tel()
                    ->required()
                    ->maxLength(255)
                    ->label('Teléfono'),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->label('Email'),
                Forms\Components\Select::make('estado')
                    ->options([
                        'active' => 'Activa',
                        'inactive' => 'Inactiva',
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
                Tables\Columns\TextColumn::make('ciudad')
                    ->searchable()
                    ->sortable()
                    ->label('Ciudad'),
                Tables\Columns\TextColumn::make('telefono')
                    ->searchable()
                    ->label('Teléfono'),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->label('Email'),
                Tables\Columns\BadgeColumn::make('estado')
                    ->colors([
                        'success' => 'active',
                        'danger' => 'inactive',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Activa',
                        'inactive' => 'Inactiva',
                        default => $state,
                    })
                    ->label('Estado'),
                Tables\Columns\TextColumn::make('visitas_count')
                    ->counts('visitas')
                    ->label('Visitas'),
                Tables\Columns\TextColumn::make('medicos_count')
                    ->counts('medicos')
                    ->label('Médicos'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'active' => 'Activa',
                        'inactive' => 'Inactiva',
                    ])
                    ->label('Estado'),
                Tables\Filters\SelectFilter::make('ciudad')
                    ->options(fn () => Institucion::distinct()->pluck('ciudad', 'ciudad')->toArray())
                    ->label('Ciudad'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('toggle_status')
                    ->label(fn (Institucion $record): string => 
                        $record->estado === 'active' ? 'Desactivar' : 'Activar'
                    )
                    ->icon(fn (Institucion $record): string => 
                        $record->estado === 'active' ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle'
                    )
                    ->color(fn (Institucion $record): string => 
                        $record->estado === 'active' ? 'danger' : 'success'
                    )
                    ->action(fn (Institucion $record) => 
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
            'index' => Pages\ListInstitucions::route('/'),
            'create' => Pages\CreateInstitucion::route('/create'),
            'edit' => Pages\EditInstitucion::route('/{record}/edit'),
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
