<?php

namespace App\Filament\Widgets;

use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Visita;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class ProximasVisitasWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = 'Próximas Visitas';

    public function getDefaultTableRecordsPerPageSelectOption(): int
    {
        return 5;
    }

    protected function getTableQuery(): Builder
    {
        return Visita::query()
            ->with(['medico', 'institucion', 'user'])
            ->where('fecha', '>=', now())
            ->where('fecha', '<=', now()->addDays(7))
            ->where('estado', 'pendiente')
            ->orderBy('fecha');
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('fecha')
                ->label('Fecha')
                ->dateTime('d/m/Y H:i')
                ->sortable(),

            TextColumn::make('medico.name')
                ->label('Médico')
                ->searchable(),

            TextColumn::make('institucion.nombre')
                ->label('Institución')
                ->searchable(),

            TextColumn::make('user.name')
                ->label('Responsable')
                ->searchable(),

            BadgeColumn::make('estado')
                ->label('Estado')
                ->colors([
                    'warning' => 'pendiente',
                    'success' => 'realizada',
                    'danger' => 'cancelada',
                ])
                ->icons([
                    'heroicon-o-clock' => 'pendiente',
                    'heroicon-o-check-circle' => 'realizada',
                    'heroicon-o-x-circle' => 'cancelada',
                ]),

            TextColumn::make('tipo')
                ->label('Tipo de Visita')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'seguimiento' => 'info',
                    'primera' => 'success',
                    'control' => 'warning',
                    default => 'gray',
                }),

            TextColumn::make('notas')
                ->label('Notas')
                ->limit(30)
                ->tooltip(function (TextColumn $column): ?string {
                    $state = $column->getState();
                    if (strlen($state) <= $column->getLimit()) {
                        return null;
                    }
                    return $state;
                }),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            // Aquí puedes añadir filtros si son necesarios
        ];
    }

    protected function getTableActions(): array
    {
        return [
            // Aquí puedes añadir acciones si son necesarias
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [
            // Aquí puedes añadir acciones en masa si son necesarias
        ];
    }

    protected function getTableEmptyStateIcon(): ?string
    {
        return 'heroicon-o-calendar';
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return 'No hay visitas programadas';
    }

    protected function getTableEmptyStateDescription(): ?string
    {
        return 'No hay visitas programadas para los próximos 7 días.';
    }

    protected function getTablePollingInterval(): ?string
    {
        return '30s';
    }

    protected function getTableReorderColumn(): ?string
    {
        return null;
    }

    protected function shouldPersistTableFiltersInSession(): bool
    {
        return true;
    }

    protected function getDefaultTableSortColumn(): ?string
    {
        return 'fecha';
    }

    protected function getDefaultTableSortDirection(): ?string
    {
        return 'asc';
    }

    public static function canView(): bool
    {
        return auth()->user()->hasPermission('view_visits');
    }
}
