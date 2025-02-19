<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Surgery;
use App\Models\Equipment;
use App\Models\Visita;
use App\Models\SurgeryRequest;
use Carbon\Carbon;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();

        // Estadísticas de Cirugías
        $surgeriesStats = $this->getSurgeriesStats($today, $thisMonth);

        // Estadísticas de Equipos
        $equipmentStats = $this->getEquipmentStats();

        // Estadísticas de Visitas
        $visitStats = $this->getVisitStats($today, $thisWeek);

        // Estadísticas de Logística
        $logisticsStats = $this->getLogisticsStats();

        return [
            ...$surgeriesStats,
            ...$equipmentStats,
            ...$visitStats,
            ...$logisticsStats,
        ];
    }

    protected function getSurgeriesStats($today, $thisMonth): array
    {
        $todaySurgeries = Surgery::whereDate('surgery_date', $today)->count();
        $todayCompleted = Surgery::whereDate('surgery_date', $today)
            ->where('status', 'completed')
            ->count();

        $monthSurgeries = Surgery::whereDate('surgery_date', '>=', $thisMonth)->count();
        $monthCompleted = Surgery::whereDate('surgery_date', '>=', $thisMonth)
            ->where('status', 'completed')
            ->count();

        return [
            Stat::make('Cirugías Hoy', $todaySurgeries)
                ->description("{$todayCompleted} completadas")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart([
                    $todayCompleted,
                    $todaySurgeries - $todayCompleted,
                ]),

            Stat::make('Cirugías del Mes', $monthSurgeries)
                ->description("{$monthCompleted} completadas")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('primary')
                ->chart([
                    $monthCompleted,
                    $monthSurgeries - $monthCompleted,
                ]),
        ];
    }

    protected function getEquipmentStats(): array
    {
        $totalEquipment = Equipment::count();
        $availableEquipment = Equipment::where('status', 'available')->count();
        $maintenanceEquipment = Equipment::where('status', 'maintenance')->count();
        $maintenanceDue = Equipment::where('next_maintenance_date', '<=', now()->addDays(30))->count();

        return [
            Stat::make('Equipos Disponibles', $availableEquipment)
                ->description("de {$totalEquipment} totales")
                ->descriptionIcon('heroicon-m-computer-desktop')
                ->color('success'),

            Stat::make('En Mantenimiento', $maintenanceEquipment)
                ->description("{$maintenanceDue} próximos a mantenimiento")
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color($maintenanceEquipment > 0 ? 'warning' : 'success'),
        ];
    }

    protected function getVisitStats($today, $thisWeek): array
    {
        $todayVisits = Visita::whereDate('fecha', $today)->count();
        $todayCompleted = Visita::whereDate('fecha', $today)
            ->where('estado', 'realizada')
            ->count();

        $weekVisits = Visita::whereBetween('fecha', [$thisWeek, now()])->count();
        $weekCompleted = Visita::whereBetween('fecha', [$thisWeek, now()])
            ->where('estado', 'realizada')
            ->count();

        return [
            Stat::make('Visitas Hoy', $todayVisits)
                ->description("{$todayCompleted} realizadas")
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info')
                ->chart([
                    $todayCompleted,
                    $todayVisits - $todayCompleted,
                ]),

            Stat::make('Visitas de la Semana', $weekVisits)
                ->description("{$weekCompleted} realizadas")
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary')
                ->chart([
                    $weekCompleted,
                    $weekVisits - $weekCompleted,
                ]),
        ];
    }

    protected function getLogisticsStats(): array
    {
        $pendingRequests = SurgeryRequest::where('status', 'pending')->count();
        $urgentRequests = SurgeryRequest::where('status', 'pending')
            ->where('priority', 'high')
            ->count();

        $processingRequests = SurgeryRequest::where('status', 'in_progress')->count();
        $completedToday = SurgeryRequest::where('status', 'completed')
            ->whereDate('updated_at', Carbon::today())
            ->count();

        return [
            Stat::make('Solicitudes Pendientes', $pendingRequests)
                ->description("{$urgentRequests} urgentes")
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($urgentRequests > 0 ? 'danger' : 'warning'),

            Stat::make('En Proceso', $processingRequests)
                ->description("{$completedToday} completadas hoy")
                ->descriptionIcon('heroicon-m-truck')
                ->color('info'),
        ];
    }
}
