<?php

namespace App\Filament\Widgets;

use App\Models\Actividad;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class CalendarioWidget extends FullCalendarWidget
{
    public function fetchEvents(array $fetchInfo): array
    {
        return Actividad::all()->map(function ($actividad) {
            return [
                'id' => $actividad->id,
                'title' => $actividad->macroactividad,
                'start' => $actividad->star_date, // Ajusta el nombre del campo según tu BD
                'end' => $actividad->due_date,
                'backgroundColor' => match ($actividad->estado) {
                    'Completada' => '#10B981',
                    'Cancelada' => '#EF4444',
                    'Pendiente' => '#F59E0B',
                    'En Progreso' => '#3B82F6',
                    default => '#6366F1'
                },
                'borderColor' => match ($actividad->estado) {
                    'Completada' => '#10B981',
                    'Cancelada' => '#EF4444',
                    'Pendiente' => '#F59E0B',
                    'En Progreso' => '#3B82F6',
                    default => '#6366F1'
                },
            ];
        })->toArray();
    }

    public function config(): array
    {
        return [
            'initialView' => 'dayGridMonth',
            'headerToolbar' => [
                'start' => 'prev,next today',
                'center' => 'title',
                'end' => 'dayGridMonth,timeGridWeek,listWeek',
            ],
            'locale' => 'es',
            'height' => 'auto',
            'aspectRatio' => 1.8,
        ];
    }

    // Título del widget (opcional)
    protected static ?string $heading = 'Calendario de Actividades';

    // Hacer el widget más ancho (opcional)
    protected int|string|array $columnSpan = 'full';
}
