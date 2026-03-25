<?php

namespace App\Filament\Resources\ActividadResource\Pages;

use App\Filament\Resources\ActividadResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\View;

class ViewActividad extends ViewRecord
{
    protected static string $resource = ActividadResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Atestados')
                    ->schema([
                        View::make('filament.components.media-gallery')
                            ->viewData([
                                'media' => $this->record->getMedia('atestados'),
                            ])
                    ])
            ]);
    }
}