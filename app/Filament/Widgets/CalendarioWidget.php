<?php

namespace App\Filament\Widgets;

use App\Models\Actividad;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class CalendarioWidget extends FullCalendarWidget implements HasActions
{
    use InteractsWithActions;

    // Inicializa la propiedad $record para evitar el error
    public Model|string|int|null $record = null;

    public function fetchEvents(array $fetchInfo): array
    {
        $user = auth()->user();
        $query = Actividad::query();

        if (! $user->isCentralRole()) {
            $query->where('actividades.departamental_id', $user->departamental_id);
        }

        return $query
            ->where('star_date', '>=', $fetchInfo['start'])
            ->where('due_date', '<=', $fetchInfo['end'])
            ->select(['id', 'macroactividad', 'star_date', 'due_date', 'estado'])
            ->get()
            ->map(function ($actividad) {
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
        $actividad = Actividad::find($event['id']);
        if (! $actividad) {
            return;
        }
        $this->record = $actividad;

        $this->redirect(
            \App\Filament\Resources\ActividadResource::getUrl('view', ['record' => $this->record->id])
        );
    }

    protected static ?string $heading = 'Calendario de Actividades';

    protected int|string|array $columnSpan = 'full';
}
