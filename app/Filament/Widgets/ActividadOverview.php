<?php

namespace App\Filament\Widgets;

use App\Models\Actividad;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ActividadOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();
        $query = Actividad::query();

        if (! $user->isCentralRole()) {
            $query->where('actividads.departamental_id', $user->departamental_id);
        }

        $counts = $query->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN estado = 'Completada' THEN 1 ELSE 0 END) as completadas,
            SUM(CASE WHEN estado IN ('Pendiente','En Progreso') THEN 1 ELSE 0 END) as pendientes,
            SUM(CASE WHEN estado = 'Cancelada' THEN 1 ELSE 0 END) as canceladas
        ")->first();

        return [
            Stat::make('Total Actividades', $counts->total)
                ->description('Total en el sistema')
                ->descriptionIcon('heroicon-m-list-bullet')
                ->color('primary'),

            Stat::make('Actividades Completadas', $counts->completadas)
                ->description('Actividades completadas con exito')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Actividades Pendientes', $counts->pendientes)
                ->description('Actividades que aún están pendientes o en progreso')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Actividades Canceladas', $counts->canceladas)
                ->description('Actividades canceladas')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}
