<?php

namespace App\Filament\Resources\CierreMensualResource\Pages;

use App\Filament\Resources\ActividadResource;
use App\Filament\Resources\CierreMensualResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCierreMensuals extends ListRecords
{
    protected static string $resource = CierreMensualResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generar_cierre')
            ->label('Generar Nuevo Cierre')
            ->icon('heroicon-o-plus-circle')
            ->color('primary')
            ->url(fn () => ActividadResource::getUrl('index'))
            ->visible(fn () => auth()->user()->hasAnyRole(['super_admin', 'coordinador', 'gol'])),        ];
    }
}
