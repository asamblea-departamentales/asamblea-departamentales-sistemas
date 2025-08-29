<?php

namespace App\Filament\Resources\Banner\CategoryResource\Pages;

use App\Filament\Resources\Banner\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return __('Banner Categories Management');
    }
}
