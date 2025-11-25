<?php

namespace App\Filament\Resources\ActividadResource\Pages;

use App\Filament\Resources\ActividadResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditActividad extends EditRecord
{
    protected static string $resource = ActividadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    // REDIRECT después de guardar
    protected function getSavedNotificationRedirectUrl(): ?string
    {
        return $this->getResource()::getUrl('index');
    }

    // (opcional) redirect genérico de la página
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
