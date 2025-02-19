<?php

namespace App\Filament\Resources\LineResource\RelationManagers;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class StaffRelationManager extends RelationManager
{
    protected static string $relationship = 'staff';
    protected static ?string $title = 'Personal';
    protected static ?string $modelLabel = 'Personal';
    protected static ?string $pluralModelLabel = 'Personal';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Usuario')
                    ->options(User::where('role', '!=', 'admin')->pluck('name', 'id'))
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('role')
                    ->label('Rol')
                    ->options([
                        'line_manager' => 'Jefe de Línea',
                        'instrumentist' => 'Instrumentista',
                    ])
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('pivot.role')
                    ->label('Rol')
                    ->colors([
                        'primary' => 'line_manager',
                        'success' => 'instrumentist',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'line_manager' => 'Jefe de Línea',
                        'instrumentist' => 'Instrumentista',
                        default => $state,
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Rol')
                    ->options([
                        'line_manager' => 'Jefe de Línea',
                        'instrumentist' => 'Instrumentista',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Agregar Personal')
                    ->preloadRecordSelect(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
