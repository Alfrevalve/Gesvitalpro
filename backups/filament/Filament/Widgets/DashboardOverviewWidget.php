<?php

namespace App\Filament\Widgets;

use App\Models\Surgery;
use App\Models\Equipment;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class DashboardOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = 'Resumen del Dashboard';

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
            ->defaultSort('surgery_date', 'asc')
            ->headerActions([
                \Filament\Tables\Actions\Action::make('equipment_status')
                    ->label('Estado de Equipos')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->action(function () {
                        // This will be displayed in the widget header
                        $stats = [
                            'available' => Equipment::where('status', 'available')->count(),
                            'in_use' => Equipment::where('status', 'in_use')->count(),
                            'maintenance' => Equipment::where('status', 'maintenance')->count(),
                        ];

                        return view('filament.widgets.equipment-status', [
                            'stats' => $stats,
                        ]);
                    })
            ]);
    }
}
