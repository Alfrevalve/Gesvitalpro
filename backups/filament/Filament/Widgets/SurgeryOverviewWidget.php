<?php

namespace App\Filament\Widgets;

use App\Models\Surgery;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class SurgeryOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = 'Próximas Cirugías';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Surgery::query()
                    ->where('status', Surgery::STATUS_PENDING)
                    ->where('surgery_date', '>=', now())
                    ->where('surgery_date', '<=', now()->addDays(7))
                    ->orderBy('surgery_date')
            )
            ->columns([
                TextColumn::make('surgery_date')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('patient_name')
                    ->label('Paciente')
                    ->searchable(),

                TextColumn::make('surgery_type')
                    ->label('Tipo de Cirugía')
                    ->searchable(),

                TextColumn::make('medico.nombre')
                    ->label('Médico')
                    ->searchable(),

                TextColumn::make('institucion.name')
                    ->label('Institución')
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'in_progress' => 'primary',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'secondary',
                    })
            ])
            ->defaultSort('surgery_date', 'asc');
    }
}
