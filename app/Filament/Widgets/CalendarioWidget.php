<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ActividadResource;
use App\Models\Actividad;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;

class CalendarioWidget extends FullCalendarWidget implements HasActions
{
    use InteractsWithActions;

    // Inicializa la propiedad $record para evitar el error
    public Model|string|int|null $record = null;
    public function fetchEvents(array $fetchInfo): array
    {
        return Actividad::all()->map(function ($actividad) {
            return [
                'id' => $actividad->id,
                'title' => $actividad->macroactividad,
                'start' => $actividad->star_date,
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
    
    // Sobrescribe el método onEventClick para manejar el clic en el evento
    public function onEventClick(array $event): void
  {
    $this->record = Actividad::find($event['id']);

    $this->redirect(
        \App\Filament\Resources\ActividadResource::getUrl('view', ['record' => $this->record->id])
    );
  }


    protected static ?string $heading = 'Calendario de Actividades';

    protected int|string|array $columnSpan = 'full';
}