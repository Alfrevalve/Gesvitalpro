<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Equipment;
use App\Models\Surgery;
use App\Models\Institucion;

class OptimizedStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Equipos', Equipment::count())
                ->description('Total de equipos registrados')
                ->descriptionIcon('heroicon-m-computer-desktop')
                ->color('primary'),

            Stat::make('Cirugías', Surgery::count())
                ->description('Total de cirugías programadas')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('success'),

            Stat::make('Instituciones', Institucion::count())
                ->description('Total de instituciones registradas')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('info'),
        ];
    }
}
