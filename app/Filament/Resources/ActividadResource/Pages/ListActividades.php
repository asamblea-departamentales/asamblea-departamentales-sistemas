<?php

namespace App\Filament\Resources\ActividadResource\Pages;

use App\Filament\Resources\ActividadResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListActividades extends ListRecords
{
    protected static string $resource = ActividadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Crear Actividad')
                ->icon('heroicon-o-plus-circle')
                ->color('primary'),
        ];
    }
}
