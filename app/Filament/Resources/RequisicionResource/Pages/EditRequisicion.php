<?php

namespace App\Filament\Resources\RequisicionResource\Pages;

use App\Filament\Resources\RequisicionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRequisicion extends EditRecord
{
    protected static string $resource = RequisicionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
