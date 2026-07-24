<?php

namespace App\Filament\Widgets;

use App\Models\Contrato;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ContratoOverview extends BaseWidget
{
    protected static ?int $sort = 5;

    protected function getStats(): array
    {
        $user = auth()->user();
        $query = Contrato::query();

        if (! $user->isCentralRole()) {
            $query->where('departamental_id', $user->departamental_id);
        }

        $total = (clone $query)->count();

        $vigentes = (clone $query)
            ->where('fecha_inicio', '<=', Carbon::now())
            ->where('fecha_vencimiento', '>=', Carbon::now())
            ->count();

        $por_vencer = (clone $query)
            ->where('fecha_vencimiento', '>=', Carbon::now())
            ->where('fecha_vencimiento', '<=', Carbon::now()->addDays(30))
            ->count();

        $vencidos = (clone $query)
            ->where('fecha_vencimiento', '<', Carbon::now())
            ->count();

        return [
            Stat::make('Total Contratos', $total)
                ->description('Contratos registrados')
                ->descriptionIcon('heroicon-m-document-duplicate')
                ->color('primary'),

            Stat::make('Vigentes', $vigentes)
                ->description('Contratos activos')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Por Vencer', $por_vencer)
                ->description('Vencen en 30 días')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),

            Stat::make('Vencidos', $vencidos)
                ->description('Contratos vencidos')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}
