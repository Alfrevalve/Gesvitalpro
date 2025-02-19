<?php

namespace App\Filament\Widgets;

use App\Models\Visita;
use App\Models\Medico;
use App\Models\Institucion;
use App\Services\DashboardOptimizer;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class OptimizedProximasVisitasWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = 'Próximas Visitas';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns($this->getTableColumns())
            ->filters($this->getTableFilters())
            ->actions($this->getTableActions())
            ->bulkActions($this->getTableBulkActions())
            ->defaultSort('fecha', 'asc')
            ->paginated([5, 10, 25])
            ->poll('30s');
    }

    protected function getTableQuery(): Builder
    {
        return Visita::query()
            ->where('fecha', '>=', now())
            ->where('fecha', '<=', now()->addDays(7))
            ->orderBy('fecha');
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('fecha')
                ->label('Fecha')
                ->dateTime('d/m/Y H:i')
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('medico.nombre')
                ->label('Médico')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('institucion.nombre')
                ->label('Institución')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('responsable_nombre')
                ->label('Responsable')
                ->searchable()
                ->sortable(),

            Tables\Columns\BadgeColumn::make('estado')
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
                ])
                ->sortable(),

            Tables\Columns\BadgeColumn::make('tipo')
                ->label('Tipo de Visita')
                ->colors([
                    'info' => 'seguimiento',
                    'success' => 'primera',
                    'warning' => 'control',
                ])
                ->sortable(),

            Tables\Columns\TextColumn::make('notas')
                ->label('Notas')
                ->limit(30)
                ->searchable(),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('tipo')
                ->options([
                    'primera' => 'Primera Visita',
                    'seguimiento' => 'Seguimiento',
                    'control' => 'Control',
                ])
                ->label('Tipo de Visita'),

            Tables\Filters\SelectFilter::make('estado')
                ->options([
                    'pendiente' => 'Pendiente',
                    'realizada' => 'Realizada',
                    'cancelada' => 'Cancelada',
                ])
                ->label('Estado'),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\Action::make('ver_detalles')
                ->label('Ver Detalles')
                ->icon('heroicon-o-eye')
                ->url(fn (Visita $record) => route('filament.resources.visitas.view', $record))
                ->openUrlInNewTab(),

            Tables\Actions\Action::make('marcar_realizada')
                ->label('Marcar Realizada')
                ->icon('heroicon-o-check-circle')
                ->action(fn (Visita $record) => $record->update(['estado' => 'realizada']))
                ->requiresConfirmation()
                ->visible(fn (Visita $record) => $record->estado === 'pendiente'),

            Tables\Actions\Action::make('marcar_cancelada')
                ->label('Marcar Cancelada')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->action(fn (Visita $record) => $record->update(['estado' => 'cancelada']))
                ->requiresConfirmation()
                ->visible(fn (Visita $record) => $record->estado === 'pendiente'),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [
            Tables\Actions\BulkAction::make('marcar_realizadas')
                ->label('Marcar como Realizadas')
                ->icon('heroicon-o-check-circle')
                ->action(fn (Collection $records) => $records->each->update(['estado' => 'realizada']))
                ->requiresConfirmation()
                ->deselectRecordsAfterCompletion(),

            Tables\Actions\BulkAction::make('marcar_canceladas')
                ->label('Marcar como Canceladas')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->action(fn (Collection $records) => $records->each->update(['estado' => 'cancelada']))
                ->requiresConfirmation()
                ->deselectRecordsAfterCompletion(),
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()->hasPermission('view_visits');
    }
}
