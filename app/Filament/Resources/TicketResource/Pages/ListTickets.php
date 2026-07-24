<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Exports\CsvExport;
use App\Filament\Resources\TicketResource;
use App\Models\Catalogo;
use App\Models\Ticket;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTickets extends ListRecords
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            Actions\Action::make('exportar_csv')
                ->label('Exportar CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    $user = auth()->user();
                    $query = Ticket::query()->with('departamental');

                    if (! $user->isCentralRole()) {
                        $query->where('departamental_id', $user->departamental_id);
                    }

                    $data = $query->get()->map(fn ($t) => collect([
                        $t->id,
                        Catalogo::label('tipo_ticket', $t->tipo_ticket) ?? $t->tipo_ticket,
                        $t->motivo,
                        $t->fecha_solicitud?->format('d/m/Y'),
                        Ticket::ESTADOS[$t->estado_interno] ?? $t->estado_interno,
                        $t->departamental?->nombre,
                        $t->observaciones,
                    ]));

                    return CsvExport::download(
                        $data,
                        ['ID', 'Tipo', 'Motivo', 'Fecha', 'Estado', 'Departamental', 'Observaciones'],
                        'tickets_'.now()->format('Y-m-d').'.csv'
                    );
                }),

            Actions\Action::make('descargar_pdf')
                ->label('Descargar PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->url(route('reportes.tickets.pdf'))
                ->openUrlInNewTab(),
        ];
    }
}
