<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Equipment;
use App\Models\Surgery;
use App\Models\Institucion;

class StatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $pendingSurgeries = Surgery::query()
            ->where('status', Surgery::STATUS_PENDING)
            ->where('surgery_date', '<=', now()->addDays(7))
            ->count();

        $totalEquipment = Equipment::count();
        $totalInstitutions = Institucion::count();

        return [
            Stat::make('Equipos Registrados', $totalEquipment)
                ->description('Total de equipos en el sistema')
                ->descriptionIcon('heroicon-m-computer-desktop')
                ->color('primary'),

            Stat::make('Cirugías Próximas', $pendingSurgeries)
                ->description('En los próximos 7 días')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('warning'),

            Stat::make('Instituciones', $totalInstitutions)
                ->description('Total de instituciones registradas')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('success'),
        ];
    }
}
