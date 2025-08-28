<?php

namespace App\Filament\Widgets;


use App\Models\Actividad;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ActividadOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
        //Primer estadística: Total de Actividades
        Stat::make('Total Actividades', Actividad::count())
            ->description('Total en el sistema')
            ->descriptionIcon('heroicon-m-list-bullet')
            ->color('primary'),

        //Segunda estaditica: Actividades Completadas
        Stat::make('Actividades Completadas', Actividad::where('estado', 'Completada')->count())
            ->description('Actividades completadas con exito')
            ->descriptionIcon('heroicon-m-check-circle')
            ->color('success'),

       //Tercera estadística: Actividades Pendientes y En Progreso
        Stat::make('Actividades Pendientes', Actividad::whereIn('estado', ['Pendiente', 'En Progreso'])->count())
            ->description('Actividades que aún están pendientes o en progreso')
            ->descriptionIcon('heroicon-m-clock')
            ->color('warning'),
        ];
    }
}
