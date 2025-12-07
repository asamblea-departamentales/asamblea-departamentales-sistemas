<?php

namespace App\Filament\Resources\CierreMensualResource\Pages;

use App\Filament\Resources\CierreMensualResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCierreMensual extends EditRecord
{
    protected static string $resource = CierreMensualResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
