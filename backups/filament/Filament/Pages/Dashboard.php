<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\OptimizedStatsOverviewWidget;
use App\Filament\Widgets\OptimizedProximasVisitasWidget;
use App\Filament\Widgets\DashboardOverviewWidget;
use App\Models\Surgery;
use App\Models\Visita;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Panel de Control';
    protected static ?int $navigationSort = -2;

    public function getHeaderWidgets(): array
    {
        $widgets = [
            OptimizedStatsOverviewWidget::class,
            DashboardOverviewWidget::class,
        ];

        if (auth()->user()->hasPermission('view_visits')) {
            $widgets[] = OptimizedProximasVisitasWidget::class;
        }

        return $widgets;
    }

    public function getFooterWidgets(): array
    {
        return [];
    }

    public function getHeading(): string
    {
        $hour = now()->hour;
        $greeting = match(true) {
            $hour >= 5 && $hour < 12 => '¡Buenos días',
            $hour >= 12 && $hour < 19 => '¡Buenas tardes',
            default => '¡Buenas noches'
        };

        return $greeting . ', ' . auth()->user()->name;
    }

    public function getSubheading(): ?string
    {
        $pendingTasks = Surgery::where('status', Surgery::STATUS_PENDING)->count();
        $upcomingVisits = Visita::where('estado', 'pendiente')
            ->whereDate('fecha', '>=', now())
            ->count();

        return "Tienes {$pendingTasks} cirugías pendientes y {$upcomingVisits} visitas programadas.";
    }

    public function getColumns(): int|array
    {
        return [
            'default' => 1,
            'sm' => 2,
            'md' => 3,
            'lg' => 4,
        ];
    }
}
