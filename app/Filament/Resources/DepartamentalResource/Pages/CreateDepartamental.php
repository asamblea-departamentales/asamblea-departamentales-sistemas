<?php

namespace App\Filament\Resources\DepartamentalResource\Pages;

use App\Filament\Resources\DepartamentalResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDepartamental extends CreateRecord
{
    protected static string $resource = DepartamentalResource::class;

    // Forma compatible en todas las versiones para quitar "Create another"
    protected function getFormActions(): array
    {
        return [
            Actions\CreateAction::make()->createAnother(false),
        ];
    }
}
