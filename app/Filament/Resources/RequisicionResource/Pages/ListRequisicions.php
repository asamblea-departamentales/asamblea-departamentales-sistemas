<?php

namespace App\Filament\Resources\RequisicionResource\Pages;

use App\Exports\CsvExport;
use App\Filament\Resources\RequisicionResource;
use App\Models\Catalogo;
use App\Models\Requisicion;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRequisicions extends ListRecords
{
    protected static string $resource = RequisicionResource::class;

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
                    $query = Requisicion::query()->with('departamental');

                    if (! $user->isCentralRole()) {
                        $query->where('departamental_id', $user->departamental_id);
                    }

                    $data = $query->get()->map(fn ($r) => collect([
                        $r->id,
                        Catalogo::label('tipo_insumo', $r->tipo_insumo) ?? $r->tipo_insumo,
                        Catalogo::label('rubro', $r->rubro) ?? $r->rubro,
                        $r->cantidad,
                        $r->fecha_solicitud?->format('d/m/Y'),
                        Requisicion::ESTADOS[$r->estado_interno] ?? $r->estado_interno,
                        $r->departamental?->nombre,
                        $r->observaciones,
                    ]));

                    return CsvExport::download(
                        $data,
                        ['ID', 'Tipo Insumo', 'Rubro', 'Cantidad', 'Fecha', 'Estado', 'Departamental', 'Observaciones'],
                        'requisiciones_'.now()->format('Y-m-d').'.csv'
                    );
                }),

            Actions\Action::make('descargar_pdf')
                ->label('Descargar PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->url(route('reportes.requisiciones.pdf'))
                ->openUrlInNewTab(),
        ];
    }
}
