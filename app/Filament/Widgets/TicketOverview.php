<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TicketOverview extends BaseWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        $user = auth()->user();
        $query = Ticket::query();

        if (! $user->isCentralRole()) {
            $query->where('departamental_id', $user->departamental_id);
        }

        $counts = $query->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN estado_interno = 'PENDIENTE' THEN 1 ELSE 0 END) as pendientes,
            SUM(CASE WHEN estado_interno = 'EN_PROCESO' THEN 1 ELSE 0 END) as en_proceso,
            SUM(CASE WHEN estado_interno IN ('RESUELTO','CERRADO') THEN 1 ELSE 0 END) as resueltos,
            SUM(CASE WHEN estado_interno = 'CANCELADO' THEN 1 ELSE 0 END) as cancelados
        ")->first();

        return [
            Stat::make('Total Tickets', $counts->total)
                ->description('Tickets registrados')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('primary'),

            Stat::make('Pendientes', $counts->pendientes)
                ->description('Esperando atención')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('En Proceso', $counts->en_proceso)
                ->description('Siendo atendidos')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('info'),

            Stat::make('Resueltos', $counts->resueltos)
                ->description('Tickets resueltos o cerrados')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
