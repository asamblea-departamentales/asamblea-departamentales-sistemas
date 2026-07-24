<?php

namespace App\Filament\Resources\ContratoResource\Pages;

use App\Exports\CsvExport;
use App\Filament\Resources\ContratoResource;
use App\Models\Contrato;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContratos extends ListRecords
{
    protected static string $resource = ContratoResource::class;

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
                    $query = Contrato::query()->with('departamental');

                    if (! $user->isCentralRole()) {
                        $query->where('departamental_id', $user->departamental_id);
                    }

                    $data = $query->get()->map(fn ($c) => collect([
                        $c->id,
                        $c->tipo,
                        $c->proveedor,
                        '$'.number_format($c->monto, 2),
                        $c->fecha_inicio?->format('d/m/Y'),
                        $c->fecha_vencimiento?->format('d/m/Y'),
                        $c->oficina,
                        $c->departamental?->nombre,
                        $c->observaciones,
                    ]));

                    return CsvExport::download(
                        $data,
                        ['ID', 'Tipo', 'Proveedor', 'Monto', 'Fecha Inicio', 'Fecha Vencimiento', 'Oficina', 'Departamental', 'Observaciones'],
                        'contratos_'.now()->format('Y-m-d').'.csv'
                    );
                }),

            Actions\Action::make('descargar_pdf')
                ->label('Descargar PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->url(route('reportes.contratos.pdf'))
                ->openUrlInNewTab(),
        ];
    }
}
