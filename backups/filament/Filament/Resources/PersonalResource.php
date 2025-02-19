<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersonalResource\Pages;
use App\Models\User;
use App\Models\Role;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PersonalResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Personal';

    protected static ?string $navigationGroup = 'Gestión';

    protected static ?int $navigationSort = 8;

    public static function getEloquentQuery(): Builder
    {
        $staffRole = Role::where('slug', 'staff')->first();
        if (!$staffRole) {
            return parent::getEloquentQuery()->where('id', 0);
        }
        return parent::getEloquentQuery()
            ->whereHas('role', function (Builder $query) use ($staffRole) {
                $query->where('id', $staffRole->id);
            });
    }

    public static function form(Form $form): Form
    {
        $staffRole = Role::where('slug', 'staff')->first();

        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->label('Nombre'),
            Forms\Components\TextInput::make('email')
                ->email()
                ->required()
                ->maxLength(255)
                ->label('Correo Electrónico'),
            Forms\Components\TextInput::make('password')
                ->password()
                ->required()
                ->maxLength(255)
                ->label('Contraseña')
                ->visible(fn ($livewire) => $livewire instanceof Pages\CrearPersonal),
            Forms\Components\Hidden::make('role_id')
                ->default($staffRole?->id)
                ->required(),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')
                ->searchable()
                ->sortable()
                ->label('Nombre'),
            Tables\Columns\TextColumn::make('email')
                ->searchable()
                ->sortable()
                ->label('Correo Electrónico'),
            Tables\Columns\TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->label('Fecha de Registro'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListarPersonal::route('/'),
            'create' => Pages\CrearPersonal::route('/crear'),
            'edit' => Pages\EditarPersonal::route('/{record}/editar'),
        ];
    }

    public static function getModelLabel(): string
    {
        return 'Personal';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Personal';
    }
}
