<?php

namespace App\Filament\Widgets;

use App\Models\Requisicion;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RequisicionOverview extends BaseWidget
{
    protected static ?int $sort = 4;

    protected function getStats(): array
    {
        $user = auth()->user();
        $query = Requisicion::query();

        if (! $user->isCentralRole()) {
            $query->where('departamental_id', $user->departamental_id);
        }

        $counts = $query->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN estado_interno = 'PENDIENTE' THEN 1 ELSE 0 END) as pendientes,
            SUM(CASE WHEN estado_interno = 'APROBADA' THEN 1 ELSE 0 END) as aprobadas,
            SUM(CASE WHEN estado_interno IN ('COMPRADA','ENTREGADA') THEN 1 ELSE 0 END) as completadas,
            SUM(CASE WHEN estado_interno = 'RECHAZADA' THEN 1 ELSE 0 END) as rechazadas
        ")->first();

        return [
            Stat::make('Total Requisiciones', $counts->total)
                ->description('Requisiciones registradas')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),

            Stat::make('Pendientes', $counts->pendientes)
                ->description('Esperando aprobación')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Aprobadas', $counts->aprobadas)
                ->description('Aprobadas, en proceso')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('info'),

            Stat::make('Completadas', $counts->completadas)
                ->description('Compradas o entregadas')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
